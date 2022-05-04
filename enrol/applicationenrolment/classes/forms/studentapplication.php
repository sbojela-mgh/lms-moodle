<<<<<<< HEAD
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_applicationenrolment;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/outputcomponents.php');

use coding_exception;
use moodleform;
use html_writer;

class studentapplication extends moodleform {

    /**
     * Makes the form elements.
     */
    public function definition() {

        global $DB, $USER;

        $file_size_limit = get_max_upload_file_size();

        $mform =& $this->_form;
        $data = $this->_customdata;

        $courseid = $data['courseid'];
        $applicationid = $data['applicationid'];

        $submitable = true;

        if ($applicationid != 0) {
            $application = $DB->get_record('student_apply_course', ['id' => $applicationid]);
            if (!empty($application)) {
                if ($application->application_state == 'Pending') {
                    $submitable = false;
                }
                $question_answers = json_decode($application->question_answer);
            }
        }

        $questionids = $DB->get_field('enrol', 'customchar1', ['courseid' => $courseid, 'enrol' => 'applicationenrolment']);

        $arr_questionids = explode(',', $questionids);

        $sql = "SELECT * FROM {application_question} WHERE id IN ($questionids)";
        $questions = $DB->get_records_sql($sql, []);

        $html = '';
        $quest_order = 1;
        foreach($arr_questionids as $questionidonly) {

            $question = $questions[$questionidonly]; // To keep the order
            $question_content = json_decode($question->question_content);

            $html .= '<div class="app_question_text">';
            $html .= '<table class="app_question_text_tbl" boder="0" cellspacing="0" cellpadding="0"><tr><td>'. $quest_order . '.';
            $html .= $question->required == 1 ? '<span class="star"> *&nbsp;</span> ' : '&nbsp;';
            $html .= '</td>';
            $html .= '<td>' . $question_content->question_text->text . '</td</tr>';
            $html .= '</tr></table></div>';
            $html .= '<div class="app_question_answerfield">';
            if ($question_content->question_type == 'text') {
                if (!empty($question_answers)) {
                    $html .= "<textarea name='questionid_$question->id' rows='4' ". ($question->required == 1 ? 'class="quest_required"' : '') ." style='width:70%;'>";
                    $html .= $question_answers->$questionidonly->answer;
                    $html .= '</textarea>';
                }
                else {
                    $html .= "<textarea name='questionid_$question->id' rows='4' ".($question->required == 1 ? 'class="quest_required"' : '')." style='width:70%;'></textarea>";
                }
            }
            else if ($question_content->question_type == 'multiple_choices') {
                $html .= "<select name='questionid_$question->id' ".($question->required == 1 ? 'class="quest_required"' : '')." style='width:70%;'>";
                $html .= '<option value=""></option>';
                foreach($question_content->choices as $choice) {
                    if (!empty($question_answers)) {
                        $selected = $question_answers->$questionidonly->answer == $choice ? 'selected' : '';
                        $html .= "<option value='". htmlentities($choice) ."' $selected>$choice</option>";
                    }
                    else {
                        $html .= "<option value='". htmlentities($choice) ."'>$choice</option>";
                    }
                }
                $html .= "</select>";
            }

            if ($question->allow_attachments == 1) {
                if (!empty($question_answers->$questionidonly->filename) && is_file($question_answers->$questionidonly->filename)) {
                    $html .= '<p>' . basename($question_answers->$questionidonly->filename) . '</p>';
                }
                if ($submitable) {
                    $mform->addElement('html', $html);
                    $html = '';
                    $mform->addElement('filepicker', 'filequestionid_'.$question->id, null, null, array('maxbytes' => $file_size_limit));
                    if ($question->required_attachments == 1) {
                        if (empty($question_answers->$questionidonly->filename)) {
                            $mform->addRule('filequestionid_'.$question->id, 'Please select a file!', 'required');
                        }
                    }
                }
            }

            $html .= '</div>';

            $quest_order += 1;
        }

        if ($submitable) {
            $html .= '
                <div class="app_group_button">
                    <input type="button" class="btn btn-primary " name="button_save" id="id_button_save" value="Save" />
                    <input type="button" class="btn btn-primary " name="button_submit" id="id_button_submit" value="Submit" />
                    <input type="hidden" name="courseid" id="courseid" value="'. $courseid .'" />
                    <input type="hidden" name="applicationid" id="applicationid" value="'. $applicationid .'" />
                    <input type="hidden" name="button_clicked" id="button_clicked" value="" />
                </div>';
        }
        else {
            $course_url = new \moodle_url('/course/view.php', ['id' => $courseid]);
            $html .= '<div class="app_group_button">';
            $html .= '<input type="button" class="btn btn-primary " name="backtocourse" id="id_backtocourse" value="Back to course page" ';
            $html .= 'onclick="window.location=\'' . $course_url->out(false) . '\';"';
            $html .= '/></div>';

            $html .= '<style type="text/css">input[type=text], select, textarea { pointer-events: none; }</style>';
        }

        $html .= '
            <style type="text/css">
                .app_question_text { margin-top: 20px; max-width: 70%;}
                .app_question_text_tbl td { vertical-align: top; }
                .app_group_button { margin-top: 40px; }
                .mform fieldset { margin-left: 0; }
                .app_question_answerfield > .fitem { max-width: 73%; }
                .app_question_answerfield > .fitem > div:first-child {display: none !important;}
                .app_question_answerfield > .fitem > div:first-child + div { width: 100% !important; max-width: 100% !important; flex: 0 0 100%; }
                #fgroup_id_buttonar { display: none; }
                .fdescription.required { margin-top: 20px; }
                .form-control-feedback.invalid-feedback { font-size: 16px; font-weight: 600; }
            </style>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#id_button_save, #id_button_submit").click(function() {

                        var submit_ok = true;
                        if( $(this).val() == "Submit" ) {
                            $(".quest_required").each(function() {
                                if ($(this).val().trim() == "") {
                                    $(this).css("border", "1px solid RED");
                                    submit_ok = false;
                                }
                            });
                        }

                        if (submit_ok) {
                            $("#button_clicked").val($(this).val());
                            $("#id_submitbutton").click();
                        }
                        else {
                            require(["core/notification"], function(notification) {
                                notification.alert("Info", "Please fill in all required fields.", "OK");
                            });
                        }
                    });

                });
            </script>';

        $mform->addElement('html', $html);

        if ($submitable) {
            $this->add_action_buttons(true, get_string('savechanges'));
        }
    }

    public function validation($data, $files) {

        return array();
    }

}

=======
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_applicationenrolment;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/outputcomponents.php');

use coding_exception;
use moodleform;
use html_writer;

class studentapplication extends moodleform {

    /**
     * Makes the form elements.
     */
    public function definition() {

        global $DB, $USER;

        $file_size_limit = get_max_upload_file_size();

        $mform =& $this->_form;
        $data = $this->_customdata;

        $courseid = $data['courseid'];
        $applicationid = $data['applicationid'];

        $submitable = true;

        if ($applicationid != 0) {
            $application = $DB->get_record('student_apply_course', ['id' => $applicationid]);
            if (!empty($application)) {
                if ($application->application_state == 'Pending') {
                    $submitable = false;
                }
                $question_answers = json_decode($application->question_answer);
            }
        }

        $questionids = $DB->get_field('enrol', 'customchar1', ['courseid' => $courseid, 'enrol' => 'applicationenrolment']);

        $arr_questionids = explode(',', $questionids);

        $sql = "SELECT * FROM {application_question} WHERE id IN ($questionids)";
        $questions = $DB->get_records_sql($sql, []);

        $html = '';
        $quest_order = 1;
        foreach($arr_questionids as $questionidonly) {

            $question = $questions[$questionidonly]; // To keep the order
            $question_content = json_decode($question->question_content);

            $html .= '<div class="app_question_text">';
            $html .= '<table class="app_question_text_tbl" boder="0" cellspacing="0" cellpadding="0"><tr><td>'. $quest_order . '.';
            $html .= $question->required == 1 ? '<span class="star"> *&nbsp;</span> ' : '&nbsp;';
            $html .= '</td>';
            $html .= '<td>' . $question_content->question_text->text . '</td</tr>';
            $html .= '</tr></table></div>';
            $html .= '<div class="app_question_answerfield">';
            if ($question_content->question_type == 'text') {
                if (!empty($question_answers)) {
                    $html .= "<textarea name='questionid_$question->id' rows='4' ". ($question->required == 1 ? 'class="quest_required"' : '') ." style='width:70%;'>";
                    $html .= $question_answers->$questionidonly->answer;
                    $html .= '</textarea>';
                }
                else {
                    $html .= "<textarea name='questionid_$question->id' rows='4' ".($question->required == 1 ? 'class="quest_required"' : '')." style='width:70%;'></textarea>";
                }
            }
            else if ($question_content->question_type == 'multiple_choices') {
                $html .= "<select name='questionid_$question->id' ".($question->required == 1 ? 'class="quest_required"' : '')." style='width:70%;'>";
                $html .= '<option value=""></option>';
                foreach($question_content->choices as $choice) {
                    if (!empty($question_answers)) {
                        $selected = $question_answers->$questionidonly->answer == $choice ? 'selected' : '';
                        $html .= "<option value='". htmlentities($choice) ."' $selected>$choice</option>";
                    }
                    else {
                        $html .= "<option value='". htmlentities($choice) ."'>$choice</option>";
                    }
                }
                $html .= "</select>";
            }

            if ($question->allow_attachments == 1) {
                if (!empty($question_answers->$questionidonly->filename) && is_file($question_answers->$questionidonly->filename)) {
                    $html .= '<p>' . basename($question_answers->$questionidonly->filename) . '</p>';
                }
                if ($submitable) {
                    $mform->addElement('html', $html);
                    $html = '';
                    $mform->addElement('filepicker', 'filequestionid_'.$question->id, null, null, array('maxbytes' => $file_size_limit));
                    if ($question->required_attachments == 1) {
                        if (empty($question_answers->$questionidonly->filename)) {
                            $mform->addRule('filequestionid_'.$question->id, 'Please select a file!', 'required');
                        }
                    }
                }
            }

            $html .= '</div>';

            $quest_order += 1;
        }

        if ($submitable) {
            $html .= '
                <div class="app_group_button">
                    <input type="button" class="btn btn-primary " name="button_save" id="id_button_save" value="Save" />
                    <input type="button" class="btn btn-primary " name="button_submit" id="id_button_submit" value="Submit" />
                    <input type="hidden" name="courseid" id="courseid" value="'. $courseid .'" />
                    <input type="hidden" name="applicationid" id="applicationid" value="'. $applicationid .'" />
                    <input type="hidden" name="button_clicked" id="button_clicked" value="" />
                </div>';
        }
        else {
            $course_url = new \moodle_url('/course/view.php', ['id' => $courseid]);
            $html .= '<div class="app_group_button">';
            $html .= '<input type="button" class="btn btn-primary " name="backtocourse" id="id_backtocourse" value="Back to course page" ';
            $html .= 'onclick="window.location=\'' . $course_url->out(false) . '\';"';
            $html .= '/></div>';

            $html .= '<style type="text/css">input[type=text], select, textarea { pointer-events: none; }</style>';
        }

        $html .= '
            <style type="text/css">
                .app_question_text { margin-top: 20px; max-width: 70%;}
                .app_question_text_tbl td { vertical-align: top; }
                .app_group_button { margin-top: 40px; }
                .mform fieldset { margin-left: 0; }
                .app_question_answerfield > .fitem { max-width: 73%; }
                .app_question_answerfield > .fitem > div:first-child {display: none !important;}
                .app_question_answerfield > .fitem > div:first-child + div { width: 100% !important; max-width: 100% !important; flex: 0 0 100%; }
                #fgroup_id_buttonar { display: none; }
                .fdescription.required { margin-top: 20px; }
                .form-control-feedback.invalid-feedback { font-size: 16px; font-weight: 600; }
            </style>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#id_button_save, #id_button_submit").click(function() {

                        var submit_ok = true;
                        if( $(this).val() == "Submit" ) {
                            $(".quest_required").each(function() {
                                if ($(this).val().trim() == "") {
                                    $(this).css("border", "1px solid RED");
                                    submit_ok = false;
                                }
                            });
                        }

                        if (submit_ok) {
                            $("#button_clicked").val($(this).val());
                            $("#id_submitbutton").click();
                        }
                        else {
                            require(["core/notification"], function(notification) {
                                notification.alert("Info", "Please fill in all required fields.", "OK");
                            });
                        }
                    });

                });
            </script>';

        $mform->addElement('html', $html);

        if ($submitable) {
            $this->add_action_buttons(true, get_string('savechanges'));
        }
    }

    public function validation($data, $files) {

        return array();
    }

}

>>>>>>> 50f475fc90ed57a62c9dd5bf62b657f8b9598e76
