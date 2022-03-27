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

/** LearnerScript
 * A Moodle block for creating customizable reports
 * @package blocks
 * @subpackage learnerscript
 * @author: sreekanth
 * @date: 2017
 */
use block_learnerscript\local\querylib;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;
use block_learnerscript\local\ls as ls;

defined('MOODLE_INTERNAL') || die();
class report_assignment extends reportbase implements report {
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report, $reportproperties);
        $this->parent = true;
        $this->columns = array('assignmentfield' => ['assignmentfield'],
                                'assignment' => array('gradepass', 'grademax', 'avggrade', 'submittedusers', 'completedusers', 'needgrading', 'totaltimespent', 'numviews'));
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $this->courselevel = true;
        $this->basicparams = array(['name' => 'courses']);
        $this->orderable = array('name', 'course', 'submittedusers', 'completedusers', 'needgrading', 'avggrade', 'gradepass', 'totaltimespent',  'grademax');
        $this->searchable = array('main.name', 'c.fullname', 'c.shortname');
        $this->defaultcolumn = 'main.id';
        $this->excludedroles = array("'student'");
    }
    public function count() {
        $this->sql = "SELECT COUNT(DISTINCT main.id)";
    }
    public function select() {
        $this->sql = "SELECT DISTINCT main.id, main.name AS name, main.course AS course";
        parent::select();
    }
    public function from() {
        $this->sql .= " FROM {assign} as main 
                        JOIN {course_modules} as cm ON cm.instance = main.id
                        JOIN {modules} m ON cm.module = m.id
                        JOIN {course} as c ON main.course = c.id";
    }
    public function joins() {
        parent::joins();
    }
    public function where() {
        $this->sql .= " WHERE c.visible = 1 AND cm.visible = 1 AND c.id <> :siteid AND m.name = 'assign'";
        if (!is_siteadmin($this->userid) && !(new ls)->is_manager($this->userid, $this->contextlevel, $this->role)) {
            if ($this->rolewisecourses != '') {
                $this->sql .= " AND main.course IN ($this->rolewisecourses) ";
            }
        }
        parent::where();
    }
    public function search() {
        if (isset($this->search) && $this->search) {
            $fields = implode(" LIKE '%" . $this->search . "%' OR ", $this->searchable);
            $fields .= " LIKE '%" . $this->search . "%' ";
            $this->sql .= " AND ($fields) ";
        }
    }
    public function filters() {
        if (!empty($this->params['filter_courses']) && $this->params['filter_courses'] <> SITEID && !$this->scheduling) {
            $courseids = $this->params['filter_courses'];
            $this->sql .= " AND main.course IN ($courseids) ";
        }
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->sql .= " AND cm.added BETWEEN $this->ls_startdate AND $this->ls_enddate ";
        }
    }

    /**
     * [get_rows description]
     * @param  array  $users [description]
     * @return [type]        [description]
     */
    public function get_rows($assignments = array()) {
        return $assignments;
    }
    public function column_queries($columnname, $assignid, $courseid = null) {
        if($courseid){
            $learnersql  = (new querylib)->get_learners('', $courseid);
        }else{
            $learnersql  = (new querylib)->get_learners('', '%courseid%');
        }
        $where = " AND %placeholder% = $assignid";
        
        switch ($columnname) {
            case 'gradepass':
                $identy = 'cm.instance';
                $query = "SELECT ROUND(gi.gradepass) AS gradepass 
                            FROM {assign} a
                            JOIN {course_modules} as cm ON cm.instance = a.id
                            JOIN {modules} m ON cm.module = m.id
                            JOIN {course} c ON c.id = cm.course
                            JOIN {grade_items} gi ON gi.courseid = c.id AND gi.itemmodule = 'assign' AND gi.iteminstance = a.id  
                           WHERE m.name = 'assign' AND cm.visible = 1 AND cm.deletioninprogress = 0 AND c.visible = 1 $where ";
                break;
            case 'grademax':
                $identy = 'gi.iteminstance';
                $query = "SELECT ROUND(gi.grademax)  AS grademax 
                            FROM {assign} a
                            JOIN {course_modules} as cm ON cm.instance = a.id
                            JOIN {modules} m ON cm.module = m.id
                            JOIN {course} c ON c.id = cm.course
                            JOIN {grade_items} gi ON gi.courseid = c.id AND gi.itemmodule = 'assign' AND gi.iteminstance = a.id   
                           WHERE m.name = 'assign' AND cm.visible = 1 AND cm.deletioninprogress = 0 AND c.visible = 1 $where  ";
            break;
            case 'submittedusers':
                $identy = 'asb.assignment';
                $courseid = 'a.course';
                $query = "SELECT COUNT(asb.id) AS submittedusers  
                            FROM {assign_submission} as asb 
                            JOIN {assign} a ON a.id = asb.assignment
                            WHERE asb.status = 'submitted' AND asb.userid in ($learnersql) $where ";
            break;
            case 'completedusers':
                $identy = 'cmo.instance';
                $courseid = 'cmo.course';
                $query = "SELECT COUNT(DISTINCT cmc.userid) AS completedusers  
                            FROM {course_modules_completion} AS cmc
                            JOIN {course_modules} as cmo ON cmo.id = cmc.coursemoduleid
                           WHERE cmc.userid in ($learnersql) AND cmc.completionstate > 0 
                             AND cmo.module = 1 AND cmo.visible = 1 $where ";
            break;
            case 'needgrading':
                $identy = 'asb.assignment';
                $query = "SELECT COUNT(asb.id) AS needgrading  
                            FROM {assign_submission} asb 
                            JOIN {grade_grades} g ON g.userid = asb.userid
                            JOIN {grade_items} gi ON gi.id = g.itemid
                            WHERE asb.status = 'submitted' AND asb.userid > 2 AND g.finalgrade IS NULL AND gi.iteminstance = asb.assignment
                                AND gi.itemmodule = 'assign' $where ";
            break;
            case 'avggrade':
                $identy = 'gi.iteminstance';
                $query = "SELECT ROUND(AVG(gg.finalgrade), 2) AS avggrade 
                            FROM {grade_grades} gg 
                            JOIN {grade_items} gi ON gi.id = gg.itemid 
                            WHERE gi.itemmodule = 'assign' $where ";
            break;
            case 'totaltimespent':
                $identy = 'cm.instance';
                $courseid = 'mt.courseid';
                $query = "SELECT SUM(mt.timespent) AS totaltimespent  
                            FROM {block_ls_modtimestats} mt 
                            JOIN {course_modules} cm ON cm.id = mt.activityid 
                            JOIN {modules} m ON m.id = cm.module
                            WHERE mt.userid in($learnersql) AND m.name = 'assign' $where ";
                break;
             case 'numviews':
                $identy = 'cm.instance';
                $courseid = 'lsl.courseid';
                if($this->reporttype == 'table'){
                    $query = "  SELECT * FROM ((SELECT COUNT(DISTINCT lsl.userid) as distinctusers 
                                  FROM {logstore_standard_log} lsl
                                  JOIN {course} c ON c.id = lsl.courseid 
                                  JOIN {user} u ON u.id = lsl.userid 
                                  JOIN {course_modules} cm ON lsl.contextinstanceid = cm.id
                                  JOIN {assign} q ON q.id = cm.instance 
                                  JOIN {modules} m ON m.id = cm.module
                                 WHERE lsl.crud = 'r' AND lsl.contextlevel = 70  AND lsl.anonymous = 0 AND u.id IN ($learnersql)
                                   AND lsl.userid > 2  AND u.confirmed = 1 AND u.deleted = 0  AND lsl.anonymous = 0 AND m.name = 'assign'
                                   $where ) 
                                  AS c1,
                                   (SELECT COUNT('X') as numviews 
                                      FROM {logstore_standard_log} lsl 
                                       JOIN {course} c ON c.id = lsl.courseid
                                      JOIN {user} u ON u.id = lsl.userid
                                     JOIN {course_modules} cm ON lsl.contextinstanceid = cm.id
                                     JOIN {assign} q ON q.id = cm.instance 
                                     JOIN {modules} m ON m.id = cm.module
                                     WHERE  lsl.crud = 'r' AND lsl.contextlevel = 70 AND lsl.userid > 2 AND u.id IN ($learnersql) AND lsl.anonymous = 0 AND u.confirmed = 1 AND u.deleted = 0 AND m.name = 'assign' $where ) AS c2)";
                }else{
                    $query = "  SELECT COUNT('X') as numviews 
                                  FROM {logstore_standard_log} lsl 
                                 JOIN {user} u ON u.id = lsl.userid
                                  JOIN {course} c ON c.id = lsl.courseid
                                 JOIN {course_modules} cm ON lsl.contextinstanceid = cm.id
                                 JOIN {assign} q ON q.id = cm.instance 
                                 JOIN {modules} m ON m.id = cm.module
                                 WHERE  lsl.crud = 'r' AND lsl.contextlevel = 70 AND lsl.userid > 2 AND u.id IN ($learnersql) AND lsl.anonymous = 0 AND u.confirmed = 1 AND u.deleted = 0 AND m.name = 'assign' $where";
                } 
            break;   
            default:
                return false;
                break;
        }
        $query = str_replace('%placeholder%', $identy, $query);
        $query = str_replace('%courseid%', $courseid, $query);
        return $query;
    }
}