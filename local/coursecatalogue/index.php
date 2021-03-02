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
global $USER, $DB, $CFG;

$PAGE->set_url('/local/coursecatalogue/index.php');
$PAGE->set_context(context_system::instance());

<<<<<<< HEAD

require_login();

$strpagetitle = get_string('coursecatalogue', 'local_coursecatalogue');
$strpageheading = get_string('coursecatalogue', 'local_coursecatalogue');
=======
$strpagetitle = get_string('pluginname', 'local_coursecatalogue');
$strpageheading = get_string('headername', 'local_coursecatalogue');
>>>>>>> origin/feature-sam


$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

<<<<<<< HEAD

if (isset($_GET['tsort'])){
    $orderby = $_GET['tsort'];
    $dir = $_GET['tdir'];
    if($dir == 3){
        $sort = 'ASC';
    }
    else {
        $sort = 'DESC';
    }
    $sql = "SELECT c.fullname as name, c.id as id, c.startdate as startdate, CONCAT(u.firstname, ' ', u.lastname) as instructor
    FROM {course} c, {enrol} e, {user_enrolments} u_e, {user} u, {role_assignments} r_a, {role} r
    WHERE
    u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND e.courseid = c.id
    ORDER BY ". $orderby. " ". $sort. ";";
}
else{
    $sql = "SELECT c.fullname as name, c.id as id, c.startdate as startdate, CONCAT(u.firstname, ' ', u.lastname) as instructor
        FROM {course} c, {enrol} e, {user_enrolments} u_e, {user} u, {role_assignments} r_a, {role} r
        WHERE
        u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND e.courseid = c.id; ";
}

//echo $OUTPUT->render_from_template('local_coursecatalogue/searchbars', []);
#echo $OUTPUT->render_from_template('local_coursecatalogue/searchresults', []);

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

echo $OUTPUT->render_from_template('local_coursecatalogue/searchbars', []);
$OUTPUT->render_from_template('local_coursecatalogue/searchresults', []);
$table->finish_output();
=======
require_login();

echo $OUTPUT->header();

//echo '<hr><p>test <span style = "margin:50px">test 2</span></p></hr>';
/**
 * This is just for testing
 * $sql = "SELECT u.firstname,u.lastname from mdl_user as u where id in (select id from mdl_role_assignments where roleid in( select id from mdl_role where shortname = 'editingteacher'))";
 * //echo '<p> Query: <b>' . $sql . '</b></p>';
 * $results = $DB->get_records_sql($sql);
 * echo 'Query results:';
 * 
 * Testing purposes
 * print_r ($results);
*/
/** 
 * $userid = $DB->get_record_select('user', 'username=? ',array('admin'));echo $userid->firstname;
 * echo ' ';
 * echo $userid->lastname;
 * echo '</br>';
 * $role = $DB->get_record_select('role_assignments', 'userid =?', array($userid->id));
 * echo $role->id;
 * echo '</br>';
 * $roleassignments = $DB->get_record_select('role', 'id =?', array($role->id));
 * echo $roleassignments->shortname;
*/
/**
*$getrole = $DB->get_record_select('role', 'shortname=?', array('editingteacher'));

*Display the teacher role id >>Just incase purposes
*echo $getrole->id;
*echo '<br>';

*$getteachers = $DB->get_records_select('role_assignments','roleid=?', *array($getrole->id));
*print_r($getteachers);
*echo '<br>';
*
*foreach($getteachers as $showteachers){
*  $getteacherinfo = $DB->get_record_select('user', 'id=?', array
* ($showteachers->userid));
*
*  echo 'Teacher Name: ';
*  echo $getteacherinfo->firstname;
*  echo ' ';
*  echo $getteacherinfo->lastname;
*  echo '<br>';
*}
*/
echo $OUTPUT->render_from_template('local_coursecatalogue/searchbar', []);
echo '<div class="card">';
echo '<table class="table table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Course</th>';
echo '<th></th>';
echo '<th>Start Date</th>';
echo '<th>Instructor</th>';
echo '<th>Tags</th>';
echo '<th>Ratings</th>';
echo '</tr>';

$sql = "SELECT * FROM {course} WHERE ID is not null";
$courses = $DB->get_records_sql($sql);
  sort($courses);

foreach($courses as $course){
  
  echo '<br>';
  echo '<tr>';
  echo '<td>'.'<a href=/course/view.php?id=.$course->id.>'.$course->fullname.'</a>'.'<td>';
  echo '<td>'.date('m-d-yy h:m', $course->startdate).'<td>';
  //next line
  echo '<br>';

$sql = "SELECT firstname FROM {user} as u
JOIN {role_assignments} as ra ON ra.userid = u.id
JOIN {role} as r ON ra.roleid = r.id
JOIN {context} as con ON ra.contextid = con.id
JOIN {course} as c ON c.id = con.instanceid AND con.contextlevel = 50
WHERE r.shortname = 'editingteacher'";

$teachers = $DB->get_records_sql($sql);
 foreach($teachers as $teacher){
   echo $teacher->firstname;
 }
     
};
//print_r($courses);

>>>>>>> origin/feature-sam
echo $OUTPUT->footer();