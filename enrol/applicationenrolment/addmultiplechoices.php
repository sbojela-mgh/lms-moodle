<<<<<<< HEAD
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han (hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/enrol/applicationenrolment/classes/forms/multiple_choices.php');

use enrol_applicationenrolment\multiple_choices;

global $CFG, $DB, $PAGE, $USER;

$courseid = optional_param('courseid', '', PARAM_INT);
$instanceid = optional_param('instanceid', 0, PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);
$answers = optional_param_array('answer', [], PARAM_RAW);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

if (!has_capability('enrol/applicationenrolment:manage', $PAGE->context)) {
    echo get_string('nopermission', 'enrol_applicationenrolment');
    exit();
}

if ($instanceid == 0) {
    $instance_url = new moodle_url('/enrol/editinstance.php', ['type' => 'applicationenrolment', 'courseid' => $courseid]);
}
else {
    $instance_url = new moodle_url('/enrol/editinstance.php', ['type' => 'applicationenrolment', 'courseid' => $courseid, 'id'=>$instanceid]);
}

$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/enrol/applicationenrolment/addmultiplechoices.php'), []);

$pagetitle = get_string('pageheading_multiplechoices', 'enrol_applicationenrolment');
if ($questionid != 0) {
    $pagetitle = 'Update question';
}

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'enrol_applicationenrolment'), $instance_url->out(false));
$PAGE->navbar->add($pagetitle);

// Display the form
$form = new multiple_choices(new moodle_url('/enrol/applicationenrolment/addmultiplechoices.php'), []);

if ($form->is_cancelled()) {
    redirect($instance_url->out(false));
}

else if ($formadata = $form->get_data()) {
    if ($questionid == 0) {
        // Add new
        $obj_question = new stdclass;
    }
    else {
        $obj_question = $DB->get_record('application_question', ['id' => $questionid]);
        if (empty($obj_question)) {
            $obj_question = new stdclass;
            $questionid = 0;
        }
    }
    $answers                        = array_filter($answers, function($value) { return !is_null($value) && trim($value) !== ''; });
    $obj_question->question_content = json_encode([
                                        'question_type' => 'multiple_choices',
                                        'question_name' => $formadata->question_name,
                                        'question_text' => $formadata->question_text,
                                        'choices'       => $answers]);

    $obj_question->categoryid       = $formadata->category;
    $obj_question->required         = $formadata->required == 1 ? 1 : 0;

    if ($questionid == 0) {
        $new_createdid = $DB->insert_record('application_question', $obj_question);
        redirect($instance_url->out(false) . '&new_questionids[]=' . $new_createdid);
    }
    else {
        $DB->update_record('application_question', $obj_question);
        redirect($instance_url->out(false));
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
require_once($CFG->dirroot.'/enrol/applicationenrolment/classes/forms/multiple_choices.php');

use enrol_applicationenrolment\multiple_choices;

global $CFG, $DB, $PAGE, $USER;

$courseid = optional_param('courseid', '', PARAM_INT);
$instanceid = optional_param('instanceid', 0, PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);
$answers = optional_param_array('answer', [], PARAM_RAW);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

if (!has_capability('enrol/applicationenrolment:manage', $PAGE->context)) {
    echo get_string('nopermission', 'enrol_applicationenrolment');
    exit();
}

if ($instanceid == 0) {
    $instance_url = new moodle_url('/enrol/editinstance.php', ['type' => 'applicationenrolment', 'courseid' => $courseid]);
}
else {
    $instance_url = new moodle_url('/enrol/editinstance.php', ['type' => 'applicationenrolment', 'courseid' => $courseid, 'id'=>$instanceid]);
}

$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/enrol/applicationenrolment/addmultiplechoices.php'), []);

$pagetitle = get_string('pageheading_multiplechoices', 'enrol_applicationenrolment');
if ($questionid != 0) {
    $pagetitle = 'Update question';
}

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'enrol_applicationenrolment'), $instance_url->out(false));
$PAGE->navbar->add($pagetitle);

// Display the form
$form = new multiple_choices(new moodle_url('/enrol/applicationenrolment/addmultiplechoices.php'), []);

if ($form->is_cancelled()) {
    redirect($instance_url->out(false));
}

else if ($formadata = $form->get_data()) {
    if ($questionid == 0) {
        // Add new
        $obj_question = new stdclass;
    }
    else {
        $obj_question = $DB->get_record('application_question', ['id' => $questionid]);
        if (empty($obj_question)) {
            $obj_question = new stdclass;
            $questionid = 0;
        }
    }
    $answers                        = array_filter($answers, function($value) { return !is_null($value) && trim($value) !== ''; });
    $obj_question->question_content = json_encode([
                                        'question_type' => 'multiple_choices',
                                        'question_name' => $formadata->question_name,
                                        'question_text' => $formadata->question_text,
                                        'choices'       => $answers]);

    $obj_question->categoryid       = $formadata->category;
    $obj_question->required         = $formadata->required == 1 ? 1 : 0;

    if ($questionid == 0) {
        $new_createdid = $DB->insert_record('application_question', $obj_question);
        redirect($instance_url->out(false) . '&new_questionids[]=' . $new_createdid);
    }
    else {
        $DB->update_record('application_question', $obj_question);
        redirect($instance_url->out(false));
    }
}

echo $OUTPUT->header();

$form->display();


echo $OUTPUT->footer();
>>>>>>> 50f475fc90ed57a62c9dd5bf62b657f8b9598e76
