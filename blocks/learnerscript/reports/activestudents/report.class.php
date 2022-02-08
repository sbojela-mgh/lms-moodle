<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License AS published by
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

/** LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @subpackage learnerscript
 * @author: <jahnavi@eabyas.in>
 * @date: 2020
 */
use block_learnerscript\local\querylib;
use block_learnerscript\local\reportbase;
use block_learnerscript\local\ls as ls;
use block_learnerscript\report;

class report_activestudents extends reportbase implements report {
    /**
     * [__construct description]
     * @param [type] $report           [description]
     * @param [type] $reportproperties [description]
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = false;
        $this->courselevel = true;
        $this->components = array('columns', 'filters', 'permissions', 'plot');
        $columns = ['learner', 'email', 'sessionjoinedat', 'sessionsduration'];
        $this->columns = ['bigbluebuttonfields' => $columns];
        $this->orderable = array('learner', 'email', 'sessionjoinedat'); 
        $this->basicparams = array(['name' => 'sessions']);
        $this->defaultcolumn = 'u.id';
        $this->excludedroles = array("'student'");
    }
    function init() {
        global $DB; 
        if (!$this->scheduling && isset($this->basicparams) && !empty($this->basicparams)) {
            $basicparams = array_column($this->basicparams, 'name');
            foreach ($basicparams AS $basicparam) {
                if (empty($this->params['filter_' . $basicparam])) {
                    return false;
                }
            }
        }
    }

    function count() {
        $this->sql = "SELECT COUNT(DISTINCT u.id) ";
    }

    function select() {
        $this->sql = "SELECT DISTINCT u.id, CONCAT(u.firstname, ' ', u.lastname) AS learner, u.email, MAX(bbl.timecreated) AS sessionjoinedat, bbl.bigbluebuttonbnid, 
        (SELECT SUM(mt.timespent) AS totaltimespent  
                            FROM {block_ls_modtimestats} mt 
                            JOIN {course_modules} cm1 ON cm1.id = mt.activityid 
                            JOIN {modules} m1 ON m1.id = cm1.module
                            WHERE mt.userid > 2 AND m.name = 'bigbluebuttonbn' AND mt.userid = u.id AND cm1.module = cm.module) AS sessionsduration";
        parent::select();
    }

    function from() {
        $this->sql .= " FROM {user} u ";
    }

    function joins() {
        $this->sql .= " JOIN {bigbluebuttonbn_logs} bbl ON bbl.userid = u.id  
                        JOIN {course_modules} cm ON cm.instance = bbl.bigbluebuttonbnid 
                        JOIN {modules} m ON m.id = cm.module AND m.name = 'bigbluebuttonbn'";
        parent::joins();
    }

    function where() { 
        parent::where();
    }

    function search() {
        
    }

    function filters() { 
        global $DB;
        if (isset($this->params['filter_session']) && $this->params['filter_session'] > 0) {
            $this->sql .= " JOIN {user_enrolments} ue ON ue.userid = bbl.userid 
                        JOIN {enrol} e ON e.id = ue.enrolid 
                        JOIN {role_assignments} ra ON ra.userid = ue.userid
                        JOIN {context} ct ON ct.id = ra.contextid
                        JOIN {role} rl ON rl.id = ra.roleid AND rl.shortname = 'student' 
                        JOIN {course} c ON c.id = bbl.courseid"; 
            $this->sql .= " WHERE bbl.log = 'Join' AND u.confirmed = 1 AND u.deleted = 0 "; 
            if ((!is_siteadmin() || $this->scheduling) && !(new ls)->is_manager()) {
                if ($this->rolewisecourses != '') {
                    $this->sql .= " AND bbl.courseid IN ($this->rolewisecourses) ";
                }
            } 
            if (isset($this->search) && $this->search) {
                $fields = array("CONCAT(u.firstname, ' ', u.lastname)");
                $fields = implode(" LIKE '%" . $this->search . "%' OR ", $fields);
                $fields .= " LIKE '%" . $this->search . "%' ";
                $this->sql .= " AND ($fields) ";
            }
            $sessionid = $this->params['filter_session'];
            $this->sql .= " AND bbl.bigbluebuttonbnid IN ($sessionid) "; 
            $courseid = $DB->get_field_sql("SELECT course FROM {bigbluebuttonbn} WHERE id = $sessionid"); 
            $this->sql .= " AND ct.instanceid = $courseid ";
        } 
        if ($this->ls_startdate > 0 && $this->ls_enddate) {
            $this->sql .= " AND bbl.timecreated BETWEEN $this->ls_startdate AND $this->ls_enddate ";
        }
    }
    /**
     * [get_rows description]
     * @param  array  $users [description]
     * @return [type]        [description]
     */
    public function get_rows($users = array()) {
        return $users;
    }
    // public function column_queries($columnname, $activityid, $courseid = null) {
    //     $where = " AND %placeholder% = $activityid";
    //     $filtercourseid = $this->params['filter_courses'];

    //     switch ($columnname) {      
    //         default:
    //             return false;
    //             break;
    //     }
    //     $query = str_replace('%placeholder%', $identy, $query);
    //     return $query;
    // }
}
