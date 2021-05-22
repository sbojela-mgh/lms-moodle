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
 * This page shows all course enrolment options for current user.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);

if (!isloggedin()) {
    $referer = get_local_referer();
    if (empty($referer)) {
        // A user that is not logged in has arrived directly on this page,
        // they should be redirected to the course page they are trying to enrol on after logging in.
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=$id";
        //"$CFG->wwwroot/enrol/confirmation.php?id=$course->id"
    }
    // do not use require_login here because we are usually coming from it,
    // it would also mess up the SESSION->wantsurl
    redirect(get_login_url());
}
$online_course_category_id = 0;
$sql = "SELECT * from {course_categories} where name = 'On Demand'";
$categories = $DB->get_records_sql($sql);
foreach ($categories as $category){

  $online_course_category_id = $category->id;
  
}


$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Everybody is enrolled on the frontpage
if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
    print_error('coursehidden');
}

$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/enrol/index.php', array('id'=>$course->id));

// do not allow enrols when in login-as session
if (\core\session\manager::is_loggedinas() and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
    print_error('loginasnoenrol', '', $CFG->wwwroot.'/course/view.php?id='.$USER->loginascontext->instanceid);
}

// Check if user has access to the category where the course is located.
if (!core_course_category::can_view_course_info($course) && !is_enrolled($context, $USER, '', true)) {
    print_error('coursehidden', '', $CFG->wwwroot . '/');
}

// get all enrol forms available in this course
$enrols = enrol_get_plugins(true);
$enrolinstances = enrol_get_instances($course->id, true);
$forms = array();
foreach($enrolinstances as $instance) {
    if (!isset($enrols[$instance->enrol])) {
        continue;
    }
    $form = $enrols[$instance->enrol]->enrol_page_hook($instance);
    if ($form) {
        $forms[$instance->id] = $form;
    }
}

// Check if user already enrolled
if (is_enrolled($context, $USER, '', true)) {
    if (!empty($SESSION->wantsurl)) {
        $destination = "$CFG->wwwroot/enrol/confirmation.php?id=$course->id";
        unset($SESSION->wantsurl);
    } else {
        //$destination = "$CFG->wwwroot/enrol/confirmation.php?id=$course->id";
        //Original below
        $destination = "$CFG->wwwroot/course/view.php?id=$course->id";
    }
    redirect($destination);   // Bye!
}

$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('enrolmentoptions','enrol'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('enrolmentoptions','enrol'));
echo '<span style = "color: #1177d1; font-weight: bold; font-size: 24px;";>'.$course->fullname.'</span>';
echo '<span style = "float: right;">';
$block = block_instance('rate_course');
$block->display_rating($course->id);
echo '</span>';
echo '<br/>';
if ($course->category == $online_course_category_id){
    echo '<tr>'.'<span style = "font-weight: bold;
    font-size: 18px;">'.'Date/Time:'.'</span>'. ' On Demand'.'</tr>';
} else {
    echo '<tr>'.'<span style = "font-weight: bold;
    font-size: 18px;">'.'Date/Time:'.'</span>'. date(' M-d-Y hA', $course->startdate).'</tr>';
}
/*Retrieving the context instanceid to bring context into context for the 
the other retrievals to come*/
/*$context = $DB->get_record_select('context', 'instanceid =?', array($course->id));
//retrieving role assignments to bring into context all associated role_assignments
$role_assignments = $DB->get_record_select('role_assignments', 'contextid =?', array($context->id));

$role = $DB->get_record_select('role', 'id =?', array($role_assignments->roleid));

$user = $DB->get_record_select('user', 'id =?', array($role_assignments->userid ));
*/
echo '</br>';
$sql = "SELECT u.firstname, u.lastname
            FROM {user} u, {role_assignments} r_a, {role} r, {enrol} e, {user_enrolments} u_e
            WHERE e.courseid = ". $course->id ." AND u.id = r_a.userid AND (r_a.roleid = 4 OR r_a.roleid = 3) AND u_e.userid = u.id AND e.id = u_e.enrolid AND
            u.id <> 3";
            
            $teachers = $DB->get_records_sql($sql); 
            foreach($teachers as $teacher){
          
if ($teachers != null){
    echo '<span style= "font-weight: bold; font-size: 18px;">' .'Instructor:'. '</span>'.' '. $teacher->firstname. ' '.$teacher->lastname;
}
            }
/*
$sql = "SELECT firstname FROM {user} as u
JOIN {role_assignments} as ra ON ra.userid = u.id
JOIN {role} as r ON ra.roleid = r.id
JOIN {context} as con ON ra.contextid = con.id
JOIN {course} as c ON c.id = con.instanceid AND con.contextlevel = 50
WHERE r.shortname = 'editingteacher'";

$teachers = $DB->get_records_sql($sql);
 foreach($teachers as $teacher){
 }
echo '<div style = "font-weight: bold;
font-size: 18px; margin-top: 10px;">'.'Instructor(s)'.':'.' '.'</div>'.$teacher->firstname.' '.$teacher->lastname;
*/

echo '<div style = "font-weight: bold;
font-size: 18px; ">'.'Description'.'</div>';

echo '<div style ="margin:0px !important;" >'.$course->summary.'<div>';

$sql = "SELECT t.name 
        FROM {tag} t, {tag_instance} t_i
        WHERE t.id = t_i.tagid AND t_i.itemid = ". $course->id;
$tags = $DB->get_records_sql($sql);

echo '<span style= "font-weight: bold; font-size: 18px;">'.'Tags:'.' '.'</span>';
echo '<br/>';
foreach($tags as $tag){
    echo '<span style="font-size: 22px;margin-left: 3px;">'.$tag->name.' '.'</span>';
    
}

$courserenderer = $PAGE->get_renderer('core', 'course');
//adding a header named course description
//echo $courserenderer->course_info_box($course);

//TODO: find if future enrolments present and display some info

foreach ($forms as $form) {
    echo $form;
}

if (!$forms) {
    if (isguestuser()) {
        notice(get_string('noguestaccess', 'enrol'), get_login_url());
    } else if ($returnurl) {
        notice(get_string('notenrollable', 'enrol'), $returnurl);
    } else {
        $url = get_local_referer(false);
        if (empty($url)) {
            $url = new moodle_url('/index.php');
        }
        notice(get_string('notenrollable', 'enrol'), $url);
    }
}



echo $OUTPUT->footer();
