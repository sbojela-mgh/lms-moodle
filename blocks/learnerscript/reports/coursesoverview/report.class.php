<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * LearnerScript
 * A Moodle block for creating customizable reports
 * @package blocks
 * @subpackage learnerscript
 * @author: sreekanth<sreekanth@eabyas.in>
 * @date: 2017
 */
use block_learnerscript\local\querylib;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\ls as ls;

defined('MOODLE_INTERNAL') || die();
class report_coursesoverview extends reportbase implements report {
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public $userid;

    public function __construct($report, $reportproperties) {
        global $USER;
        parent::__construct($report, $reportproperties);
        $this->components = array('columns', 'conditions', 'filters', 'permissions', 'calcs', 'plot');
        $columns = ['coursename', 'totalactivities', 'completedactivities', 'inprogressactivities', 'grades', 'totaltimespent'];
        $this->columns = ['coursesoverview' => $columns];
       
        if ($this->role != 'student') {
            $this->basicparams = [['name' => 'users']];
        }
        if ($this->role == 'student') {
            $this->parent = true;
        } else {
            $this->parent = false;
        }
        $this->filters = array('courses', 'modules');
        $this->orderable = array('totalactivities', 'completedactivities', 'inprogressactivities', 'coursename', 'totaltimespent');
        $this->defaultcolumn = 'c.id';
    }

    public function init() {
       if($this->role != 'student' && !isset($this->params['filter_users'])){
            $this->initial_basicparams('users');
            $fusers = array_keys($this->filterdata);
            $this->params['filter_users'] = array_shift($fusers);
        }
        if (!$this->scheduling && isset($this->basicparams) && !empty($this->basicparams)) {
            $basicparams = array_column($this->basicparams, 'name');
            foreach ($basicparams as $basicparam) {
                if (empty($this->params['filter_' . $basicparam])) {
                    return false;
                }
            }
        }
        $this->courseid = isset($this->params['filter_courses']) ? $this->params['filter_courses'] : array();
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        $this->params['userid'] = $userid;
    }

    public function count() {
        $this->sql = "SELECT COUNT(DISTINCT c.id)";
    }

    public function select() {
        $this->sql = "SELECT DISTINCT c.id, c.fullname AS coursename";
        parent::select();
    }

    public function from() {
        $this->sql .= " FROM {role_assignments} ra";
    }

    public function joins() {
        $this->sql .=  " JOIN {context} AS ctx ON ctx.id = ra.contextid
                         JOIN {course} c ON c.id = ctx.instanceid
                         JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'";
        parent::joins();
    }

    public function where() {
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        $this->sql .= " WHERE ra.userid = $userid AND c.visible = 1";
        if (!empty($conditionfinalelements)) {
            $conditions = implode(',', $conditionfinalelements);
            $this->sql .= " AND c.id IN (:conditions)";
            $this->params['conditions'] = $conditions;
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->sql .= " AND ra.timemodified BETWEEN $this->ls_startdate AND $this->ls_enddate ";
        }
        if (!is_siteadmin($this->userid) && !(new ls)->is_manager($this->userid, $this->contextlevel, $this->role)) {
            if ($this->rolewisecourses != '') {
                $this->sql .= " AND c.id IN ($this->rolewisecourses) ";
            }
        }
        parent::where();
    }

    public function search() {
      if (isset($this->search) && $this->search) {
         $fields = array("c.fullname");
          $fields = implode(" LIKE '%" . $this->search . "%' OR ", $fields);
          $fields .= " LIKE '%" . $this->search . "%' ";
          $this->sql .= " AND ($fields) ";
      }
    }

    public function filters() {
        $filtercourses = isset($this->params['filter_courses']) ? $this->params['filter_courses'] : SITEID;
        $filtermodules = isset($this->params['filter_modules']) ? $this->params['filter_modules'] : 0;
        $userid = isset($this->params['filter_users']) && $this->params['filter_users'] > 0
                    ? $this->params['filter_users'] : $this->userid;
        if ($filtercourses > SITEID) {
            $filtercourses = $filtercourses;
            $this->sql .= " AND c.id IN ($filtercourses)";
        }
        if (empty($this->params['filter_status']) || $this->params['filter_status'] == 'all') {
            $this->sql .= " ";
        }
        if ($this->params['filter_status'] == 'completed') {
            $this->sql .= " AND c.id IN (SELECT DISTINCT course FROM {course_completions} WHERE userid = $userid
                                        AND timecompleted > 0)";
        }
        if ($this->params['filter_status'] == 'inprogress') {
            $this->sql .= " AND c.id NOT IN (SELECT DISTINCT course FROM {course_completions} WHERE userid = $userid
                                            AND timecompleted > 0)";
        }
    }
    /**
     * @param  array $courses Courses
     * @return array $reportarray courses information
     */
    public function get_rows($courses) {
        return $courses;
    }
    
    public function column_queries($columnname, $courseid) {
       global $DB; 
        $filteruserid = isset($this->params['filter_users']) ? $this->params['filter_users'] : $this->userid;
        $filtermoduleid = isset($this->params['filter_modules']) ? $this->params['filter_modules'] : 0;

        $where = " AND %placeholder% = $courseid";
        $concatsql = " ";
        if (!empty($filtermoduleid)) {
            $concatsql = " AND cm.module = $filtermoduleid";
        }
        switch ($columnname) {
            case 'totalactivities' : 
                $identy = 'cm.course';
                $query =  "SELECT COUNT(cm.id) AS totalactivities 
                              FROM {course_modules} AS cm
                             WHERE cm.visible = 1  $concatsql $where ";
            break;
            case 'completedactivities' :
                $identy = 'cm.course';
                $query =  "SELECT COUNT(DISTINCT cmc.coursemoduleid) AS completedactivities 
                               FROM {course_modules_completion} AS cmc
                               JOIN {course_modules} AS cm ON cm.id = cmc.coursemoduleid
                              WHERE cm.visible = 1  AND cmc.userid = $filteruserid AND cmc.completionstate > 0
                                    $concatsql $where ";

            break;
            case 'inprogressactivities' :
                $identy = 'cm.course';
                $query =  "SELECT COUNT(DISTINCT cm.id) AS inprogressactivities 
                               FROM {course_modules} AS cm
                              WHERE  cm.visible = 1 AND cm.id NOT IN (SELECT coursemoduleid
                                                    FROM {course_modules_completion}
                                                    WHERE userid = " . $filteruserid . "  AND completionstate > 0) $concatsql $where ";
            break;
            case 'grades' :
                $identy = 'gi.courseid';
                $modulename = $DB->get_field('modules', 'name', array('id' => $filtermoduleid));
                $gradesql = "SELECT  CONCAT(ROUND(SUM(gg.finalgrade), 2),' / ', ROUND(SUM(gi.grademax), 2)) AS grades 
                               FROM {grade_grades} gg
                               JOIN {grade_items} gi ON gi.id = gg.itemid
                              WHERE gg.userid = $filteruserid  $where ";
                if (!empty($filtermoduleid)) {
                    $gradesql .= " AND gi.itemmodule = '$modulename' AND gi.itemtype != 'course'";
                } else {
                    $gradesql .= " AND gi.itemtype = 'course'";
                }
                $query =  $gradesql;
            break;
            case 'totaltimespent' :
                $identy = 'blc.courseid';
                $query = " SELECT SUM(blc.timespent) AS totaltimespent 
                            FROM {block_ls_coursetimestats} blc 
                            WHERE blc.userid > 2 AND blc.userid = $filteruserid  $where ";
                break;

            default:
            return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        return $query;
    }
}