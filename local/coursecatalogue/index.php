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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
 
require_once '../../config.php';
require_once($CFG->dirroot.'/lib/moodlelib.php');
require_once($CFG->dirroot . '/mod/data/lib.php');
require_once($CFG->dirroot. "/lib/tablelib.php");
#require_once($CFG->dirroot. "/blocks/rate_course/block_rate_course.php");
global $USER, $DB, $CFG;
 
$PAGE->set_url('/local/coursecatalogue/index.php');
$PAGE->set_context(context_system::instance());
 
require_login();
 
$strpagetitle = get_string('coursecatalogue', 'local_coursecatalogue');
$strpageheading = get_string('coursecatalogue', 'local_coursecatalogue');
 
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);
 
#$obj = new stdClass;
#$obj->test = 'abc';
#$obj->other = 6.2;
#$obj->arr = array (1, 2, 3);
 
#echo $OUTPUT->header();
 
if (isset($_GET['tsort'])){
 $orderby = $_GET['tsort'];
 $dir = $_GET['tdir'];
 if($dir == 3){
 $sort = 'ASC';
 }
 else {
 $sort = 'DESC';
 }
 $sql = "SELECT c.fullname as name, c.idnumber as id, c.startdate as startdate, CONCAT(u.firstname, ' ', u.lastname) as instructor
 FROM {course} c, {enrol} e, {user_enrolments} u_e, {user} u, {role_assignments} r_a, {role} r
 WHERE
 u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND e.courseid = c.id
 ORDER BY ". $orderby. " ". $sort. ";";
}
else{
 $sql = "SELECT c.fullname as name, c.idnumber as id, c.startdate as startdate, CONCAT(u.firstname, ' ', u.lastname) as instructor
 FROM {course} c, {enrol} e, {user_enrolments} u_e, {user} u, {role_assignments} r_a, {role} r
 WHERE
 u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND e.courseid = c.id; ";
}
 
#$tagfeed = new core_tag\output\tagfeed();


 
#$rows = $DB->get_records_sql($sql);
#foreach($rows as $row){
# $course_name = $row->
 
#}





 
//echo $content;
 
//the open brackets at the end is there to add a data object
//echo $OUTPUT->render_from_template('local_coursecatalogue/searchbars', []);
#echo $OUTPUT->render_from_template('local_coursecatalogue/searchresults', []);
#$block = block_instance('rate_course');
#$block->display_rating(480);
$rec=$DB->get_records_sql($sql);
$table=new flexible_table('course_table');
$table->define_columns(array('idnumber', 'name', 'startdate', 'instructor'));
$table->define_headers(array('idnumber', 'course name', 'start date', 'instructor'));
$table->define_baseurl($PAGE->url);
$table->sortable(true);
$table->collapsible(true);
$table->setup();
$table->head = array('idnumber', 'name', 'date','instructor');
if (!$table->is_downloading()){
 echo $OUTPUT->header();
 echo $OUTPUT->skip_link_target();
}
foreach ($rec as $record) {
 #$block->display_rating($course->id);
 #$rating = $rating_obj->get_rating($record->id);
 $url = new moodle_url('/course/view.php', array('idnumber' => $record->id));
 $name = html_writer::link($url, $record->name);
 #$instructor = $record->firstname.' '.$record->lastname;
 $table->add_data(array($record->id, $name, date('M-d-yy h:mA', $record->startdate), $record->instructor));
}
$table->finish_output();
echo $OUTPUT->footer();