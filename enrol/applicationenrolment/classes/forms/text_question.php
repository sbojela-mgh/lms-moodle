<?php
/**
 *
 * @package   	enrol_applicationenrolment
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_applicationenrolment;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/outputcomponents.php');

use coding_exception;
use moodleform;
use html_writer;

class text_question extends moodleform {

    /**
     * Makes the form elements.
     */
    public function definition() {

        global $DB;

        $mform =& $this->_form;

        $courseid = optional_param('courseid', '', PARAM_INT);
        $instanceid = optional_param('instanceid', 0, PARAM_INT);
        $questionid = optional_param('questionid', 0, PARAM_INT);

        if ($questionid != 0) {
            // Editing an existing question
            $question = $DB->get_record('application_question', ['id' => $questionid]);
            if (!empty($question)) {
                $question_content = json_decode($question->question_content);
            }
            else {
                $questionid = 0;
            }
        }

        $courses = $DB->get_records_sql('SELECT id, shortname FROM {course} WHERE id <> 1');

        $html_general = '<legend class="legend_app"><a class="legend_title" href="#"><i class="icon fa fa-caret-down"></i>General</a></legend><div>';
        $mform->addElement('html', $html_general);

        $options = ['-1' => '', '0' => 'System'];
        foreach ($courses as $course) {
            $options[$course->id] = $course->shortname;
        }

        $select = $mform->addElement('select', 'category', 'Category', $options, ['data-placeholder' => 'Select a category']);
        if ($questionid != 0) {
            $select->setSelected($question->categoryid);
        }

        $mform->addElement('text', 'question_name', 'Question name', array('size' => '48'));
        $mform->setType('question_name', PARAM_TEXT);
        $mform->addRule('question_name', '255 letters limit', 'maxlength', 255, 'client');
        $questionid != 0 ? $mform->setDefault('question_name', $question_content->question_name) : '';

        $mform->addElement('editor', 'question_text', 'Question text', ['rows' => 6], []);
        $mform->setType('question_text', PARAM_RAW);
        if ($questionid != 0) {
            $mform->setDefault('question_text', ['text' => $question_content->question_text->text, 'format' => FORMAT_HTML]);
        }

        $options = ['No', 'Yes'];
        $select = $mform->addElement('select', 'required', 'Required', $options);
        $questionid != 0 ? $select->setSelected($question->required) : $select->setSelected('0');

        $mform->addElement('hidden', 'courseid', 0);
        $mform->setDefault('courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'instanceid', 0);
        $mform->setDefault('instanceid', $instanceid);
        $mform->setType('instanceid', PARAM_INT);

        $mform->addElement('hidden', 'questionid', 0);
        $mform->setDefault('questionid', $questionid);
        $mform->setType('questionid', PARAM_INT);

        $mform->addElement('html', '</div>');

        $html_general = '<legend class="legend_app"><a class="app_title" href="#"><i class="icon fa fa-caret-down"></i>Response Options</a></legend><div style="border-bottom: 1px solid #000; margin-bottom: 40px;">';
        $mform->addElement('html', $html_general);

        $options = ['No', 'Yes'];
        $select = $mform->addElement('select', 'allow_attachments', 'Allow attachments', $options);
        if ($questionid != 0) {
            $select->setSelected($question->allow_attachments);
        }

        $options = ['No', 'Yes'];
        $select = $mform->addElement('select', 'required_attachments', 'Required attachments', $options);
        if ($questionid != 0) {
            $select->setSelected($question->required_attachments);
        }

        $options = [5 => '5 lines',10 => '10 lines',15 => '15 lines',20 => '20 lines',25 => '25 lines',30 => '30 lines'];
        $select = $mform->addElement('select', 'inbox_text_size', 'Inbox text size', $options);
        if ($questionid != 0) {
            $select->setSelected($question_content->inbox_text_size);
        }

        $mform->addElement('html', '</div><script src="https://harvesthq.github.io/chosen/chosen.jquery.js"></script><link rel="stylesheet" href="https://harvesthq.github.io/chosen/chosen.css" type="text/css" /><style type="text/css">.chosen-container { min-width: 200px; font-size: 16px; }.chosen-container-single .chosen-default,.chosen-container-single .chosen-single { color: #000; border: 1px solid rgb(143 149 158); box-shadow: 0 0 0 #fff inset, 0 0 0 #fff; border-radius: 0; height: 34px; line-height: 32px; }.chosen-container-single .chosen-single div b{ background-position: 0 8px;}</style>');

        if ($questionid != 0) {
            $this->add_action_buttons(true, get_string('savechanges'));
        }
        else {
            $this->add_action_buttons(true, 'Create question');
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!isset($data['question_name']) || empty($data['question_name']))   {
            $errors['question_name'] = 'Please enter question name';
        }
        if (!isset($data['question_text']['text']) || empty($data['question_text']['text']))   {
            $errors['question_text'] = 'Please enter question text';
        }

        return $errors;
    }

}

