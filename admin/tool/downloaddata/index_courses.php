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
 * Web interface for downloading courses.
 *
 * @package    tool_downloaddata
 * @copyright  2015 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/filelib.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/config.php');

core_php_time_limit::raise(60 * 60); // 1 hour.
raise_memory_limit(MEMORY_HUGE);

require_login();
admin_externalpage_setup('tooldownloaddata_courses');

$returnurl = new moodle_url('/admin/tool/downloaddata/index_courses.php');

// Checking for the 'sortbycategorypath' field because there might be session
// data carried over from the index_users.php page, which doesn't have the
// 'sortbycategorypath' option.
if (!isset($SESSION->customdata) || !isset($SESSION->customdata['sortbycategorypath'])) {
    // Adding the form defaults.
    $SESSION->customdata = tool_downloaddata_courses_form::get_default_form_values();
}

$mform = new tool_downloaddata_courses_form(null, $SESSION->customdata);

if ($formdata = $mform->get_data()) {
    // Adding all the valid fields.
    if (!empty($formdata->addallfields)) {
        $SESSION->customdata['selectedfields'] = tool_downloaddata_processor::get_valid_course_fields();

    // Removing all the selected fields.
    } else if (!empty($formdata->removeallfields)) {
        $SESSION->customdata['selectedfields'] = array();

    // Adding the selected fields.
    } else if (!empty($formdata->addfieldselection)) {
        if (!empty($formdata->availablefields)) {
            $validfields = tool_downloaddata_processor::get_valid_course_fields();
            foreach ($formdata->availablefields as $fieldindex) {
                $field = $validfields[intval($fieldindex)];
                if (!in_array($field, $SESSION->customdata['selectedfields'])) {
                    $SESSION->customdata['selectedfields'][] = $field;
                }
            }
        }

    // Removing the selected fields.
    } else if (!empty($formdata->removefieldselection)) {
        if (!empty($formdata->selectedfields) && !empty($SESSION->customdata['selectedfields'])) {
            foreach($formdata->selectedfields as $fieldindex) {
                unset($SESSION->customdata['selectedfields'][intval($fieldindex)]);
            }
        }

    // Downloading the courses.
    } else {
        $options = array();
        $options['format'] = $formdata->format;
        $options['data'] = tool_downloaddata_processor::DATA_COURSES;
        $options['encoding'] = $formdata->encoding;
        $options['usedefaults'] = false;
        $options['useoverrides'] = ($formdata->useoverrides == 'true');
        $options['sortbycategorypath'] = ($formdata->sortbycategorypath == 'true');
        $options['delimiter'] = $formdata->delimiter_name;

        if (!empty($SESSION->customdata['selectedfields'])) {
            $fields = $SESSION->customdata['selectedfields'];
        } else {
            throw new moodle_exception('emptyfields', 'tool_downloaddata', $returnurl);
        }

        $overrides = array();
        if ($options['useoverrides']) {
            try {
                $overrides = tool_downloaddata_process_overrides($formdata->overrides);
            } catch (Exception $e) {
                $e->link = $returnurl;
                throw $e;
            }
        }

        $processor = new tool_downloaddata_processor($options, $fields, null, $overrides);
        try {
            $processor->prepare();
        } catch (Exception $e) {
            $e->link = $returnurl;
            throw $e;
        }
        $processor->download();
    }

    unset($_POST);
    // Saving current data.
    $SESSION->customdata['format'] = $formdata->format;
    $SESSION->customdata['encoding'] = $formdata->encoding;
    $SESSION->customdata['delimiter_name'] = $formdata->delimiter_name;
    $SESSION->customdata['useoverrides'] = $formdata->useoverrides;
    $SESSION->customdata['sortbycategorypath'] = $formdata->sortbycategorypath;
    $SESSION->customdata['overrides'] = $formdata->overrides;
    $mform = new tool_downloaddata_courses_form(null, $SESSION->customdata);
} else {
    // Resetting the session data on a page refresh.
    $SESSION->customdata = tool_downloaddata_courses_form::get_default_form_values();
    $mform = new tool_downloaddata_courses_form(null, $SESSION->customdata);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
