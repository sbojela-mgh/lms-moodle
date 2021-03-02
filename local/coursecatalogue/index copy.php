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

require_once '../../config.php';
require_once($CFG->dirroot.'/lib/moodlelib.php');
require_once($CFG->dirroot . '/mod/data/lib.php');
require_once($CFG->dirroot. "/lib/tablelib.php");
#require_once($CFG->dirroot. "/blocks/rate_course/block_rate_course.php");
global $USER, $DB, $CFG;

$PAGE->set_url('/local/coursecatalogue/index.php');
$PAGE->set_context(context_system::instance());


require_login();

$strpagetitle = get_string('pluginname', 'local_coursecatalogue');
$strpageheading = get_string('headername', 'local_coursecatalogue');


$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

if (isset($_GET['tsort'])){
    $orderby = $_GET['tsort'];
    $dir = $_GET['tdir'];
    if($dir == 3){
        $sort = 'ASC';
    }
    else {
        $sort = 'DESC';
    }
    $sql = "SELECT c.fullname as name, c.id as id, c.startdate as startdate, CONCAT(u.firstname, \' \', u.lastname) as instructor FROM mdl_course as c, mdl_enrol e, mdl_user_enrolments as u_e, mdl_user as u, mdl_role_assignments as r_a, mdl_role as r WHERE u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND e.courseid = c.id";

}
else{
    $sql = "SELECT c.fullname as name, c.id as id, c.startdate as startdate, CONCAT(u.firstname, ' ', u.lastname) as instructor
        FROM {course} as c, {enrol} as e, {user_enrolments} as u_e, {user} as u, {role_assignments} as r_a, {role} as r
        WHERE
        u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND e.courseid = c.id; ";
}

$results = new stdClass();

$rec=$DB->get_records_sql($sql);
$table=new flexible_table('course_table');
$table->define_columns(array('id', 'name', 'startdate', 'instructor'));
$table->define_headers(array('id', 'course name', 'start date', 'instructor'));
$table->define_baseurl($PAGE->url);
$table->sortable(true);
$table->collapsible(true);
$table->setup();
$table->head = array('id', 'name', 'date','instructor');


if (!$table->is_downloading()){
    echo $OUTPUT->header();
    echo $OUTPUT->skip_link_target();
}
foreach ($rec as $record) {
    #$block->display_rating($course->id);
    #$rating = $rating_obj->get_rating($record->id);
    $url = new moodle_url('/course/view.php', array('id' => $record->id));
    $name = html_writer::link($url, $record->name);
    #$instructor = $record->firstname.' '.$record->lastname;
    $table->add_data(array($record->id, $name, date('Y-m-d', $record->startdate), $record->instructor));
}
$table->finish_output();
$results->data = $table;
echo $OUTPUT->render_from_template('local_coursecatalogue/searchbar', []);
echo $OUTPUT->render_from_template('local_coursecatalogue/searchresults',[$results]);
echo $OUTPUT->footer();
