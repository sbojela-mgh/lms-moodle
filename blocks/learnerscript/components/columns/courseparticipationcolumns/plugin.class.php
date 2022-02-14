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
namespace block_learnerscript\lsreports;
use block_learnerscript\local\pluginbase;
use block_learnerscript\local\reportbase;
use block_learnerscript\local\ls;
use context_system;
use html_writer;

class plugin_courseparticipationcolumns extends pluginbase{
	public function init(){
		$this->fullname = get_string('courseparticipationcolumns', 'block_learnerscript');
		$this->type = 'undefined';
		$this->form = true;
		$this->reporttypes = array('courseparticipation');
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
	public function execute($data,$row,$user,$courseid,$starttime=0,$endtime=0,$reporttype = 'table'){
		global $DB, $CFG, $USER;
        $context = context_system::instance();
		$usercoursesReportID = $DB->get_field('block_learnerscript', 'id', array('type' => 'usercourses'), IGNORE_MULTIPLE);
        $competencyreportid =  $DB->get_field('block_learnerscript', 'id', array('type' => 'coursecompetency'), IGNORE_MULTIPLE);
  	switch($data->column){
      case 'courseenddate':
          $row->{$data->column} = $row->{$data->column} ? userdate($row->{$data->column}) : '';              
      break;
			case 'categorypath':
          $catlistarray = [];
          if(isset($row->{$data->column})){
             $cats=explode("/",$row->{$data->column}); 
              $countcats=sizeof($cats); 
               for($counter=0;$counter<$countcats;$counter++){ 
                $catname = $DB->get_record("course_categories", array("id" => $cats[$counter]) ); 
                 $catlistarray[] =  $catname->name;
               }         
             $row->{$data->column} =  $catlistarray ? implode("/", $catlistarray) : '';
          } else {
              $row->{$data->column} = $row->{$data->column} ? $row->{$data->column}  : '';
          }              
      break;
			case 'totalenroledstudents':
          if(!isset($row->totalenroledstudents) && isset($data->subquery)){
            $row->{$data->column} = $DB->get_field_sql($data->subquery);
          } else {
            $row->{$data->column} = $row->{$data->column};
          }
			break;
			case 'totalcompletedstudents':
          if(!isset($row->totalcompletedstudents) && isset($data->subquery)){
             $row->{$data->column} =  $DB->get_field_sql($data->subquery);
          } else {
              $row->{$data->column}= $row->{$data->column};
          }
			break;
			default:
				return (isset($row->{$data->column}))? $row->{$data->column} : '--';
			break;

		}
		return (isset($row->{$data->column}))? $row->{$data->column} : '--';
	}
}
