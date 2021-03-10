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

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_coursecatalogue/searchbar', []);
echo '<div class="card">';
echo '<table class="table table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Course</th>';
//echo '<th></th>';
echo '<th>Start Date</th>';
echo '<th>Main Instructor</th>';
echo '<th>Tags</th>';
echo '<th>Ratings</th>';
echo '</tr>';

$sql = "SELECT * FROM {course} WHERE ID is not null and fullname <> 'Local Environment' AND ID <> 1";


//$sql = "SELECT c.fullname as fullname, c.id as id, c.startdate as startdate
//FROM {course} c, {enrol} e, {user_enrolments} u_e, {user} u, {role_assignments} r_a, {role} r";

$courses = $DB->get_records_sql($sql); 
  sort($courses);

$rowsperpage = 15;
$totalpages = ceil(count($courses) / $rowsperpage);

if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
  // cast var as int
  $currentpage = (int) $_GET['currentpage'];
} else {
  // default page num
  $currentpage = 1;
} // end if

// if current page is greater than total pages...
if ($currentpage > $totalpages) {
  // set current page to last page
  $currentpage = $totalpages;
} // end if
// if current page is less than first page...
if ($currentpage < 1) {
  // set current page to first page
  $currentpage = 1;
}

// the offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;

$sql = "SELECT * FROM {course} WHERE ID is not null and fullname <> 'Local Environment' AND ID <> 1 LIMIT $offset, $rowsperpage";

$courses = $DB->get_records_sql($sql); 
  sort($courses);

foreach($courses as $course){
  if (isset($_GET['month'])){ //this one is to filter out courses that don't match the month
      if ($_GET['month'] != date('M', $course->startdate)){
        continue;
      }
  }
  if (isset($_GET['tags'])){ //this one is to filter out courses that don't contain a particular tag we passed
      //echo $_GET['tags'];
      $sql = "SELECT t.name 
        FROM {tag} t, {tag_instance} t_i
        WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;

      $tags = $DB->get_records_sql($sql);
      $match_flag = 0; //as we iterate through all the tags assigned to a particular course, we wanna find a match
      foreach ($tags as $tag){
        if ($_GET['tags'] == $tag->name){ //if we find a match, we set the flag to 1
            $match_flag = 1;
        }
      }
      if ($match_flag == 0){ //if we didn't find a match, skiip this iteration in the loop
        continue;
      }
  }
  if (isset($_GET['year'])){ //this one is to filter out anything that doesn't match the year
      if ($_GET['year'] != date('Y', $course->startdate)) {
          continue;
      }
  }
  if (isset($_GET['search'])){ // this one is to check for string patterns
    $to_match = $_GET['search'];
    $regex_mode = "/.*".$to_match.".*/i"; // surround the string we passed in the search with wildcard characters
    if (preg_match($regex_mode, $course->fullname) != 1){
        continue;
    }
  }

  //echo '<br>';
  echo '<tr>'; 
  echo '<td>'.'<a href=/lms-moodle/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';
  echo '<td>'.date('M-d-Y hA', $course->startdate).'</td>';
  //next line
  //echo '<td>'.$course->instructor.'</td>';
  $sql = "SELECT u.firstname, u.lastname
          FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
          WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
          
  echo '<td>';
  $teachers = $DB->get_records_sql($sql);
  foreach($teachers as $teacher){
 
    echo $teacher->firstname;
    echo ' ';
    echo $teacher->lastname;   
    echo ' ';
    echo '<br>';   
    
  }
  echo '</td>';
  
    
/*
$sql = "SELECT u.firstname, u.lastname FROM {user} as u
JOIN {role_assignments} as ra ON ra.userid = u.id
JOIN {role} as r ON ra.roleid = r.id
JOIN {context} as con ON ra.contextid = con.id
JOIN {course} as c ON c.id = con.instanceid AND con.contextlevel = 50
WHERE r.shortname = 'editingteacher'";

$teachers = $DB->get_records_sql($sql);
 foreach($teachers as $teacher){

   echo $teacher->firstname;
   echo ' ';
   echo $teacher->lastname;   
   echo ' ';   
   
 }
   echo '<td>';
   */
 
#$sql = "SELECT t.name FROM mdl_tag AS t 
#JOIN mdl_tag_instance AS ti ON ti.tagid = t.id
#JOIN mdl_context AS ctx ON ctx.contextlevel = 50";

$sql = "SELECT t.name 
        FROM {tag} t, {tag_instance} t_i
        WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;

$tags = $DB->get_records_sql($sql);
echo '<td>';
foreach($tags as $tag){
  echo $tag->name;
  echo '<br>';
}
echo '</td>';

echo '<td>';
$block = block_instance('rate_course');
$block->display_rating($course->id);
echo '</td>';
};

echo '</tr>';
echo '<tr>';
echo '<td colspan = 5 style="text-align:center;">';
$range = 3;

// if not on page 1, don't show back links
if ($currentpage > 1) {
   // show << link to go back to page 1
   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=1'><<</a> ";
   // get previous page num
   $prevpage = $currentpage - 1;
   // show < link to go back to 1 page
   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$prevpage'><</a> ";
} // end if 

// loop to show links to range of pages around current page
for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
   // if it's a valid page number...
   if (($x > 0) && ($x <= $totalpages)) {
      // if we're on current page...
      if ($x == $currentpage) {
         // 'highlight' it but don't make a link
         echo " [<b>$x</b>] ";
      // if not current page...
      } else {
         // make it a link
         echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$x'>$x</a> ";
      } // end else
   } // end if 
} // end for

// if not on last page, show forward and last page links        
if ($currentpage != $totalpages) {
   // get next page
   $nextpage = $currentpage + 1;
    // echo forward link for next page 
   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$nextpage'>></a> ";
   // echo forward link for lastpage
   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$totalpages'>>></a> ";
} // end if
echo '</td>';
echo '</tr>';
/****** end build pagination links ******/
echo '</table>';


echo $OUTPUT->footer();




