<?php
require('../config.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if (isset($_SESSION['username'])) {
    $url = "page1.php";
    header('Location: ' . $url);
    exit();
} else if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $_SESSION['username'] = $username;
    $url = "page1.php";
    header('Location: ' . $url);
    exit();
}

$event = array(
	'id' => $course->id,
	'title' => $course->fullname,
	'description' => $course->summary,
	'datestart' => $course->startdate,
	'dateend' => $course->enddate
);

// Convert times to iCalendar format. They require a block for yyyymmdd and then another block
// for the time, which is in hhiiss. Both of those blocks are separated by a "T". The Z is
// declared at the end for UTC time, but shouldn't be included in the date conversion.

// iCal date format: yyyymmddThhiissZ
// PHP equiv format: Ymd\This

function dateToCal($time) {
	return date('Ymd\This', $time) . 'P';
}

// Build the ics file
$ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:' . dateToCal($event['dateend']) . '
UID:' . md5($event['title']) . '
DTSTAMP:' . time() . '
DESCRIPTION:' . addslashes($event['description']) . '
URL;VALUE=URI: http://mydomain.com/events/' . $event['id'] . '
SUMMARY:' . addslashes($event['title']) . '
DTSTART:' . dateToCal($event['datestart']) . '
END:VEVENT
END:VCALENDAR';

//set correct content-type-header
if($event['id']){
	header('Content-type: text/calendar; charset=utf-8');
	header('Content-Disposition: attachment; filename=mohawk-event.ics');
	echo $ical;
} else {
	// If $id isn't set, then kick the user back to home. Do not pass go,
        // and do not collect $200. Currently it's _very_ slow.
	header('Location: /lms-moodle/index.php');
}
?>