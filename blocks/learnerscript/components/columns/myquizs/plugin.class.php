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
  * @subpackage learnerscript
  * @author: sowmya<sowmya@eabyas.in>
  * @date: 2016
  */
use block_learnerscript\local\pluginbase;
require_once $CFG->dirroot . '/lib/gradelib.php';

class plugin_myquizs extends pluginbase{
	public function init(){
		$this->fullname = get_string('myquizs','block_learnerscript');
		$this->type = 'undefined';
		$this->form = true;
		$this->reporttypes = array('myquizs');
	}
	public function summary($data){
		return format_string($data->columname);
	}
	public function colformat($data){
		$align = (isset($data->align))? $data->align : '';
		$size = (isset($data->size))? $data->size : '';
		$wrap = (isset($data->wrap))? $data->wrap : '';
		return array($align,$size,$wrap);
	}
	public function execute($data,$row,$user,$courseid,$starttime=0,$endtime=0,$reporttype){
		global $DB, $OUTPUT;
        $quizattemptstatus = $DB->get_field_sql("SELECT state FROM {quiz_attempts}
                                                  WHERE quiz = $row->id AND userid = $row->userid
                                                  ORDER BY id DESC LIMIT 1 ");
        $quizcomppletion = $DB->get_field_sql("SELECT id FROM {course_modules_completion}
                                                  WHERE coursemoduleid = $row->activityid AND completionstate > 0
                                                  AND userid = $row->userid
                                                  ORDER BY id DESC LIMIT 1 ");
		switch ($data->column) {
			case 'gradepass':
	            if(!isset($row->gradepass) && isset($data->subquery)){
	                $gradepass =  $DB->get_field_sql($data->subquery);
	            }else{
	                $gradepass = $row->{$data->column};
	            }
	            if($reporttype == 'table'){
                    $row->{$data->column} = !empty($gradepass) ? $gradepass : '--';
                }else{
                    $row->{$data->column} = !empty($gradepass) ? $gradepass : 0;
                }
        		break;
		    case 'finalgrade':
	            if(!isset($row->finalgrade) && isset($data->subquery)){
	                $finalgrade =  $DB->get_field_sql($data->subquery);
	            }else{
	                $finalgrade = $row->{$data->column};
	            }
	            if($reporttype == 'table'){
                    $row->{$data->column} = !empty($finalgrade) ? $finalgrade : '--';
                }else{
                    $row->{$data->column} = !empty($finalgrade) ? $finalgrade : 0;
                }
        		break;
        	case 'lowestgrade':
	            if(!isset($row->lowestgrade) && isset($data->subquery)){
	                $lowestgrade =  $DB->get_field_sql($data->subquery);
	            }else{
	                $lowestgrade = $row->{$data->column};
	            }
	           if($reporttype == 'table'){
                    $row->{$data->column} = !empty($lowestgrade) ? $lowestgrade : '--';
                }else{
                    $row->{$data->column} = !empty($lowestgrade) ? $lowestgrade : 0;
                }
        		break;
        	case 'highestgrade':
	            if(!isset($row->highestgrade) && isset($data->subquery)){
	                $highestgrade =  $DB->get_field_sql($data->subquery);
	            }else{
	                $highestgrade = $row->{$data->column};
	            }
	            if($reporttype == 'table'){
                    $row->{$data->column} = !empty($highestgrade) ? $highestgrade : '--';
                }else{
                    $row->{$data->column} = !empty($highestgrade) ? $highestgrade : 0;
                }
        		break;
        	case 'quizattempts':
	            if(!isset($row->quizattempts) && isset($data->subquery)){
	                $quizattempts =  $DB->get_field_sql($data->subquery);
	            }else{
	                $quizattempts = $row->{$data->column};
	            }
	            $row->{$data->column} = !empty($quizattempts) ? $quizattempts : '--';
        		break;
            case 'grademax':
                if(!isset($row->grademax) && isset($data->subquery)){
	                $grademax =  $DB->get_field_sql($data->subquery);
	            }else{
	                $grademax = $row->{$data->column};
	            }
	             if($reporttype == 'table'){
                    $row->{$data->column} = !empty($grademax) ? $grademax : '--';
                }else{
                    $row->{$data->column} = !empty($grademax) ? $grademax : 0;
                }
        		break;
			case 'state':
		 		$courserecord = $DB->get_record('course', array('id' => $row->courseid));
		        $completion_info = new completion_info($courserecord);
		        if (empty($quizattemptstatus) && empty($quizcomppletion)) {
		            $completionstatus = '<span class="notyetstart">Not Yet Started</span>';
		        } else if ($quizattemptstatus == 'inprogress' && empty($quizcomppletion)) {
		            $completionstatus = 'In Progress';
		        } else if ($quizattemptstatus == 'finished' && empty($quizcomppletion)) {
		            $completionstatus = 'Finished';
		        } else if ($quizattemptstatus == 'finished' || !empty($quizcomppletion)) {
		            $cm = new stdClass();
		            $cm->id = $row->activityid;
		                $completion = $completion_info->get_data($cm, false, $row->userid);
		                switch($completion->completionstate) {
		                    case COMPLETION_INCOMPLETE :
		                        $completionstatus = 'In-Progress';
		                    break;
		                    case COMPLETION_COMPLETE :
		                        $completionstatus = 'Completed';
		                    break;
		                    case COMPLETION_COMPLETE_PASS :
		                        $completionstatus = 'Completed (achieved pass grade)';
		                    break;
		                    case COMPLETION_COMPLETE_FAIL :
		                        $completionstatus = 'Fail';
		                    break;
		            }
		        }
		       $row->{$data->column} =  !empty($completionstatus) ? $completionstatus : '--';
		    break;
            case 'status':
            $userfinalgrade = $DB->get_field_sql("SELECT ROUND(gg.finalgrade, 2) 
            	AS finalgrade  FROM {grade_grades} gg  
                            JOIN {grade_items} gi ON gg.itemid = gi.id  
                           WHERE 1 = 1 AND gi.itemmodule = 'quiz' AND gg.userid = $row->userid 
                           AND gi.iteminstance = $row->id");
            $usergradepass = $DB->get_field_sql("SELECT ROUND(gi.gradepass, 2)  AS gradepass FROM {grade_items} as gi WHERE gi.itemmodule = 'quiz' AND gi.iteminstance = $row->id");
            if (empty($quizattemptstatus) && empty($quizcomppletion) && empty($userfinalgrade)){
                $row->{$data->column} = '--';
            }else if($userfinalgrade >= $usergradepass){
                $row->{$data->column} = 'Pass';
            }else if(is_null($userfinalgrade) || $userfinalgrade == '--' || $usergradepass == 0 || ($row->gradetype == GRADE_TYPE_SCALE && !grade_floats_different($usergradepass, 0.0))){
                $row->{$data->column} = '--';
            }else{
                $row->{$data->column} = 'Fail';
            }

            break;

        }
		return (isset($row->{$data->column}))? $row->{$data->column} : '';
	}
}
