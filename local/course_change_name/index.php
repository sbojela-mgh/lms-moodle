<?php
/**
 *
 * @package     local_course_change_name
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/local/course_change_name/classes/course_change_name_form.php');

global $CFG, $DB, $USER;

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/local/course_change_name/index.php'), []);

$pagetitle = get_string('page_heading', 'local_course_change_name');

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'local_course_change_name'),
    new moodle_url('/admin/search.php#linkmodules'));
$PAGE->navbar->add(get_string('page_heading','local_course_change_name'));

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/course_change_name/js/script.js'));
$PAGE->requires->yui_module('moodle-core-notification', 'notification_init');

$button_filter = optional_param('bt_filter', '', PARAM_RAW);
$bt_rename = optional_param('bt_rename', '', PARAM_RAW);

$courses = [];
$success_message = '';

if($button_filter == 'Filter') {
    $filter_course_name = trim(optional_param('filter_course_name', '', PARAM_RAW));
    $filter_shortname = trim(optional_param('filter_shortname', '', PARAM_RAW));
    $filter_courseid = trim(optional_param('filter_courseid', '', PARAM_RAW));
    $filter_categoryname = trim(optional_param('filter_categoryname', '', PARAM_RAW));
    $filter_category = optional_param('filter_category', '', PARAM_RAW);

    if ($filter_course_name == '' && $filter_shortname == '' && $filter_courseid == '' && $filter_categoryname == '' && $filter_category == '') {
        // No querying DB
    }
    else {
        $where = '';
        if ($filter_course_name != '') {
            $filter_course_name = str_replace('"', '', $filter_course_name);
            $filter_course_name = str_replace("'", '', $filter_course_name);
            if($filter_course_name != '') {
                $where_course_name = " c.fullname LIKE '%" . $filter_course_name . "%'";
                $where .= ($where == '' ? $where_course_name : ' AND ' . $where_course_name);
            }
        }

        if ($filter_shortname != '') {
            $filter_shortname = str_replace('"', '', $filter_shortname);
            $filter_shortname = str_replace("'", '', $filter_shortname);
            if($filter_shortname != '') {
                $where_shortname = " c.shortname LIKE '%" . $filter_shortname . "%'";
                $where .= ($where == '' ? $where_shortname : ' AND ' . $where_shortname);
            }
        }

        if ($filter_courseid != '') {
            $filter_courseid = str_replace('"', '', $filter_courseid);
            $filter_courseid = str_replace("'", '', $filter_courseid);
            $where_courseid = " c.idnumber LIKE '%" . $filter_courseid . "%'";
            $where .= ($where == '' ? $where_courseid : ' AND ' . $where_courseid);
        }

        if ($filter_categoryname != '') {
            $filter_categoryname = str_replace('"', '', $filter_categoryname);
            $filter_categoryname = str_replace("'", '', $filter_categoryname);
            $where_catname = " cat.name LIKE '%" . $filter_categoryname . "%'";
            $where .= ($where == '' ? $where_catname : ' AND ' . $where_catname);
        }

        if ($filter_category != '') {
            $filter_category = str_replace('"', '', $filter_category);
            $filter_category = str_replace("'", '', $filter_category);
            $where_catid = " cat.id=" . $filter_category;
            $where .= ($where == '' ? $where_catid : ' AND ' . $where_catid);
        }

        $sql = "SELECT c.id, c.fullname, c.shortname, c.idnumber, cat.name as category_name FROM
            {course} c JOIN {course_categories} cat ON c.category = cat.id
            WHERE $where AND c.id <> 1"; // 1 => the ID of dashboard page

        $courses = $DB->get_records_sql($sql);
    }
}
elseif ($bt_rename == 'Rename') {

    $new_coursefullname = trim(optional_param('new_coursefullname', '', PARAM_RAW));
    $new_shortname = trim(optional_param('new_shortname', '', PARAM_RAW));
    $json_selected_courseids = trim(optional_param('hd_selected_courseids', '', PARAM_RAW));

    if (($new_coursefullname == '' && $new_shortname == '') || $json_selected_courseids == '') {
        // No action for wrong input
    }
    else {

        $selected_courseids = json_decode($json_selected_courseids);

        if(!empty($selected_courseids)) {

            $sql_in = '(' . implode(',', $selected_courseids) . ')';

            $sql_in = str_replace(' ', '', $sql_in);
            $sql_in = str_replace('(1,', '(', $sql_in); // 1 => the ID of dashboard page
            $sql_in = str_replace(',1)', ')', $sql_in);
            $sql_in = str_replace(',1,', ',', $sql_in);

            $sql_set = '';

            if($new_coursefullname != '') {
                $new_coursefullname = str_replace('"', '', $new_coursefullname);
                $new_coursefullname = str_replace("'", '', $new_coursefullname);
                if($new_coursefullname != '') {
                    if ($sql_set == '') {
                        $sql_set = " SET fullname='$new_coursefullname' ";
                    }
                }
            }

            if($new_shortname != '') {
                $new_shortname = str_replace('"', '', $new_shortname);
                $new_shortname = str_replace("'", '', $new_shortname);
                if($new_shortname != '') {
                    if ($sql_set == '') {
                        $sql_set = " SET shortname='$new_shortname' ";
                    }
                    else {
                        $sql_set .= ", shortname='$new_shortname' ";
                    }
                }
            }

            if ($sql_in != '()' && $sql_set != '') {
                $sql_update = 'UPDATE {course} ' . $sql_set . ' WHERE id IN ' . $sql_in;
                $DB->execute($sql_update);
                $nr_updated = count($selected_courseids);
                $info = $nr_updated == 1 ? '1 course ' : "$nr_updated courses ";
                $info .= get_string('rename_succeeded', 'local_course_change_name');
                $success_message = '<p style="margin-top: 30px;">' . $info . '</p>';
            }
        }
    }
}

echo $OUTPUT->header();

// Display the form
$configuration_form = new local_course_change_name\course_change_name_form(
    new moodle_url('/local/course_change_name/index.php'), []);

//$configuration_form->get_data();

$configuration_form->display();

if($button_filter == 'Filter' && empty($courses)) {
    echo '<p style="margin-top: 30px;">'.get_string('no_course_found', 'local_course_change_name').'</p>';
}

if (!empty($courses)) {

    $rows = '';
    foreach ($courses as $course) {
        $rows .= '<tr>';
        $rows .= '<td><input type="checkbox" name="single_course" value="' . $course->id . '"/></td>';
        $rows .= '<td>' . $course->fullname . '</td>';
        $rows .= '<td>' . $course->shortname . '</td>';
        $rows .= '<td>' . $course->idnumber . '</td>';
        $rows .= '<td>' . $course->category_name . '</td>';
        $rows .= '</tr>';
    }
    $html = '
        <div class="container" style="margin-top: 30px;">
            <div class="row">
                <div class="col-sm-12">
                    <table cellpadding="0" cellspacing="0" border="1" width="100%" rules="All" class="tbl_courses">
                        <tr>
                            <th width="5%"><input type="Checkbox" name="toggle_all_courses"/></th>
                            <th width="35%">Course Fullname</th>
                            <th width="20%">Course Shortname</th>
                            <th width="20%">Course ID Number</th>
                            <th width="20%">Course Category</th>
                        </tr>
                        ' . $rows . '
                    </table>
                </div>
            </div>
        </div>
        <style type="text/css">
            .tbl_courses th:first-child,.tbl_courses td:first-child { text-align: center; }
            .tbl_courses th, .tbl_courses td { padding: 5px 8px; }
            .tbl_courses input[type=checkbox] { cursor: pointer; }
        </style>
    ';

    echo $html;
}

if ($success_message != '') {
    echo $success_message;
}

echo $OUTPUT->footer();
