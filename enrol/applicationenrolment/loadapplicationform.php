<<<<<<< HEAD
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han (hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/enrol/applicationenrolment/lib.php');
require_once($CFG->dirroot.'/enrol/applicationenrolment/classes/forms/studentapplication.php');

use enrol_applicationenrolment\studentapplication;

global $CFG, $DB, $USER;

require_login();

$courseid = required_param('courseid', PARAM_INT);
$applicationid = optional_param('applicationid', '0', PARAM_INT);

$context = context_course::instance($courseid);
$is_enrolled = is_enrolled($context, $USER->id, '', true);

$course_url = new moodle_url('/course/view.php', ['id' => $courseid]);

if ($is_enrolled) {
    redirect($course_url->out(false));
    exit;
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_url(new moodle_url('/enrol/applicationenrolment/loadapplicationform.php'), []);

$pagetitle = 'Application';

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('enroll', $course_url->out(false));
$PAGE->navbar->add($pagetitle);

if($applicationid != 0) {

    $application_record = $DB->get_record('student_apply_course', ['id' => $applicationid, 'studentid' => $USER->id]);
    if (empty($application_record)) {
        // Naughty users
        $course_url = new moodle_url('/enrol/index.php', ['id' => $courseid, 'warn' => 'your_action_was_recorded_dont_do_that']);
        redirect($course_url->out(false));
        exit;
    }
    $cur_question_answer = json_decode($application_record->question_answer);
}

// Check if the application limit number or enrol-enddate has been reached
$sql = "SELECT COUNT(*) AS application_number, e.customint1 AS application_limit, e.enrolenddate
        FROM {student_apply_course} sac
        JOIN {enrol} e ON sac.courseid = e.courseid
        WHERE application_state = 'Pending'
            AND e.enrol = 'applicationenrolment'
            AND sac.courseid = $courseid";

$result = $DB->get_record_sql($sql, []);

if (!empty($result->application_number) && !empty($result->application_limit)) {
    if (intval($result->application_number) >= intval($result->application_limit)) {
        $application_limit_reached = true;
    }
}
// if application limit number reached, not to accept any more new applications
if (!empty($application_limit_reached) && $applicationid == 0) {
    redirect($course_url->out(false));
    exit;
}
// if the enrollment duedate reached or passed, no more new applications, also not to allow saved applications to submit
if ( $applicationid == 0 && !empty($result->enrolenddate) && time() > $result->enrolenddate) {
    // No more new applications
    redirect($course_url->out(false));
    exit;
}
else if ( $applicationid != 0 && !empty($result->enrolenddate) && time() > $result->enrolenddate
            && $application_record->application_button_state != 'Submitted') {
    // Not to allow saved applications to submit
    redirect($course_url->out(false));
    exit;
}

// Display the form
$form = new studentapplication(new moodle_url('/enrol/applicationenrolment/loadapplicationform.php'),
                                                ['courseid' => $courseid, 'applicationid' => $applicationid]);
$formdata = $form->get_data();

$button_clicked = optional_param('button_clicked', '', PARAM_RAW);

if ($button_clicked == 'Save' || $button_clicked == 'Submit') {

    if (!empty($application_record) && $application_record->application_state == 'Pending') {
        $course_url = new moodle_url('/enrol/index.php', ['id' => $courseid, 'warn' => 'your_action_was_recorded_dont_do_that']);
        redirect($course_url->out(false));
        exit;
    }

    @set_time_limit(0);
    raise_memory_limit(MEMORY_EXTRA);

    $questionids = $DB->get_field('enrol', 'customchar1', ['courseid' => $courseid, 'enrol' => 'applicationenrolment']);

    $sql = "SELECT * FROM {application_question} WHERE id IN ($questionids)";
    $questions = $DB->get_records_sql($sql, []);

    $arr_questionids = explode(',', $questionids);

    $answers = [];

    foreach($arr_questionids as $questionidonly) {
        $question_content = json_decode($questions[$questionidonly]->question_content);
        $answer = optional_param('questionid_' . $questionidonly, '', PARAM_RAW);

        $filename = '';
        $filecontrol = 'filequestionid_'. $questionidonly;

        if (!empty($formdata->$filecontrol)) {
            $filename = $form->get_new_filename('filequestionid_'. $questionidonly);
            if ($filename) {
                $filename = $CFG->tempdir . '/applicationenrolment/' . $courseid . '/' . $USER->id . '/' . $filename;
                make_temp_directory('applicationenrolment/' . $courseid . '/' . $USER->id);
                $success = $form->save_file('filequestionid_'. $questionidonly, $filename, true);
            }
            else {
                // File control was added but no file upload, check if there is already a uploaded file
                $filename = $cur_question_answer->$questionidonly->filename;
            }
            $answers[$questionidonly] = ['question' => $question_content->question_text->text,
                                            'answer' => $answer, 'filename' => $filename];
        }
        else {
            // This quesiton doesn't have a file control
            $answers[$questionidonly] = ['question' => $question_content->question_text->text,
                                            'answer' => $answer, 'filename' => ''];
        }
    }

    $studen_apply_course            = new stdclass;
    $studen_apply_course->courseid  = $courseid;
    $studen_apply_course->studentid = $USER->id;
    if ($button_clicked == 'Save') {
        $studen_apply_course->application_state = 'Incomplete';
        $studen_apply_course->application_button_state = 'Started';
    }
    else if ($button_clicked == 'Submit') {
        if (!empty($formdata)) {
            $studen_apply_course->application_state = 'Pending';
            $studen_apply_course->application_button_state = 'Submitted';
        }
        else {
            $studen_apply_course->application_state = 'Incomplete';
            $studen_apply_course->application_button_state = 'Started';
        }
    }
    $studen_apply_course->question_answer   = json_encode($answers);

    if($applicationid == 0) {
        // Create a new one
        $studen_apply_course->timecreated       = time();
        $studen_apply_course->timemodified      = time();
        $DB->insert_record('student_apply_course', $studen_apply_course);
    }
    else {
        // Update an existing one
        $studen_apply_course->id = $applicationid;
        $studen_apply_course->timemodified = time();
        $DB->update_record('student_apply_course', $studen_apply_course);
    }

    if ($button_clicked == 'Save') {
        redirect($course_url->out(false));
        exit;
    }
    else if ($button_clicked == 'Submit') {
        if (!empty($formdata)) {

            $emailtemplate = $DB->get_field('enrol', 'customtext4', ['enrol' => 'applicationenrolment', 'courseid' => $courseid]);
            $emailtemplate = empty(trim($emailtemplate)) ? get_string('emailtemplate_submitconfirm', 'enrol_applicationenrolment') : $emailtemplate;
            $subject = 'Application Submitted - ';
            send_email_and_log($USER->id, $courseid, $subject, $emailtemplate);
            redirect($course_url->out(false));
            exit;
        }
    }
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
=======
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han (hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/enrol/applicationenrolment/lib.php');
require_once($CFG->dirroot.'/enrol/applicationenrolment/classes/forms/studentapplication.php');

use enrol_applicationenrolment\studentapplication;

global $CFG, $DB, $USER;

require_login();

$courseid = required_param('courseid', PARAM_INT);
$applicationid = optional_param('applicationid', '0', PARAM_INT);

$context = context_course::instance($courseid);
$is_enrolled = is_enrolled($context, $USER->id, '', true);

$course_url = new moodle_url('/course/view.php', ['id' => $courseid]);

if ($is_enrolled) {
    redirect($course_url->out(false));
    exit;
}

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_url(new moodle_url('/enrol/applicationenrolment/loadapplicationform.php'), []);

$pagetitle = 'Application';

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('enrol', $course_url->out(false));
$PAGE->navbar->add($pagetitle);

if($applicationid != 0) {

    $application_record = $DB->get_record('student_apply_course', ['id' => $applicationid, 'studentid' => $USER->id]);
    if (empty($application_record)) {
        // Naughty users
        $course_url = new moodle_url('/enrol/index.php', ['id' => $courseid, 'warn' => 'your_action_was_recorded_dont_do_that']);
        redirect($course_url->out(false));
        exit;
    }
    $cur_question_answer = json_decode($application_record->question_answer);
}

// Check if the application limit number or enrol-enddate has been reached
$sql = "SELECT COUNT(*) AS application_number, e.customint1 AS application_limit, e.enrolenddate
        FROM {student_apply_course} sac
        JOIN {enrol} e ON sac.courseid = e.courseid
        WHERE application_state = 'Pending'
            AND e.enrol = 'applicationenrolment'
            AND sac.courseid = $courseid";

$result = $DB->get_record_sql($sql, []);

if (!empty($result->application_number) && !empty($result->application_limit)) {
    if (intval($result->application_number) >= intval($result->application_limit)) {
        $application_limit_reached = true;
    }
}
// if application limit number reached, not to accept any more new applications
if (!empty($application_limit_reached) && $applicationid == 0) {
    redirect($course_url->out(false));
    exit;
}
// if the enrollment duedate reached or passed, no more new applications, also not to allow saved applications to submit
if ( $applicationid == 0 && !empty($result->enrolenddate) && time() > $result->enrolenddate) {
    // No more new applications
    redirect($course_url->out(false));
    exit;
}
else if ( $applicationid != 0 && !empty($result->enrolenddate) && time() > $result->enrolenddate
            && $application_record->application_button_state != 'Submitted') {
    // Not to allow saved applications to submit
    redirect($course_url->out(false));
    exit;
}

// Display the form
$form = new studentapplication(new moodle_url('/enrol/applicationenrolment/loadapplicationform.php'),
                                                ['courseid' => $courseid, 'applicationid' => $applicationid]);
$formdata = $form->get_data();

$button_clicked = optional_param('button_clicked', '', PARAM_RAW);

if ($button_clicked == 'Save' || $button_clicked == 'Submit') {

    if (!empty($application_record) && $application_record->application_state == 'Pending') {
        $course_url = new moodle_url('/enrol/index.php', ['id' => $courseid, 'warn' => 'your_action_was_recorded_dont_do_that']);
        redirect($course_url->out(false));
        exit;
    }

    @set_time_limit(0);
    raise_memory_limit(MEMORY_EXTRA);

    $questionids = $DB->get_field('enrol', 'customchar1', ['courseid' => $courseid, 'enrol' => 'applicationenrolment']);

    $sql = "SELECT * FROM {application_question} WHERE id IN ($questionids)";
    $questions = $DB->get_records_sql($sql, []);

    $arr_questionids = explode(',', $questionids);

    $answers = [];

    foreach($arr_questionids as $questionidonly) {
        $question_content = json_decode($questions[$questionidonly]->question_content);
        $answer = optional_param('questionid_' . $questionidonly, '', PARAM_RAW);

        $filename = '';
        $filecontrol = 'filequestionid_'. $questionidonly;

        if (!empty($formdata->$filecontrol)) {
            $filename = $form->get_new_filename('filequestionid_'. $questionidonly);
            if ($filename) {
                $filename = $CFG->tempdir . '/applicationenrolment/' . $courseid . '/' . $USER->id . '/' . $filename;
                make_temp_directory('applicationenrolment/' . $courseid . '/' . $USER->id);
                $success = $form->save_file('filequestionid_'. $questionidonly, $filename, true);
            }
            else {
                // File control was added but no file upload, check if there is already a uploaded file
                $filename = $cur_question_answer->$questionidonly->filename;
            }
            $answers[$questionidonly] = ['question' => $question_content->question_text->text,
                                            'answer' => $answer, 'filename' => $filename];
        }
        else {
            // This quesiton doesn't have a file control
            $answers[$questionidonly] = ['question' => $question_content->question_text->text,
                                            'answer' => $answer, 'filename' => ''];
        }
    }

    $studen_apply_course            = new stdclass;
    $studen_apply_course->courseid  = $courseid;
    $studen_apply_course->studentid = $USER->id;
    if ($button_clicked == 'Save') {
        $studen_apply_course->application_state = 'Incomplete';
        $studen_apply_course->application_button_state = 'Started';
    }
    else if ($button_clicked == 'Submit') {
        if (!empty($formdata)) {
            $studen_apply_course->application_state = 'Pending';
            $studen_apply_course->application_button_state = 'Submitted';
        }
        else {
            $studen_apply_course->application_state = 'Incomplete';
            $studen_apply_course->application_button_state = 'Started';
        }
    }
    $studen_apply_course->question_answer   = json_encode($answers);

    if($applicationid == 0) {
        // Create a new one
        $studen_apply_course->timecreated       = time();
        $studen_apply_course->timemodified      = time();
        $DB->insert_record('student_apply_course', $studen_apply_course);
    }
    else {
        // Update an existing one
        $studen_apply_course->id = $applicationid;
        $studen_apply_course->timemodified = time();
        $DB->update_record('student_apply_course', $studen_apply_course);
    }

    if ($button_clicked == 'Save') {
        redirect($course_url->out(false));
        exit;
    }
    else if ($button_clicked == 'Submit') {
        if (!empty($formdata)) {

            $emailtemplate = $DB->get_field('enrol', 'customtext4', ['enrol' => 'applicationenrolment', 'courseid' => $courseid]);
            $emailtemplate = empty(trim($emailtemplate)) ? get_string('emailtemplate_submitconfirm', 'enrol_applicationenrolment') : $emailtemplate;
            $subject = 'Application Submitted - ';
            send_email_and_log($USER->id, $courseid, $subject, $emailtemplate);
            redirect($course_url->out(false));
            exit;
        }
    }
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
>>>>>>> 50f475fc90ed57a62c9dd5bf62b657f8b9598e76
