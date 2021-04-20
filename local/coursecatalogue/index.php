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

if (isset($_GET['tags'])) {
  $context = array_push_assoc($context, 'tags', $_GET['tags']);
}else{
  $context = array_push_assoc($context, 'tags', '');
}

if (isset($_GET['stars'])) {
  $context = array_push_assoc($context, 'stars', $_GET['stars']);
}else{
  $context = array_push_assoc($context, 'stars', '');
}

if (isset($_GET['tsort'])) {
  $sort = $_GET['tsort'];
  switch($sort){
    case "fullname":
      $context = array_push_assoc($context, 'tsortname', "Course Name");
      break;
    case "startdate":
      $context = array_push_assoc($context, 'tsortname', "Start Date");
      break;
    case "rating":
      $context = array_push_assoc($context, 'tsortname', "Ratings");
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

$sql = "SELECT * from {course_categories} where name = 'Past Offerings'";
$categories = $DB->get_records_sql($sql);
$online_course_category_id = 0;
$past_offerings_category_id = 0;
foreach ($categories as $category) {
  
  $past_offerings_category_id = $category->id;
  
}
$sql = "SELECT * from {course_categories} where name = 'On Demand'";
$categories = $DB->get_records_sql($sql);
foreach ($categories as $category){

  $online_course_category_id = $category->id;
  
}

//$online_course_category_id = 32; //Hardcoded solution for now

//echo("The On Demand ID used for reference is: " . $online_course_category_id);


echo $OUTPUT->render_from_template('local_coursecatalogue/searchbar', $context);

echo '<div class="card">';
echo '<table class="table table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th>Course</th>';
echo '<th>Start Date</th>';
echo '<th>Main Instructor</th>';
echo '<th>Tags</th>';
echo '<th>Ratings</th>';
echo '</tr>';

$sql = "SELECT * FROM {course} WHERE ID is not null and fullname <> 'Local Environment' AND ID <> 1";

$on_demand_flag = 0;

if (isset($_GET['tsort'])){
  //echo("IM ALIVE!");
  $sort = $_GET['tsort'];
  //echo($sort);
  if ($sort == ''){
    $sql = "select * from {course} c left outer join (select r.course as course, avg(r.rating) as rating from {block_rate_course} r group by r.course) r on c.id = r.course";
  }
  else if ($sort == 'rating') {
    $sql = "select * from mdl_course c left outer join (SELECT x.avg, x.name, c.id as course FROM (SELECT AVG(rating) AS avg, c.fullname as name FROM mdl_block_rate_course as r JOIN mdl_course as c ON c.id = r.course GROUP BY c.fullname) as x, mdl_course c WHERE x.name = c.fullname) r on c.id = r.course order by r.avg desc";

  }
  else if ($sort == 'ondemand'){
    $on_demand_flag = 1;
    $sql = "select * from {course} c left outer join (select r.course as course, avg(r.rating) as rating from {block_rate_course} r group by r.course) r on c.id = r.course";
  } 
  else if ($sort == 'startdate') {
    $on_demand_flag = 1;
    $sql = "select * from {course} c left outer join (select r.course as course, avg(r.rating) as rating from {block_rate_course} r group by r.course) r on c.id = r.course order by ". $sort . " asc";
  } else {
  $sql = "select * from {course} c left outer join (select r.course as course, avg(r.rating) as rating from {block_rate_course} r group by r.course) r on c.id = r.course order by ". $sort . " asc";
  }
}
//else{
//  echo("I shoudln't be doing this");
//  $sql = "select * from {course} c left outer join (select r.course as course, avg(r.rating) as rating from {block_rate_course} r group by r.course) r on c.id = r.course";
//}




//echo($sql);



$courses = $DB->get_records_sql($sql); 
  


if ($on_demand_flag == 0){
  foreach($courses as $course){
    if ($course->category == $past_offerings_category_id) { //if course is in 'past offerings' category (34), then check if user is enrolled, if (s)he is, then display, else skip
      continue;
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
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
            
    echo '<td>';
    $teachers = $DB->get_records_sql($sql);  
    foreach($teachers as $teacher){
  
      echo $teacher->firstname;
      echo ' ';
      echo $teacher->lastname;   
      echo ' ';
      echo '</td>';
      break;
      
    }

    
      
 
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
    //echo '<br>';
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
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid";
            
    echo '<td>';
    $teachers = $DB->get_records_sql($sql);  
    foreach($teachers as $teacher){
  
      echo $teacher->firstname;
      echo ' ';
      echo $teacher->lastname;   
      echo ' ';
      echo '</td>';
      break;
      
    }

    
      
  
    

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
else
{
  foreach($courses as $course){
    
    
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
    //echo '<br>';
    echo '<tr>'; 
    echo '<td>'.'<a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';
    if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
      echo '<td>On Demand</td>';
    }
    else{
      echo '<td>'.date('M-d-Y h:i A', $course->startdate). '</td>';
    }

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
      echo '</td>';
      break;
      
    }

    
      
  
    
  
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
    if ($course->category == $online_course_category_id){ //category 32 corresponds to online courses
      continue;
    }
    
    
    echo '<tr>'; 
    echo '<td>'.'<a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>'.$course->fullname.'</a>'.'</td>';

    

    
    echo '<td>'.date('M-d-Y h:i A', $course->startdate). '</td>';

    
    
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
      echo '</td>';
      break;
      
    }

  
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
