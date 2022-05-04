<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han (hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $DB, $USER;

require_login();

if (!confirm_sesskey()) {
    echo 'Naughty!';
}

// $context = context_course::instance($courseid);
// if (!has_capability('enrol/applicationenrolment:config', $context)) {
//     echo "<p>You don't have the permission to access this page</p>";
//     exit();
// }

$courseid = required_param('courseid', PARAM_INT);
$applicationid = required_param('applicationid', PARAM_INT);
$questionid = required_param('questionid', PARAM_INT);

$question_answers = $DB->get_field('student_apply_course', 'question_answer', ['id' => $applicationid]);
$question_answers = json_decode($question_answers);
$fullfilepath = $question_answers->$questionid->filename;

if (!is_file($fullfilepath)) {
    echo "This file no longer exists: $fullfilepath";
    exit;
}

$basename = basename($fullfilepath);

$zip = new ZipArchive;
if ($zip->open($fullfilepath, ZipArchive::CREATE) === TRUE) {

    $zip->addFile($fullfilepath, $basename);

    $zip->close();

    // header("Content-type: application/zip");
    // header("Content-Disposition: attachment; filename=" . $basename);
    // header("Pragma: no-cache");
    // header("Expires: 0");
    // readfile("{$fullfilepath}");

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename=" . $basename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: '.filesize($fullfilepath));
    ob_clean();
    flush();
    readfile("{$fullfilepath}");

    exit;
}