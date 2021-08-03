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

global $USER, $DB, $CFG;

$PAGE->set_url('/local/coursecatalogue/index.php');
$PAGE->set_context(context_system::instance());

require_login();

$strpagetitle = get_string('pluginname', 'local_coursecatalogue');
$strpageheading = get_string('headername', 'local_coursecatalogue');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

echo $OUTPUT->header();

function array_push_assoc($array, $key, $value){
  $array[$key] = $value;
  return $array;
}

$context = array();
if (isset($_GET['month'])) {
  $context = array_push_assoc($context, 'month', $_GET['month']);
}else{
  $context = array_push_assoc($context, 'month', '');
}

if (isset($_GET['year'])) {
  $context = array_push_assoc($context, 'year', $_GET['year']);
}else{
  $context = array_push_assoc($context, 'year', '');
}
//no sort on commented attribute

if (isset($_GET['competency'])) {
  $context = array_push_assoc($context, 'competency', $_GET['competency']);
}else{
  $context = array_push_assoc($context, 'competency', '');
}

if (isset($_GET['role'])) {
  $context = array_push_assoc($context, 'role', $_GET['role']);
}else{
  $context = array_push_assoc($context, 'role', '');
}

if (isset($_GET['programs'])) {
  $context = array_push_assoc($context, 'programs', $_GET['programs']);
}else{
  $context = array_push_assoc($context, 'programs', '');
}

if (isset($_GET['level'])) {
  $context = array_push_assoc($context, 'level', $_GET['level']);
}else{
  $context = array_push_assoc($context, 'level', '');
}
if (isset($_GET['level'])) {
  $sort = $_GET['level'];
  switch($sort){
    case "entry":
      $context = array_push_assoc($context, 'level', "Entry");
      break;
    case "intermediate":
      $context = array_push_assoc($context, 'level', "Intermediate");
      break; 
    case "advanced":
      $context = array_push_assoc($context, 'level', "Advanced");
      break; 
    }
  $context = array_push_assoc($context, 'level', $_GET['level']);
}else{
  $context = array_push_assoc($context, 'level', '');
}

if (isset($_GET['format'])) {
  $context = array_push_assoc($context, 'format', $_GET['format']);
}else{
  $context = array_push_assoc($context, 'format', '');
}

if (isset($_GET['stars'])) {
  $context = array_push_assoc($context, 'stars', $_GET['stars']);
}else{
  $context = array_push_assoc($context, 'stars', '');
}


if (isset($_GET['department'])){
  $dept = $_GET['department'];
  switch($dept){ //so far we have 3 departments 1 -> DCR, 2 -> CFD, 3-> MGRI, check mdl_customfield_field options column for any new options
    case "1":
      $context = array_push_assoc($context, 'departmentname', "Department of Clinical Research");
      break;
    case "2":
      $context = array_push_assoc($context, 'departmentname', "Center for Faculty Development");
      break;
    //not in use yet
   /* case "3":
      $context = array_push_assoc($context, 'departmentname', "MGRI");
      break; */
  }
  $context = array_push_assoc($context, 'department', $_GET['department']);
}else{
  $context = array_push_assoc($context, 'department', '');
}

if (isset($_GET['tsort'])) {
  $sort = $_GET['tsort'];
  switch($sort){
    case "livecourses":
      $context = array_push_assoc($context, 'tsortname', "Live Courses");
      break;
    case "ondemand":
      $context = array_push_assoc($context, 'tsortname', "On Demand");
      break; 
    }
  $context = array_push_assoc($context, 'tsort', $_GET['tsort']);
}else{
  $context = array_push_assoc($context, 'tsort', '');
}

if (isset($_GET['search'])) {
  $context = array_push_assoc($context, 'search', $_GET['search']);
}else{
  $context = array_push_assoc($context, 'search', '');
}



$online_course_category_id = 0;
$live_online_course_category_id = 0; //live course filter
$past_offerings_category_id = 0;
$templates_course_category_id = 0;
$pending_course_category_id = 0;
$sql = "SELECT * from {course_categories} where name = 'Past Offerings'";
$categories = $DB->get_records_sql($sql);
foreach ($categories as $category) {
  
  $past_offerings_category_id = $category->id;
  
}
//Here, we are filtering the Live and On Demand parameter options
if (isset($_GET['format'])) {
  $format = $_GET['format'];
  switch($sort){
    case "ondemand":
      $sql = "SELECT * from {course_categories} where name = 'Live Courses'";
        $categories = $DB->get_records_sql($sql);
        foreach ($categories as $category){
          $live_online_course_category_id = $category->id;
      break;}
    case "livecourses":
      $sql = "SELECT * from {course_categories} where name = 'On Demand'";
      $categories = $DB->get_records_sql($sql);
      foreach ($categories as $category){
      $online_course_category_id = $category->id; 
      break; }
  }}
  else{
    $sql = "SELECT * from {course_categories} where name = 'On Demand'";
    $categories = $DB->get_records_sql($sql);
    foreach ($categories as $category){
    $online_course_category_id = $category->id; }
}

$sql = "SELECT * from {course_categories} where name = 'Live Courses'";
$categories = $DB->get_records_sql($sql);
foreach ($categories as $category){

  $live_online_course_category_id = $category->id;
  
}

$sql = "SELECT * from {course_categories} where name = 'Templates'";
$categories = $DB->get_records_sql($sql);
foreach ($categories as $category){

  $templates_course_category_id = $category->id;
  
}
$sql = "SELECT * from {course_categories} where name = 'Pending'";
$categories = $DB->get_records_sql($sql);
foreach ($categories as $category){

  $pending_course_category_id = $category->id;
  
}

echo $OUTPUT->render_from_template('local_coursecatalogue/searchbar', $context);

$fullname_desc = 0;
$startdate_desc = 0;
$ratings_desc = 0; 
$teacher_desc = 0;

if (isset($_GET['order']) && $_GET['order'] == 'asc') {
  if ($_GET['tsort'] == 'fullname'){
    $fullname_desc = 1;
  }
  else if ($_GET['tsort'] == 'startdate'){
    $startdate_desc = 1;
  }
  else if ($_GET['tsort'] == 'rating'){
    $ratings_desc = 1;
  }
  else if ($_GET['tsort'] == 'teacher') {
    $teacher_desc = 1;
  }
}
//this is to set up the URLs for each table header
$original_get = $_GET;

$query = $_GET;

$query['tsort'] = 'fullname';

$fullname_header = http_build_query($query);

$query['tsort'] = 'startdate';

$startdate_header = http_build_query($query);

$query['tsort'] = 'rating';

$rating_header = http_build_query($query);

$query['tsort'] = 'teacher';

$teacher_header = http_build_query($query);
//done setting up URLs for table headers, display table headers

echo '<div class="card">';
echo '<table class="table table-striped">';
echo '<thead>';
echo '<tr>';

if ($fullname_desc == 1){
  $query = $original_get;
  $query['order'] = 'desc';
  $fullname_header = http_build_query($query);
  echo '<th><a href="index.php?'.$fullname_header.'" >Course ▼</a></th>';
} 
else if ($fullname_desc == 0 && $_GET['order'] == 'desc' && $_GET['tsort'] == 'fullname') {
  $query = $original_get;
  $query['order'] = 'asc';
  $fullname_header = http_build_query($query);
  echo '<th><a href="index.php?'.$fullname_header.'" >Course ▲</a></th>';
}
else {
  $query = $original_get;
  $query['order'] = 'asc';
  $query['tsort'] = 'fullname';
  $fullname_header = http_build_query($query);
  echo '<th><a href="index.php?'.$fullname_header.'" >Course</a></th>';
}

if ($startdate_desc == 1) {
  $query = $original_get;
  $query['order'] = 'desc';
  $startdate_header = http_build_query($query);
  echo '<th><a href="index.php?'.$startdate_header.'" >Start Date ▼</a></th>';
}
else if ($startdate_desc == 0 && $_GET['order'] == 'desc' && $_GET['tsort'] == 'startdate') {
  $query = $original_get;
  $query['order'] = 'asc';
  $startdate_header = http_build_query($query);
  echo '<th><a href="index.php?'.$startdate_header.'" >Start Date ▲</a></th>';
}
else {
  $query = $original_get;
  $query['order'] = 'asc';
  $query['tsort'] = 'startdate';
  $startdate_header = http_build_query($query);
  echo '<th><a href="index.php?'.$startdate_header.'" >Start Date</a></th>';
}


if ($teacher_desc == 1) {
  $query = $original_get;
  $query['order'] = 'desc';
  $teacher_header = http_build_query($query);
  echo '<th><a href="index.php?'.$teacher_header.'">Main Instructor ▼</a></th>';
}
else if ($teacher_desc == 0 && $_GET['order'] == 'desc' && $_GET['tsort'] == 'teacher'){
  $query = $original_get;
  $query['order'] = 'asc';
  $teacher_header = http_build_query($query);
  echo '<th><a href="index.php?'.$teacher_header.'">Main Instructor ▲</a></th>';
}
else {
  $query = $original_get;
  $query['order'] = 'asc';
  $query['tsort'] = 'teacher';
  $teacher_header = http_build_query($query);
  echo '<th><a href="index.php?'.$teacher_header.'">Main Instructor</a></th>';
}

echo '<th>Tags</th>';
echo '<th>Department</th>';

if ($ratings_desc == 1) {
  $query = $original_get;
  $query['order'] = 'desc';
  $rating_header = http_build_query($query);
  echo '<th><a href="index.php?'.$rating_header.'">Ratings ▼</a></th>';
}
else if ($ratings_desc == 0 && $_GET['order'] == 'desc' && $_GET['tsort'] == 'rating'){
  $query = $original_get;
  $query['order'] = 'asc';
  $rating_header = http_build_query($query);
  echo '<th><a href="index.php?'.$rating_header.'">Ratings ▲</a></th>';
}
else {
  $query = $original_get;
  $query['order'] = 'asc';
  $query['tsort'] = 'rating';
  $rating_header = http_build_query($query);
  echo '<th><a href="index.php?'.$rating_header.'">Ratings</a></th>';
}

echo '</tr>';

$sql = "SELECT * FROM {course} WHERE ID is not null and fullname <> 'Local Environment' AND ID <> 1";

$on_demand_flag = 0;

if (isset($_GET['tsort'])){

  $sort = $_GET['tsort'];

  if ($sort == 'rating') {

    if (isset($_GET['order']) && $_GET['order'] == 'asc'){
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by rating desc";
    } else if (isset($_GET['order']) && $_GET['order'] == 'desc'){
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by rating asc";
    }

    else {
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by rating desc";
    }
  }

  else if ($sort == 'startdate') {
    $on_demand_flag = 0;
    if (isset($_GET['order']) && $_GET['order'] == 'asc'){
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by startdate asc";
    } else if (isset($_GET['order']) && $_GET['order'] == 'desc'){
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by startdate desc";
    }

    else {
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by startdate asc";
    }
  }

  else if ($sort == 'fullname'){
    if (isset($_GET['order']) && $_GET['order'] == 'asc'){
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by fullname asc";
    } else if (isset($_GET['order']) && $_GET['order'] == 'desc'){
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by fullname desc";
    }

    else {
      $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by fullname asc";
    }
  }

  else if ($sort == 'ondemand'){
    $on_demand_flag = 1;
    $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id";
  }

  else if ($sort == 'livecourses'){
    $on_demand_flag = 1;
    $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id";
  }

  else if ($sort = 'teacher'){
    if (isset($_GET['order']) && $_GET['order'] == 'asc'){
      $sql = "select * 
      from
      (select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id) courses
      
      left outer join
      
      (SELECT u.firstname, u.lastname, e.courseid FROM mdl_user u, mdl_role_assignments r_a, mdl_role r, mdl_enrol e, mdl_user_enrolments u_e WHERE u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND u.firstname <> 'DCR' group by e.courseid, u.firstname, u.lastname
      ) instructors
      
      on courses.id = instructors.courseid order by instructors.firstname asc";
    } else if (isset($_GET['order']) && $_GET['order'] == 'desc'){
      $sql = "select * 
      from
      (select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id) courses
      
      left outer join
      
      (SELECT u.firstname, u.lastname, e.courseid FROM mdl_user u, mdl_role_assignments r_a, mdl_role r, mdl_enrol e, mdl_user_enrolments u_e WHERE u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND u.firstname <> 'DCR' group by e.courseid, u.firstname, u.lastname
      ) instructors
      
      on courses.id = instructors.courseid order by instructors.firstname  desc";
    }

    else {
      $sql = "select * 
      from
      (select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id) courses
      
      left outer join
      
      (SELECT u.firstname, u.lastname, e.courseid FROM mdl_user u, mdl_role_assignments r_a, mdl_role r, mdl_enrol e, mdl_user_enrolments u_e WHERE u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND u.firstname <> 'DCR' group by e.courseid, u.firstname, u.lastname
      ) instructors
      
      on courses.id = instructors.courseid order by instructors.firstname asc";
    }
  }

  else {
    $on_demand_flag = 0;
    $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by startdate asc";
  }
}

else //if tsort is not set at all, also default to sort by startdate

{
  $on_demand_flag = 0;
  $sql = "select * from mdl_course c left outer join (select r.course as course, avg(r.rating) as rating from mdl_block_rate_course r group by r.course) r on c.id = r.course left outer join (select cfd.instanceid as courseid, cfd.value as department from mdl_course c, mdl_customfield_field cf, mdl_customfield_data cfd where cfd.instanceid = c.id) x on x.courseid = c.id order by startdate asc";
}

$courses = $DB->get_records_sql($sql); 

if ($on_demand_flag == 0){
  foreach($courses as $course){
    if ($course->category == $past_offerings_category_id) { //if course is in 'past offerings' category (34)
      continue;
    }
    if ($course->category == $templates_course_category_id) { //if course is in 'templates'
      continue;
    }
    if ($course->category == $pending_course_category_id) { //if course is in 'pending' category
      continue;
    }
    
    if (isset($_GET['department'])){
      if ($_GET['department'] != ''){
        if($_GET['department'] != $course->department){
          continue;
        }
      }
    }
    
    if (isset($_GET['month'])){ //this one is to filter out courses that don't match the month
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['month'] != date('M', $course->startdate)){
          if ($_GET['month'] != ''){
            continue;
          }
          
        }
    }
    if (isset($_GET['competency'])){ //this one is to filter out courses that don't contain a particular tag we passed
        //echo $_GET['tags'];
        $sql = "SELECT t.name 
          FROM {tag} t, {tag_instance} t_i
          WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;

        $tags = $DB->get_records_sql($sql);
        $match_flag = 0; //as we iterate through all the competency assigned to a particular course, we wanna find a match
        $online_course_category_id=0;
        foreach ($tags as $tag){
          if ($_GET['competency'] == $tag->name){ //if we find a match, we set the flag to 1
              $match_flag = 1;              ;
          }
        }
        if ($match_flag == 0){ //if we didn't find a match, skiip this iteration in the loop
          if ($_GET['competency'] != ''){
            continue;
          }
        }
    }

    //newly added tag named programs
    if (isset($_GET['programs'])){ //this one is to filter out courses that don't contain a particular tag we passed
      //echo $_GET['tags'];
      $sql = "SELECT t.name 
        FROM {tag} t, {tag_instance} t_i
        WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;

      $tags = $DB->get_records_sql($sql);
      $match_flag = 0; //as we iterate through all the tags assigned to a particular course, we wanna find a match
      foreach ($tags as $tag){
        if ($_GET['programs'] == $tag->name){ //if we find a match, we set the flag to 1
            $match_flag = 1;
        }
      }
      if ($match_flag == 0){ //if we didn't find a match, skiip this iteration in the loop
        if ($_GET['programs'] != ''){
          continue;
        }
      }
    }

      //newly added tag named roles
      if (isset($_GET['role'])){ //this one is to filter out courses that don't contain a particular tag we passed
        //echo $_GET['tags'];
        $sql = "SELECT t.name 
          FROM {tag} t, {tag_instance} t_i
          WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;
  
        $tags = $DB->get_records_sql($sql);
        $match_flag = 0; //as we iterate through all the tags assigned to a particular course, we wanna find a match
        foreach ($tags as $tag){
          if ($_GET['role'] == $tag->name){ //if we find a match, we set the flag to 1
              $match_flag = 1;
          }
        }
        if ($match_flag == 0){ //if we didn't find a match, skiip this iteration in the loop
          if ($_GET['role'] != ''){
            continue;
          }
        }
      }

    //newly added tag named level
    if (isset($_GET['level'])){ //this one is to filter out courses that don't contain a particular tag we passed
      //echo $_GET['tags'];
      $sql = "SELECT t.name 
        FROM {tag} t, {tag_instance} t_i
        WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;

      $tags = $DB->get_records_sql($sql);
      $match_flag = 0; //as we iterate through all the tags assigned to a particular course, we wanna find a match
      foreach ($tags as $tag){
        if ($_GET['level'] == $tag->name){ //if we find a match, we set the flag to 1
            $match_flag = 1;
        }
      }
      if ($match_flag == 0){ //if we didn't find a match, skiip this iteration in the loop
        if ($_GET['level'] != ''){
          continue;
        }
      }
    }
    
    if (isset($_GET['year'])){ //this one is to filter out anything that doesn't match the year
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['year'] != date('Y', $course->startdate)) {
          if ($_GET['year'] != ''){
            continue;
          }
        }
    }

    if (isset($_GET['format'])){ //this one is to filter out anything that doesn't match the On Demand or Live Courses
      if ($course->category == $live_online_course_category_id or $online_course_category_id){ //category 32 corresponds to online courses
      }
        else if ($_GET['format'] != ''){
          continue;
        }
      }
      

    if (isset($_GET['search'])){ // this one is to check for string patterns
      $course_name_flag = 0;
      $instructor_name_flag = 0; // these flags will be set to 1 if we fin any matches in our search
      $to_match = $_GET['search'];
      $regex_mode = "/.*".$to_match.".*/i"; // surround the string we passed in the search with wildcard characters
      if ( (preg_match($regex_mode, $course->fullname) == 1)){
        $course_name_flag = 1;
      }
      
      $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
      $teachers = $DB->get_records_sql($sql);  
      foreach($teachers as $teacher){
        if ( (preg_match($regex_mode, $teacher->firstname) == 1) || (preg_match($regex_mode, $teacher->lastname) == 1)){
          $instructor_name_flag = 1;
        }
        
      }
      if (( $course_name_flag == 0) && ($instructor_name_flag == 0)) {
        if ($_GET['search'] != '')
        continue;
      }
      
    }
    if (isset($_GET['stars'])){
      if ($_GET['stars'] == ''){

      }
      else{
        $block = block_instance('rate_course');
        
        $rating = $block->get_rating($course->id);

        if (($_GET['stars'] > ($rating) / 2) || $rating == 0){

          continue;
        }
      }
    }
    if ($course->id == 1){
      continue;
    }
    if ($course->category == $online_course_category_id){ //category
      continue;
    }
    
    echo '<tr>'; 

    echo '<td>'.'<a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';

    echo '<td>'.date('M-d-Y h:i A', $course->startdate). '</td>';

    $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND 
            u.firstname <> 'DCR'";
            
    echo '<td>';
    $teachers = $DB->get_records_sql($sql); 
    foreach($teachers as $teacher){
  
      echo $teacher->firstname;
      echo ' ';
      echo $teacher->lastname;   
      echo ' ';
      break;
    }
    echo '</td>';

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
    switch($course->department){ //so far we have 3 departments 1 -> DCR, 2 -> CFD, 3-> MGRI, check mdl_customfield_field options column for any new options
      case "1":
        echo "Division of Clinical Research";
        break;
      case "2":
        echo "Center for Faculty Development";
        break;
      case "3":
        echo "MGRI";
        break;
    }
    echo '</td>';

    echo '<td>';
    $block = block_instance('rate_course');
    if ($block->get_rating($course->id) != -1){
      $block->display_rating($course->id);
    }
    else{
      echo "No Reviews";
    }
    echo '</td>';


  echo '</tr>';
  }

  foreach($courses as $course){
    if ($course->category == $past_offerings_category_id) { //if course is in 'past offerings' category (34), then check if user is enrolled, if (s)he is, then display, else skip
      continue;
    }
    if ($course->category == $templates_course_category_id) { //if course is in 'templates'
      continue;
    }
    if ($course->category == $pending_course_category_id) { //if course is in 'pending' category
      continue;
    }

    if (isset($_GET['department'])){
      if ($_GET['department'] != ''){
        if($_GET['department'] != $course->department) {
          continue;
        }
      }
    }

    if (isset($_GET['month'])){ //this one is to filter out courses that don't match the month
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['month'] != date('M', $course->startdate)){
          if ($_GET['month'] != ''){
            continue;
          }
          
        }

    }
    if (isset($_GET['tags'])){ //this one is to filter out courses that don't contain a particular tag we passed
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
          if ($_GET['tags'] != ''){
            continue;
          }
        }
    }
    if (isset($_GET['year'])){ //this one is to filter out anything that doesn't match the year
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['year'] != date('Y', $course->startdate)) {
          if ($_GET['year'] != ''){
            continue;
          }
        }
    }
    if (isset($_GET['search'])){ // this one is to check for string patterns
      $course_name_flag = 0;
      $instructor_name_flag = 0; // these flags will be set to 1 if we fin any matches in our search
      $to_match = $_GET['search'];
      $regex_mode = "/.*".$to_match.".*/i"; // surround the string we passed in the search with wildcard characters
      if ( (preg_match($regex_mode, $course->fullname) == 1)){
        $course_name_flag = 1;
      }
      
      $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
      $teachers = $DB->get_records_sql($sql);  
      foreach($teachers as $teacher){
        if ( (preg_match($regex_mode, $teacher->firstname) == 1) || (preg_match($regex_mode, $teacher->lastname) == 1)){
          $instructor_name_flag = 1;
        }
        
      }
      if (( $course_name_flag == 0) && ($instructor_name_flag == 0)) {
        if ($_GET['search'] != '')
        continue;
      }
      
    }
    if (isset($_GET['stars'])){
      if ($_GET['stars'] == ''){

      }
      else{
        $block = block_instance('rate_course');
        
        $rating = $block->get_rating($course->id);

        if (($_GET['stars'] > ($rating) / 2) || $rating == 0){

          continue;
        }
      }
    }
    if ($course->id == 1){
      continue;
    }
    if ($course->category != $online_course_category_id){ 
      continue;
    }
    echo '<tr>'; 
    echo '<td>'.'<a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';
    if ($course->category == $online_course_category_id){ 
      echo '<td>On Demand</td>';
    }
    else{
      echo '<td>'.date('M-d-Y h:i A', $course->startdate). '</td>';
    }
    
    $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND 
            u.firstname <> 'DCR'";
            
    echo '<td>';
    $teachers = $DB->get_records_sql($sql); 
    foreach($teachers as $teacher){
  
      echo $teacher->firstname;
      echo ' ';
      echo $teacher->lastname;   
      echo ' ';
      break;
    }
    echo '</td>';

    
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
    switch($course->department){ //so far we have 3 departments 1 -> DCR, 2 -> CFD, 3-> MGRI, check mdl_customfield_field options column for any new options
      case "1":
        echo "Division of Clinical Research";
        break;
      case "2":
        echo "Center for Faculty Development";
        break;
        /* Not in use yet
      case "3":
        echo "MGRI";
        break;
        */
    }
    echo '</td>';

    echo '<td>';
    $block = block_instance('rate_course');
    if ($block->get_rating($course->id) != -1){
      $block->display_rating($course->id);
    }
    else{
      echo "No Reviews";
    }
    echo '</td>';

    echo '</tr>'; //end of row
  }
  
  echo '</table>';
}
else
{
  foreach($courses as $course){
    if (isset($_GET['department'])){
      if ($_GET['department'] != ''){
        if($_GET['department'] != $course->department) {
          continue;
        }
      }
    }
    
    if (isset($_GET['month'])){ //this one is to filter out courses that don't match the month
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['month'] != date('M', $course->startdate)){
          if ($_GET['month'] != ''){
            continue;
          }
          
        }
    }
    if (isset($_GET['competency'])){ //this one is to filter out courses that don't contain a particular tag we passed
        //echo $_GET['tags'];
        $sql = "SELECT t.name 
          FROM {tag} t, {tag_instance} t_i
          WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;

        $tags = $DB->get_records_sql($sql);
        $match_flag = 0; //as we iterate through all the tags assigned to a particular course, we wanna find a match
        foreach ($tags as $tag){
          if ($_GET['competency'] == $tag->name){ //if we find a match, we set the flag to 1
            if ($course->category == $online_course_category_id){//checking if course in context has an ondemand tag and also has a releavant competency
              $match_flag = 1;
            }
            else{
              $match_flag = 1;
              $online_course_category_id = 0;

            }
          }
        }
        if ($match_flag == 0){ //if we didn't find a match, skiip this iteration in the loop
          if ($_GET['competency'] != ''){
            continue;
          }
        }
    }
    if (isset($_GET['year'])){ //this one is to filter out anything that doesn't match the year
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['year'] != date('Y', $course->startdate)) {
          if ($_GET['year'] != ''){
            continue;
          }
        }
    }
    if (isset($_GET['search'])){ // this one is to check for string patterns
      $course_name_flag = 0;
      $instructor_name_flag = 0; // these flags will be set to 1 if we fin any matches in our search
      $to_match = $_GET['search'];
      $regex_mode = "/.*".$to_match.".*/i"; // surround the string we passed in the search with wildcard characters
      if ( (preg_match($regex_mode, $course->fullname) == 1)){
        $course_name_flag = 1;
      }
      
      $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
      $teachers = $DB->get_records_sql($sql);  
      foreach($teachers as $teacher){
        if ( (preg_match($regex_mode, $teacher->firstname) == 1) || (preg_match($regex_mode, $teacher->lastname) == 1)){
          $instructor_name_flag = 1;
        }
        
      }
      if (( $course_name_flag == 0) && ($instructor_name_flag == 0)) {
        if ($_GET['search'] != '')
        continue;
      }
      
    }
    if (isset($_GET['stars'])){
      if ($_GET['stars'] == ''){

      }
      else{
        $block = block_instance('rate_course');
        
        $rating = $block->get_rating($course->id);
   
        
        if (($_GET['stars'] > ($rating) / 2) || $rating == 0){
     
          
          continue;
        }
      }
    }
    if ($course->id == 1){
      continue;
    }
    if ($course->category != $online_course_category_id){ //category 32 corresponds to online courses
      continue;
    }

    echo '<tr>'; 
    echo '<td>'.'<a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';
    if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
      echo '<td>On Demand</td>';
    }
    else{
      echo '<td>'.date('M-d-Y h:i A', $course->startdate). '</td>';
    }

    $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND 
            u.firstname <> 'DCR'";
            
    echo '<td>';
    $teachers = $DB->get_records_sql($sql); 
    foreach($teachers as $teacher){
      echo $teacher->firstname;
      echo ' ';
      echo $teacher->lastname;   
      echo ' ';
      break;
    }
    echo '</td>';

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
    switch($course->department){ //so far we have 3 departments 1 -> DCR, 2 -> CFD, 3-> MGRI, check mdl_customfield_field options column for any new options
      case "1":
        echo "Division of Clinical Research";
        break;
      case "2":
        echo "Center for Faculty Development";
        break;
        /*
      case "3":
        echo "MGRI";
        break;
        */
    }
    echo '</td>';

    echo '<td>';
    $block = block_instance('rate_course');
    if ($block->get_rating($course->id) != -1){
      $block->display_rating($course->id);
    }
    else{
      echo "No Reviews";
    }
    echo '</td>';
    echo '</tr>'; //end of row
  }

  foreach($courses as $course){
    if ($course->category == $past_offerings_category_id) { //if course is in 'past offerings' category (34), then check if user is enrolled, if (s)he is, then display, else skip
      continue;
    }
    if ($course->category == $templates_course_category_id) { //if course is in 'templates'
      continue;
    }
    if ($course->category == $pending_course_category_id) { //if course is in 'pending' category
      continue;
    }

    if (isset($_GET['department'])){
      if ($_GET['department'] != ''){
        if($_GET['department'] != $course->department) {
          continue;
        }
      }
    }
    //echo $course->rating;
    if (isset($_GET['month'])){ //this one is to filter out courses that don't match the month
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['month'] != date('M', $course->startdate)){
          if ($_GET['month'] != ''){
            continue;
          }
          
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
          if ($_GET['tags'] != ''){
            continue;
          }
        }
    }
    if (isset($_GET['year'])){ //this one is to filter out anything that doesn't match the year
        if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
        }
        else if ($_GET['year'] != date('Y', $course->startdate)) {
          if ($_GET['year'] != ''){
            continue;
          }
        }
    }
    if (isset($_GET['search'])){ // this one is to check for string patterns
      $course_name_flag = 0;
      $instructor_name_flag = 0; // these flags will be set to 1 if we fin any matches in our search
      $to_match = $_GET['search'];
      $regex_mode = "/.*".$to_match.".*/i"; // surround the string we passed in the search with wildcard characters
      if ( (preg_match($regex_mode, $course->fullname) == 1)){
        $course_name_flag = 1;
      }
      
      $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
      $teachers = $DB->get_records_sql($sql);  
      foreach($teachers as $teacher){
        if ( (preg_match($regex_mode, $teacher->firstname) == 1) || (preg_match($regex_mode, $teacher->lastname) == 1)){
          $instructor_name_flag = 1;
        }
        
      }
      if (( $course_name_flag == 0) && ($instructor_name_flag == 0)) {
        if ($_GET['search'] != '')
        continue;
      }
      
    }
    if (isset($_GET['stars'])){
      if ($_GET['stars'] == ''){
        //empty if statement
      } else {
        $block = block_instance('rate_course');
        
        $rating = $block->get_rating($course->id);

        if (($_GET['stars'] > ($rating) / 2) || $rating == 0){
          continue;
        }
      }
    }
    if ($course->id == 1){
      continue;
    }
    if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
      continue;
    }
    
    echo '<tr>'; 
    echo '<td>'.'<a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';

    echo '<td>'.date('M-d-Y h:i A', $course->startdate). '</td>';

    $sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND 
            u.firstname <> 'DCR'";
            
    echo '<td>';
    $teachers = $DB->get_records_sql($sql); 
    foreach($teachers as $teacher){
  
      echo $teacher->firstname;
      echo ' ';
      echo $teacher->lastname;   
      echo ' ';
      break;
    }
    echo '</td>';

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
    switch($course->department){ //so far we have 3 departments 1 -> DCR, 2 -> CFD, 3-> MGRI, check mdl_customfield_field options column for any new options
      case "1":
        echo 'Division of Clinical Research';
        break;
      case "2":
        echo 'Center for Faculty Development';
        break;
      /*case "3":
        echo "MGRI";
        break;
        */
    }
    echo '</td>';

    echo '<td>';
    $block = block_instance('rate_course');
    if ($block->get_rating($course->id) != -1){
      $block->display_rating($course->id);
    }
    else{
      echo "No Reviews";
    }
    
    echo '</td>';


  echo '</tr>';
  }

  echo '</table>';
}

echo $OUTPUT->footer();
