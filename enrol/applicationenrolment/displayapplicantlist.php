<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han (hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_once($CFG->dirroot . '/enrol/applicationenrolment/lib.php');

global $CFG, $DB, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$enrolid = required_param('enrolid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

if (!has_capability('enrol/applicationenrolment:manage', $PAGE->context)) {
    echo get_string('nopermission', 'enrol_applicationenrolment');
    exit();
}

$PAGE->set_pagelayout('incourse');

$url_applicationlist = new \moodle_url('/enrol/applicationenrolment/displayapplicantlist.php');
$PAGE->set_url($url_applicationlist, []);

$pagetitle = 'Applicants';

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$PAGE->requires->yui_module('moodle-core-notification', 'notification_init');

$course_url = new moodle_url('/enrol/instances.php', ['id' => $courseid]);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Enrolment methods', $course_url->out(false));
$PAGE->navbar->add($pagetitle);

$button_review_save = optional_param('button_review_save', '', PARAM_RAW);
if (!empty($button_review_save)) {

    $applicationid = required_param('applicationid', PARAM_INT);
    $select_reviewstates = required_param('select_reviewstates', PARAM_RAW);
    $textarea_review = optional_param('textarea_review', '', PARAM_RAW);

    $obj = new \stdclass;
    $obj->id = $applicationid;
    $obj->comment = $textarea_review;
    $obj->assessment_state = $select_reviewstates;
    $obj->evaluatorid = $USER->id;

    $DB->update_record('student_apply_course', $obj);
}
else if (!empty(optional_param('button_deny', '', PARAM_RAW))) {
    $applicationid = required_param('applicationid', PARAM_INT);

    $obj = $DB->get_record('student_apply_course', ['id' => $applicationid]);
    $obj->assessment_state = 'Denied';
    $obj->application_state = 'Pending';
    $obj->application_button_state = 'Submitted';
    $obj->evaluatorid = $USER->id;

    $DB->update_record('student_apply_course', $obj);

    $emailtemplate = $DB->get_field('enrol', 'customtext2', ['id' => $enrolid]);
    $emailtemplate = empty(trim($emailtemplate)) ? get_string('emailtemplate_deniedcontent', 'enrol_applicationenrolment') : $emailtemplate;

    // $url_appform = new \moodle_url('/enrol/applicationenrolment/loadapplicationform.php',
    //                         ['courseid' => $courseid, 'applicationid' => $applicationid]);
    // $emailtemplate = str_ireplace('[HYPERLINK_APPLICATION_FORM]',
    //                                 '<a href="'.$url_appform->out(false).'">application</a>', $emailtemplate);

    $emailtemplate = str_ireplace('[HYPERLINK_APPLICATION_FORM]',
                                    '<a href="'.$course_url->out(false).'">application</a>', $emailtemplate);
    $subject = 'Your application for ';
    send_email_and_log($obj->studentid, $courseid, $subject, $emailtemplate);
}
else if (!empty(optional_param('button_approve', '', PARAM_RAW))) {
    $applicationid = required_param('applicationid', PARAM_INT);

    $obj = $DB->get_record('student_apply_course', ['id' => $applicationid]);
    $obj->assessment_state = 'Approved';
    $obj->application_state = 'Pending';
    $obj->application_button_state = 'Submitted';
    $obj->evaluatorid = $USER->id;

    $DB->update_record('student_apply_course', $obj);

    // Enrol this user.
    $plugin = enrol_get_plugin('applicationenrolment');

    if (empty($plugin)) {
        // No plugin found.
        throw new moodle_exception('No Application Enrollment plugin found.', 'enrol_applicationenrolment', '', null);
        exit;
    }

    $instance = $plugin->get_instance_record($courseid);

    if (empty($instance)) {
        // No ApplicationEnrollment instance found for plugin.
        throw new moodle_exception('No Application Enrollment plugin instance found for course id:'.$courseid,
            'enrol_applicationenrolment', '', null);
        exit;
    }

    $user = $DB->get_record('user', ['id' => $obj->studentid]);
    $plugin->enrol($instance, $user);

    $emailtemplate = $DB->get_field('enrol', 'customtext1', ['id' => $enrolid]);
    $emailtemplate = empty(trim($emailtemplate)) ? get_string('emailtemplate_approvedcontent', 'enrol_applicationenrolment') : $emailtemplate;
    $subject = 'Congratulations and Welcome to ';
    send_email_and_log($obj->studentid, $courseid, $subject, $emailtemplate);
}
else if (!empty(optional_param('delete_selected_attempts', '', PARAM_RAW))) {

    $selected_application_ids = required_param('selected_application_ids', PARAM_RAW);
    $selected_application_ids = json_decode($selected_application_ids);

    $arr_filenames = [];
    $sql = "SELECT id, question_answer FROM {student_apply_course} WHERE id IN (" . implode(',', $selected_application_ids) . ")";
    $question_answers = $DB->get_records_sql($sql, []);
    foreach($question_answers as $key => $question_answer) {
        $decoded_questanswers = json_decode($question_answer->question_answer);
        foreach($decoded_questanswers as $pair) {
            if (!empty($pair->filename)) {
                $arr_filenames[] = $pair->filename;
            }
        }
    }

    foreach($arr_filenames as $key => $filename) {
        if (is_file($filename)) {
            unlink($filename);
        }
    }

    $sql = "DELETE FROM {student_apply_course} WHERE id IN (" . implode(',', $selected_application_ids) . ")";
    $DB->execute($sql, []);
}
else if (!empty(optional_param('download_selected_responses', '', PARAM_RAW)) ||
            !empty(optional_param('download_all', '', PARAM_RAW))) {

    $selected_application_ids = required_param('selected_application_ids', PARAM_RAW);

    if ($selected_application_ids == 'all') {
        $where = 'WHERE sac.courseid = ' . $courseid;
    }
    else {
        $selected_application_ids = json_decode($selected_application_ids);
        $where = "WHERE sac.id IN " . "(" . implode(',', $selected_application_ids) . ")";
    }

    $sql = "SELECT  sac.id,
                    u.firstname,
                    u.lastname,
                    u.email,
                    sac.application_state,
                    sac.assessment_state,
                    sac.question_answer,
                    sac.timecreated,
                    sac.timemodified
            FROM {student_apply_course} sac JOIN {user} u ON sac.studentid = u.id
            $where";

    $applications = $DB->get_records_sql($sql, []);

    $fullfilepath = $CFG->tempdir . '/applicationenrolment/tmp/applications_'. time() .'.csv';
    make_temp_directory('applicationenrolment/tmp/');

    $fp = fopen($fullfilepath, "w");

    $headerrow = '';

    foreach ($applications as $application) {

        $currow  = $application->firstname;
        $currow .= ',' . $application->lastname;
        $currow .= ',' . $application->email;

        if ($application->application_state == 'Pending') {
            $currow .= ',Completed';
        }
        else {
            $currow .= ',Started';
        }
        $currow .= ',' . userdate($application->timecreated, '%B %d %Y %I:%M %p');
        $currow .= ',' . userdate($application->timemodified, '%B %d %Y %I:%M %p');

        if (!empty($application->assessment_state)) {
            $currow .= ',' . $application->assessment_state;
        }
        else if ($application->application_state == 'Pending') {
            $currow .= ',Needs Review';
        }
        else {
            $currow .= ',';
        }

        $question_answers = json_decode($application->question_answer);

        foreach($question_answers as $pair) {

            if (empty($headerrow)) {
                $answer_count = empty($answer_count) ? 1 : $answer_count + 1;
                $header_reponses = empty($header_reponses) ? ",Response $answer_count" : $header_reponses . ",Response $answer_count";
            }

            $answer = strip_tags($pair->answer);
            $answer = str_replace(',', '', $answer);
            $currow .= ',' . $answer;
        }

        if (empty($headerrow)) {
            $headerrow = "Last Name,First Name,Email Address,Submission,Started On,Completed On,Status" . $header_reponses;
            fwrite($fp, $headerrow . "\n");
        }

        fwrite($fp, $currow .= "\n");
    }
    fflush($fp);
    fclose($fp);

    $fileName = basename($fullfilepath);
    $fileSize = filesize($fullfilepath);
    // ob_end_clean(); // => The ob_start command buffers any output (eg via echo, printf) and prevents anything being send to the user BEFORE your actual download. The ob_end_clean then stops this behavior and allows direct output again
    // Output headers.
    header("Cache-Control: private");
    header("Content-Type: application/stream");
    header("Content-Length: ".$fileSize);
    header("Content-Disposition: attachment; filename=".$fileName);
    // Output file.
    readfile ($fullfilepath);
    // chown($fullfilepath, 666);
    // @chmod($fullfilepath, 666);
    unlink($fullfilepath);

    exit;
}

echo $OUTPUT->header();

$sql = "SELECT  sac.id as applicationid,
                sac.studentid,
                sac.courseid,
                sac.application_state,
                sac.timecreated,
                sac.timemodified,
                sac.evaluatorid,
                sac.assessment_state,
                u.firstname,
                u.lastname,
                u.email
        FROM {student_apply_course} sac
        JOIN {user} u ON u.id = sac.studentid
        WHERE sac.courseid = $courseid";

$applications = $DB->get_records_sql($sql, []);

$evaluatorids = array_filter(array_unique(array_column($applications, 'evaluatorid')));
if(!empty($evaluatorids)) {
    $sql = "SELECT id,firstname,lastname FROM {user} WHERE id IN (" . implode(',', $evaluatorids) . ")";
    $evaluatornames = $DB->get_records_sql($sql, []);
}

$html = '<h2>Applicants</h2>';
$html .= '<p><b>Attempts:</b> ' . count($applications) . '</p>';
$html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_applicationlist compact">';

if (empty($applications)) {
    $html .= '<tr><td>No any students applied yet.</td></tr>';
}
else {
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th class="checkbox_cell"></th>';
    $html .= '<th class="fullname">First name / Last name</th>';
    $html .= '<th class="email">Email Address</th>';
    $html .= '<th class="roles">Roles</th>';
    $html .= '<th class="submission">Submission</th>';
    $html .= '<th class="startedon">Started on</th>';
    $html .= '<th class="completed">Completed</th>';
    $html .= '<th class="status">Status</th>';
    $html .= '<th class="coursedirector">Course Director</th>';
    $html .= '<th class="applicants">Applicants</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    foreach ($applications as $application) {

        $timecreated  = $application->timecreated == 0 ? '' : userdate($application->timecreated, '%b %d, %Y %I:%M %p');
        $timemodified = $application->timecreated == 0 ? '' : userdate($application->timemodified, '%b %d, %Y %I:%M %p');

        // if ($application->application_state == 'Pending') {

            $url_reviewform = new moodle_url('/enrol/applicationenrolment/loadreviewapplication.php',
                                                [   'applicationid' => $application->applicationid,
                                                    'enrolid' => $enrolid,
                                                    'courseid' => $courseid
                                                ]);
            $url_reviewform = '<br><a href="'.$url_reviewform->out(false).'" class="link_reviewapp">Review application</a>';
        // }
        // else {
        //     $url_reviewform = '';
        // }

        $html .= '<tr data-applicationid="'. $application->applicationid .'">';
        $html .= '<td></td>';
        $html .= "<td><span>$application->firstname $application->lastname</span>$url_reviewform</td>";
        $html .= "<td>$application->email</td>";
        $html .= "<td>Student</td>";
        if ($application->application_state == 'Pending') {
            $html .= "<td>Completed</td>";
        }
        else if ($application->application_state == 'Incomplete') {
            $html .= "<td>Started</td>";
        }
        $html .= "<td order='$application->timecreated'>$timecreated</td>";

        if ($application->application_state == 'Incomplete') {
            $html .= "<td></td>";
        }
        else {
            $html .= "<td order='$application->timemodified'>$timemodified</td>";
        }

        if (!empty($application->assessment_state)) {
            $html .= "<td>$application->assessment_state</td>";
        }
        else if ($application->application_state == 'Pending') {
            $html .= "<td>Needs review</td>";
        }
        else {
            $html .= "<td></td>";
        }

        if(!empty($evaluatornames[$application->evaluatorid])) {
            $html .= "<td>" . $evaluatornames[$application->evaluatorid]->firstname . " " .
                      $evaluatornames[$application->evaluatorid]->lastname . "</td>";
        }
        else {
            $html .= "<td></td>";
        }
        $html .= "<td>$application->firstname $application->lastname</td>";

        $html .= '</tr>';
    }
}
$html .= '</tbody>';
$html .= '</table>';

$html .= '
        <form method="post" action="'. $url_applicationlist->out(false) .'" style="margin-top: 20px;">
            <input type="submit" class="btn btn-primary" name="delete_selected_attempts" value="Delete selected attempts" />
            <input type="submit" class="btn btn-primary" name="download_selected_responses" value="Download selected responses"/>
            <input type="submit" name="download_all" value="Download table as CSV" style="visibility:hidden;"/>
            <input type="hidden" name="courseid" id="courseid" value="'. $courseid .'" />
            <input type="hidden" name="enrolid" id="enrolid" value="'. $enrolid .'" />
            <input type="hidden" name="selected_application_ids" value="" />
        </form>';

echo $html;

echo '
    <!--<link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/datatables.bootstrap.min.css"/>-->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/responsive.bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/buttons.datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/select.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/select.bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/searchpanes.datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot .'/enrol/applicationenrolment/datatables/css/jquery.datetimepicker.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="'.$CFG->wwwroot.'/enrol/applicationenrolment/datatables/js/entry.js"></script>
    <script src="'.$CFG->wwwroot.'/enrol/applicationenrolment/datatables/js/php-date-formatter.min.js"></script>';

echo '<style type="text/css">
        #region-main { min-height: 1000px; }
        .dt-buttons { margin-bottom: 20px; }
        .dataTables_length { clear: left; }
        input[type=search] { border: 1px solid #000; }
        td.editable {
            font-weight: bold;
        }
        table.dataTable th {
            color: #0f6cbf;
        }
        table.dataTable th.checkbox_cell {
            position: relative;
        }
        table.dataTable th.checkbox_cell:before {
            content: " ";
            margin-top: -6px;
            margin-left: -6px;
            border: 1px solid black;
            border-radius: 3px;
            position: absolute;
            width: 12px;
            height: 12px;
            top: 50%;
            left: 50%;
        }
        table.dataTable tr th.checkbox_cell.selected:after,
        table.dataTable tr.selected td.select-checkbox:after {
            /*content: "✔";
            content: "\2714";*/
            content: "\2713";
            margin-top: -14px;
            margin-left: -4px;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            position: absolute;
            /*text-shadow: 1px 1px #B0BED9, -1px -1px #B0BED9, 1px -1px #B0BED9, -1px 1px #B0BED9;*/
            /*top: 50%;*/
            left: 50%;
            text-shadow: 0 0 black;
            color: #000;
        }
        table.dataTable tr.selected td.select-checkbox:after {
            margin-top: -14px;
        }
        .link_reviewapp { font-size: 12px; color: #0f6cbf; text-decoration: none; position: relative; top: -5px; }
        .link_reviewapp:hover { text-decoration: underline; }
        button.dt-button { background: #0f6cbf; color: #fff; border: 1px solid #0f6cbf;}
        button.dt-button:hover { background: #0f6cbf !important; color: #fff; border: 1px solid #0f6cbf;}
    </style>';

echo $OUTPUT->footer();
