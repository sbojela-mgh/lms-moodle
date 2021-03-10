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

$strpagetitle = get_string('pluginname', 'local_coursecatalogue');
$strpageheading = get_string('headername', 'local_coursecatalogue');


$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

require_login();

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_coursecatalogue/searchbar', []);
echo '<div class="card">';
echo '<table class="table table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Course</th>';
echo '<th>Start Date</th>';
echo '<th>Instructor</th>';
echo '<th>Tags</th>';
echo '<th>Ratings</th>';
echo '</tr>';
echo '</thead>';

$sql = "SELECT * FROM {course} WHERE ID is not null and ID <> 1";
$courses = $DB->get_records_sql($sql);
  sort($courses);

foreach($courses as $course){
  
  echo '<tr>'; 
  echo '<td>'.'<a href=/lms-moodle/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';
  echo '<td>'.date('M-d-Y hA', $course->startdate).'</td>';
  //next line
  

$sql = "SELECT u.firstname, u.lastname FROM {user} as u
JOIN {role_assignments} as ra ON ra.userid = u.id
JOIN {role} as r ON ra.roleid = r.id
JOIN {context} as con ON ra.contextid = con.id
JOIN {course} as c ON c.id = con.instanceid AND con.contextlevel = 50
WHERE r.shortname = 'editingteacher'";
   echo '<td>';
$teachers = $DB->get_records_sql($sql);
 foreach($teachers as $teacher){
   echo $teacher->firstname;
   echo ' ';
   echo $teacher->lastname;   
   echo ' ';   
 }
 echo '</td>';
 
$sql = "SELECT t.name FROM mdl_tag AS t 
JOIN mdl_tag_instance AS ti ON ti.tagid = t.id
JOIN mdl_context AS ctx ON ctx.`contextlevel` = 50";


$tags = $DB->get_records_sql($sql);
echo '<td>';
foreach($tags as $tag){
  echo $tag->name;
}
echo '</td>';

echo '<td>';
$block = block_instance('rate_course');
$block->display_rating($course->id);
echo '</td>';
};
echo '</tr>';

echo '</table>';

echo $OUTPUT->footer();

