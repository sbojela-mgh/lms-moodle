<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han (hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $DB, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$enrolid = required_param('enrolid', PARAM_INT);
$applicationid = required_param('applicationid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);

if (!has_capability('enrol/applicationenrolment:manage', $PAGE->context)) {
    echo get_string('nopermission', 'enrol_applicationenrolment');
    exit();
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_url(new moodle_url('/enrol/applicationenrolment/loadreviewapplication.php'), []);

$pagetitle = 'Applicant';

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$course_url = new moodle_url('/enrol/applicationenrolment/displayapplicantlist.php',
                                ['courseid' => $courseid, 'enrolid' => $enrolid]);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add('Applicants', $course_url->out(false));
$PAGE->navbar->add($pagetitle);

$sql = "SELECT  sac.id,
                sac.application_state,
                sac.timemodified,
                sac.question_answer,
                sac.assessment_state,
                sac.comment,
                u.firstname,
                u.lastname,
                u.email
        FROM {student_apply_course} sac
        JOIN {user} u ON u.id = sac.studentid
        WHERE sac.id = $applicationid";

$application = $DB->get_record_sql($sql, []);

$question_answer = json_decode($application->question_answer);

$html_answers = '<p style="font-weight:bold;font-size: 18px;">Responses</p>';
$sequence = 1;
foreach ($question_answer as $key => $pair) {

    $html_answers .= '<div class="row"><div class="col-12">';
    $html_answers .= '<table class="pair_table" boder="0" cellspacing="0" cellpadding="0" width="100%"><tr><td>';
    $html_answers .= '<span class="pair_sequence">' . $sequence . '. &nbsp;</span></td>';
    $html_answers .= '<td>'. $pair->question .'</td></tr>';
    $html_answers .= '<tr><td colspan="2"><textarea name="pair_answer">'.$pair->answer.'</textarea></td></tr>';

    $url_tofiledownload = new \moodle_url('/enrol/applicationenrolment/getapplicationdoc.php',[
                        'courseid'=>$courseid, 'applicationid'=>$applicationid, 'questionid'=>$key, 'sesskey'=>sesskey()]);
    if(!empty($pair->filename)) {

        $html_answers .= '<tr><td colspan="2"><p>'. basename($pair->filename) .'&nbsp; &nbsp; <a href="'. $url_tofiledownload->out(false) .'">download</a></p></td></tr>';
    }

    $html_answers .= '</table>';
    $html_answers .= '</div></div>';
    $sequence += 1;
}

$submission_state = $application->application_state == 'Pending' ? 'Completed' : 'Started';

$html_select_review = '<select name="select_reviewstates">';
$html_select_review .= '<option value="Needs review" '.
                        ($application->assessment_state=='Needs review' ? 'selected' : '').'>Needs review</option>';
$html_select_review .= '<option value="In review" '.
                        ($application->assessment_state=='In review' ? 'selected' : '').'>In review</option>';
$html_select_review .= '</select>';

$url_applicationlist = new \moodle_url('/enrol/applicationenrolment/displayapplicantlist.php');

echo $OUTPUT->header();

$html = '
    <div class="container">
        <h2>Applicant</h2>
        <form method="post" action="'. $url_applicationlist->out(false) .'">
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <table border="0" cellspacing="0" cellpadding="0" width="100%" class="applicantion_info">
                    <tr>
                        <td>Name</td>
                        <td>'. $application->firstname . ' ' . $application->lastname .'</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>'. $application->email .'</td>
                    </tr>
                    <tr>
                        <td>Submission</td>
                        <td>'. $submission_state .'</td>
                    </tr>
                    <tr>
                        <td>Completed on</td>
                        <td>'. userdate($application->timemodified, '%B %d, %Y %I:%M %p') .'</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>'. $html_select_review .'</td>
                    </tr>
                </table>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="wraper_review">
                    <p style="text-align: left;margin: 0;">Comments:</p><p style="text-align:left;margin:0;padding:0;">' .
                    get_string('comment_review_instruction', 'enrol_applicationenrolment') . '</p>' .
                    '<textarea name="textarea_review">'. $application->comment .'</textarea>
                    <input type="submit" class="btn btn-primary" name="button_review_save" value="Save" />
                    <input type="hidden" name="courseid" id="courseid" value="'. $courseid .'" />
                    <input type="hidden" name="enrolid" id="enrolid" value="'. $enrolid .'" />
                    <input type="hidden" name="applicationid" value="'. $applicationid .'" />
                </div>
            </div>
        </div>
        </form>
        ' . $html_answers . '
        <div class="row">
            <div class="col-12 app_group_button">
                <form method="post" action="'. $url_applicationlist->out(false) .'">
                    <input type="submit" class="btn btn-primary " name="button_approve" value="Approve" />
                    <input type="submit" class="btn" name="button_deny" id="id_button_deny" value="Deny" />
                    <input type="hidden" name="courseid" id="courseid" value="'. $courseid .'" />
                    <input type="hidden" name="enrolid" id="enrolid" value="'. $enrolid .'" />
                    <input type="hidden" name="applicationid" value="'. $applicationid .'" />
                </form>
            </div>
        </div>
    </div>

    <style type="text/css">
        .applicantion_info { border: 1px solid #e3e1e1; }
        .applicantion_info td { height: 40px; }
        .applicantion_info td:first-child {
            width: 30%; text-align: right; padding-right: 30px; border-right: 1px solid #9d9595;
        }
        .applicantion_info td:first-child + td {
            width: 70%;
            padding-left: 24px;
        }
        .applicantion_info tr:nth-child(odd) td {
            background: #f1f1f1;
        }
        .wraper_review {
            border: 1px solid #9d9595; padding: 12px 20px; text-align: right;
        }
        [name=textarea_review] {
            border: 1px solid #9d9595; width: 100%; height: 140px;
        }
        [name=pair_answer] { width: 100%; height: 100px; }
        .pair_table { margin-top: 20px; }
        .pair_table td { vertical-align: text-bottom; }
        .pair_table td:first-child { width: 2%; }
        .pair_table .pair_sequence { font-weight: bold; }
        .app_group_button { text-align: center; margin-top: 20px; }
        .app_group_button input[type=submit] { font-weight: bold; color: #fff; width: 120px; }
        .app_group_button input[type=submit]:first-child + input { background: #aaa; margin-left: 20px; color: #000; }
    </style>';

echo $html;

echo $OUTPUT->footer();