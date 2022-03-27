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
  * @author: prashanthi<Prashanthi@eabyas.in>
  * @date: 2016
  */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->libdir.'/formslib.php');
class topicwiseperformance_form extends moodleform {
    public function definition() {
      global $DB, $USER, $CFG;
      $courseid=optional_param('courseid', 0, PARAM_INT);
      $filter_courses=optional_param('filter_courses', 0, PARAM_INT);
      $mform =& $this->_form;
      $mform->addElement('header', '', get_string('topicwiseperformance','block_learnerscript'), '');
      $plist=array("fullname", "email");
	    // if($courseid ){
     //    $sectionsql=" SELECT cs.id FROM {course_sections} cs WHERE course={$courseid} ";
     //    $sections=$DB->get_records_sql($sectionsql);
     //    if($sections){
     //      foreach($sections as $section){
     //        $plist[]="sectionid_".$section->id;
     //      }
     //    } // end of function
     //  }
      $programlabels=array();
      foreach ($plist as $x => $x_value){
        $programlabels[$x_value]=$x_value;
      }
      $mform->addElement('select', 'column', get_string('column','block_learnerscript'), $programlabels);
	    $this->_customdata['compclass']->add_form_elements($mform,$this);
      // buttons
      $this->add_action_buttons(true, get_string('add'));
    }
	public function validation($data, $files){
		$errors = parent::validation($data, $files);
		$errors = $this->_customdata['compclass']->validate_form_elements($data,$errors);
		return $errors;
	}
}