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

/** LearnerScript Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: eAbyas Info Solutions
 * @date: 2017
 */
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\ls;

class plugin_average extends pluginbase {

    function init() {
        $this->form = true;
        $this->unique = false;
        $this->fullname = get_string('average', 'block_learnerscript');
        $this->reporttypes = array('courses', 'users', 'timeline', 'categories','assignment','courses', 'coursesoverview', 'gradedactivity', 'myassignments', 'myquizs', 'quizzes', 'scorm', 'useractivities', 'userassignments', 'usercourses', 'userquizzes', 'usersresources', 'usersscorm', 'courseactivities', 'myscorm');
    }

    function summary($data) {
        global $DB, $CFG;
        if ($this->report->type != 'sql') {
            $components = (new ls)->cr_unserialize($this->report->components);
            if (!is_array($components) || empty($components['columns']['elements']))
                print_error('nocolumns');

            $columns = $components['columns']['elements'];
            $i = 0;
            foreach ($columns as $c) {
                if ($i == $data->column)
                    return $c['summary'];
                $i++;
            }
        }
        else {

            require_once($CFG->dirroot . '/blocks/learnerscript/report.class.php');
            require_once($CFG->dirroot . '/blocks/learnerscript/reports/' . $this->report->type . '/report.class.php');

            $reportclassname = 'report_' . $this->report->type;
            $reportclass = new $reportclassname($this->report);

            $components = (new ls)->cr_unserialize($this->report->components);
            $config = (isset($components['customsql']['config'])) ? $components['customsql']['config'] : new stdclass;
            if (isset($config->querysql)) {

                $sql = $config->querysql;
                $sql = $reportclass->prepare_sql($sql);
                if ($rs = $reportclass->execute_query($sql)) {
                    foreach ($rs['results'] as $row) {
                        $i = 0;
                        foreach ($row as $colname => $value) {
                            if ($i == $data->column)
                                return str_replace('_', ' ', $colname);
                            $i++;
                        }
                        break;
                    }
                  //  $rs->close();
                }
            }
        }

        return '';
    }

    function execute($rows) {

        $result = 0;
        $els = 0;

        foreach ($rows as $r) {
            $result += (is_numeric(trim(strip_tags($r)))) ? $r : 0;
            $els++;
        }

        if ($els == 0)
            $els = 1;

        $finalresult = ROUND($result / $els, 2);
        return $finalresult;
    }

}
