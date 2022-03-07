<?php
/**
 *
 * @package   	local_course_change_name
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_change_name;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/outputcomponents.php');

use coding_exception;
use moodleform;
use html_writer;

class course_change_name_form extends moodleform {

    /**
     * Makes the form elements.
     */
    public function definition() {

        global $DB;

        $mform =& $this->_form;

	    $filter_course_name = optional_param('filter_course_name', '', PARAM_RAW);
	    $filter_shortname = optional_param('filter_shortname', '', PARAM_RAW);
	    $filter_courseid = optional_param('filter_courseid', '', PARAM_RAW);
	    $filter_categoryname = optional_param('filter_categoryname', '', PARAM_RAW);
	    $filter_category = optional_param('filter_category', '', PARAM_RAW);

	    $bt_rename = optional_param('bt_rename', '', PARAM_RAW);
	    if ($bt_rename != '') {
	    	$filter_course_name = $filter_shortname = $filter_courseid = $filter_categoryname = '';
	    }

        $sql = "SELECT id, name FROM {course_categories}"; // 1 => the ID of dashboard page
        $categories = $DB->get_records_sql($sql);

        $html_categories = '<select name="filter_category" id="filter_category" style="width: 100%">';
    	$html_categories .= '<option value="" '. ($filter_category==''?'selected':'') . '>Select a category</option>';
        foreach ($categories as $id => $category) {
        	$html_categories .= '<option value="'.$id.'" '. ($filter_category==$id && $bt_rename==''?'selected':'') . '>'. $category->name .'</option>';
        }
        $html_categories .= '</select>';

        $html = '
	        <div class="container">
				<div class="row">
				    <div class="col-sm-12 col-md-6" style="border-right: 1px solid #000;">
					    <p>'.get_string('info_filter', 'local_course_change_name').'</p>
					    <div class="row">
						    <div class="col-sm-12 col-md-4">
							    <span style="white-space: nowrap;">'.get_string('course_name_contains', 'local_course_change_name').'</span>
						    </div>
						    <div class="col-sm-12 col-md-8">
							    <input type="input" name="filter_course_name" id="filter_course_name" style="width: 100%; border: 1px solid #000;" value="'.$filter_course_name.'" />
						    </div>
					    </div>
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-4">
							    <span style="white-space: nowrap;">'.get_string('shortname_contains', 'local_course_change_name').'</span>
						    </div>
						    <div class="col-sm-12 col-md-8">
							    <input type="input" name="filter_shortname" id="filter_shortname" style="width: 100%; border: 1px solid #000;" value="'.$filter_shortname.'" />
						    </div>
					    </div>
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-4">
							    <span style="white-space: nowrap;">Course ID contains</span>
						    </div>
						    <div class="col-sm-12 col-md-8">
							    <input type="input" name="filter_courseid" id="filter_courseid" style="width: 100%; border: 1px solid #000;" value="'.$filter_courseid.'" />
						    </div>
					    </div>
					    <!--
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-4">
							    <span style="white-space: nowrap;">Category contains</span>
						    </div>
						    <div class="col-sm-12 col-md-8">
							    <input type="input" name="filter_categoryname" id="filter_categoryname" style="width: 100%; border: 1px solid #000;" value="'.$filter_categoryname.'" />
						    </div>
					    </div>
					    -->
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-4">
							    <span style="white-space: nowrap;">Category</span>
						    </div>
						    <div class="col-sm-12 col-md-8">
							    ' . $html_categories . '
						    </div>
					    </div>
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-4">
						    </div>
						    <div class="col-sm-12 col-md-8">
							    <input type="submit" name="bt_filter" id="bt_filter" value="Filter" style="padding: 3px 15px;" />
						    </div>
					    </div>
				    </div>
				    <div class="col-sm-12 col-md-6">
					    <p>'.get_string('info_rename', 'local_course_change_name').'</p>
					    <div class="row">
						    <div class="col-sm-12 col-md-3">
							    <span style="white-space: nowrap;">'.get_string('new_fullname', 'local_course_change_name').'</span>
						    </div>
						    <div class="col-sm-12 col-md-9">
							    <input type="input" name="new_coursefullname" id="new_coursefullname" style="width: 100%; border: 1px solid #000;" />
						    </div>
					    </div>
					    <!--
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-3">
							    <span style="white-space: nowrap;">'.get_string('new_shortname', 'local_course_change_name').'</span>
						    </div>
						    <div class="col-sm-12 col-md-9">
							    <input type="input" name="new_shortname" id="new_shortname" style="width: 100%; border: 1px solid #000;" />
						    </div>
					    </div>
					    -->
					    <div class="row" style="margin-top: 12px;">
						    <div class="col-sm-12 col-md-3">
						    </div>
						    <div class="col-sm-12 col-md-9">
							    <input type="submit" name="bt_rename" id="bt_rename" value="Rename" style="padding: 3px 15px;" />
							    <input type="hidden" name="hd_selected_courseids" id="hd_selected_courseids" value="" />
						    </div>
					    </div>
				    </div>
				</div>
			</div>
        ';

        $mform->addElement("html", $html);
    }

}

