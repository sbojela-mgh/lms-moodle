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
 * The enrol plugin applicationenrolment is defined here.
 *
 * @package     enrol_applicationenrolment
 * @author		hieu.van.han@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/calendar/lib.php');

/**
 * Class enrol_applicationenrolment_plugin.
 */
class enrol_applicationenrolment_plugin extends enrol_plugin {

    /**
     * Does this plugin allow manual enrolments?
     *
     * All plugins allowing this must implement 'enrol/applicationenrolment:enrol' capability.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool True means user with 'enrol/applicationenrolment:enrol' may enrol others freely,
     * false means nobody may add more enrolments manually.
     */
    public function allow_enrol(stdClass $instance) {
        return true;
    }

    /**
     * Does this plugin allow manual unenrolment of all users?
     *
     * All plugins allowing this must implement 'enrol/applicationenrolment:unenrol' capability.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool True means user with 'enrol/applicationenrolment:unenrol' may unenrol others freely, false means
     * nobody may touch user_enrolments.
     */
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Does this plugin allow manual changes in user_enrolments table?
     *
     * All plugins allowing this must implement 'enrol/applicationenrolment:manage' capability.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool True means it is possible to change enrol period and status in user_enrolments table.
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     *
     * All plugins allowing this must implement 'enrol/applicationenrolment:unenrol' capability.
     *
     * This is useful especially for synchronisation plugins that
     * do suspend instead of full unenrolment.
     *
     * @param stdClass $instance Course enrol instance.
     * @param stdClass $ue Record from user_enrolments table, specifies user.
     * @return bool True means user with 'enrol/applicationenrolment:unenrol' may unenrol this user,
     * false means nobody may touch this user enrolment.
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        return true;
    }

    /**
     * Use the standard interface for adding/editing the form.
     *
     * @since Moodle 3.1.
     * @return bool.
     */
    public function use_standard_editing_ui() {
        return true;
    }

    /**
     * Adds form elements to add/edit instance form.
     *
     * @since Moodle 3.1.
     * @param object $instance Enrol instance or null if does not exist yet.
     * @param MoodleQuickForm $mform.
     * @param context $context.
     * @return void
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {

		global $CFG, $DB, $COURSE, $OUTPUT;

        if (empty($instance->id)) {
            if ($DB->record_exists('enrol', array('courseid' => $COURSE->id, 'enrol' => 'applicationenrolment'))) {
                // Only one instance allowed, sorry.
                $html = '
                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            require(["core/notification"], function(notification) {
                                notification.alert("Info", "<p class=\'oneinstanceallowed\'>Only one instance allowed, sorry.</p>", "OK");
                            });

                            setTimeout(function() {
                                history.back();
                            }, 4000);
                        });
                    </script>';
                $mform->addElement('html', $html);
            }
        }

        $instanceid = optional_param('id', 0, PARAM_INT);
        if(empty($instance->id) && $instanceid == 0) {

        }
        else if (!empty($instance->id)) {
            $instanceid = $instance->id;
        }

        $options = array('optional' => true);
        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_applicationenrolment'), $options);
        $mform->setDefault('enrolstartdate', 0);

        $options = array('optional' => true);
        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolduedate', 'enrol_applicationenrolment'), $options);
        $mform->setDefault('enrolenddate', 0);

        $mform->addElement('text', 'customint1', get_string('max_submissions', 'enrol_applicationenrolment'));
        $mform->setType('customint1', PARAM_INT);
        $mform->setDefault('customint1', 0);


        $mform->addElement('hidden', 'customchar1', '');
        $mform->setDefault('customchar1', '');
        $mform->setType('customchar1', PARAM_RAW);




        $html_email_parts = '<legend class="legend_app"><a class="legend_title" href="#"><i class="icon fa fa-caret-right"></i>Email Templates</a></legend><div style="display: none;">';
        $mform->addElement('html', $html_email_parts);

        $mform->addElement('editor', 'customtext1', get_string('emailtemplate_approved', 'enrol_applicationenrolment'), ['rows' => 10], []);
        $mform->setType('customtext1', PARAM_RAW);
        $emailtemplate_approved = get_string('emailtemplate_approvedcontent', 'enrol_applicationenrolment');
        if($instanceid == 0 || empty(trim($instance->customtext1))) {
            $mform->setDefault('customtext1', ['text' => $emailtemplate_approved, 'format' => FORMAT_HTML]);
        }
        else {
            $mform->setDefault('customtext1', ['text' => $instance->customtext1, 'format' => FORMAT_HTML]);
        }

        $mform->addElement('editor', 'customtext2', get_string('emailtemplate_denied', 'enrol_applicationenrolment'), ['rows' => 10], []);
        $mform->setDefault('customtext2', 0);
        $mform->setType('customtext2', PARAM_RAW);
        $emailtemplate_denied = get_string('emailtemplate_deniedcontent', 'enrol_applicationenrolment');
        if($instanceid == 0 || empty(trim($instance->customtext2))) {
            $mform->setDefault('customtext2', ['text' => $emailtemplate_denied, 'format' => FORMAT_HTML]);
        }
        else {
            $mform->setDefault('customtext2', ['text' => $instance->customtext2, 'format' => FORMAT_HTML]);
        }

        $mform->addElement('editor', 'customtext3', get_string('emailtemplate_reminder', 'enrol_applicationenrolment'), ['rows' => 10], []);
        $mform->setDefault('customtext3', 0);
        $mform->setType('customtext3', PARAM_RAW);
        $emailtemplate_reminder = get_string('emailtemplate_remindercontent', 'enrol_applicationenrolment');
        if($instanceid == 0 || empty(trim($instance->customtext3))) {
            $mform->setDefault('customtext3', ['text' => $emailtemplate_reminder, 'format' => FORMAT_HTML]);
        }
        else {
            $mform->setDefault('customtext3', ['text' => $instance->customtext3, 'format' => FORMAT_HTML]);
        }

        $mform->addElement('editor', 'customtext4', get_string('emailtemplate_submitted', 'enrol_applicationenrolment'), ['rows' => 10], []);
        $mform->setDefault('customtext4', 0);
        $mform->setType('customtext4', PARAM_RAW);
        $emailtemplate_submitconfirm = get_string('emailtemplate_submitconfirm', 'enrol_applicationenrolment');
        if($instanceid == 0 || empty(trim($instance->customtext4))) {
            $mform->setDefault('customtext4', ['text' => $emailtemplate_submitconfirm, 'format' => FORMAT_HTML]);
        }
        else {
            $mform->setDefault('customtext4', ['text' => $instance->customtext4, 'format' => FORMAT_HTML]);
        }

        $mform->addElement('html', '</div>');



        $url_multiplechoices = new moodle_url('/enrol/applicationenrolment/addmultiplechoices.php',
                                            ['courseid' => $COURSE->id, 'instanceid' => $instanceid]);
        $url_textquestion = new moodle_url('/enrol/applicationenrolment/addtextquestion.php',
                                            ['courseid' => $COURSE->id, 'instanceid' => $instanceid]);

        $html = '
            <style type="text/css">
                #fgroup_id_buttonar { margin-top: 20px; }
                .legend_app a { color: #094478; text-decoration: none; }
                .legend_app .icon { font-size: 20px; color: #a99; margin-left: 0; }
                .legend_questions { border-bottom: 1px solid #000; }
                .legend_questions a { color: #1d2125; font-size: 15px; text-decoration: none; }
                .legend_questions a:hover { text-decoration: none; }
                .add_question_dropdown { display: inline-block; float: right; }
                .add_question_dropdown > a { background: #0b5190; color: #fff; display: inline-block; padding: 3px 10px; }
                .modal-body { background: #faf9f9; }
                .modal-footer { display: block !important; text-align: right; }
                .modal-footer input[type=button] { width: 90px; text-align: center; background: #d7d4d4; border: 0; margin-left: 10px; padding: 4px 0; }
                .modal-footer input[type=button]:first-child { background: #0b5190; color: #fff; }
                .dynamic_question_body { text-align: center; }
                .dynamic_question_body > div { text-align: left; }
                .dynamic_question_body > div.wrapper_questions { min-height: 200px; max-height: 500px; margin-top: 20px;}
                .dynamic_question_body select { min-width: 200px; height: 32px; margin-left: 20px; }
                .dynamic_question_body table td { background: #d9d6d6; border-bottom: 3px solid #fff; padding: 3px 0; }
                .dynamic_question_body table td:first-child { padding-left: 8px; padding-right: 5px; }
                .dynamic_question_body .icon { cursor: pointer; }
                .dynamic_question_body .icon:hover { color: #0b5190; }
                .modal-footer #addfrombank{width: auto; padding-left: 10px; padding-right: 10px; float: left; margin-left: 0; }
                .table_answers td { background: #d9d6d6; border-bottom: 3px solid #fff; padding: 2px 0; }
                .table_answers .quest_order { font-size: 12px; background: #b9b6b6; display: inline-block; width: 16px; text-align: center; margin-right: 6px; }
                .table_answers td:first-child, .table_answers .action_icons { white-space: nowrap; }
                .sortable-list-current-position td { background: #0b5190 !important; color: #fff !important; }
                .table_answers .action_icons { display: inline-block; float: right; }
                .table_answers .action_icons .icon { cursor: pointer; }
                .table_answers .action_icons .icon:hover { color: #0b5190; }
            </style>
            <script type="text/javascript">
                var url_multiplechoices = "' . $url_multiplechoices->out(false) . '";
                var url_textquestion = "' . $url_textquestion->out(false) . '";
                jQuery(document).ready(function($) {
                    $("input[name=submitbutton]").attr("value", "Save and Return").clone().attr("value", "Save and Stay").attr("name","saveandstay").attr("id","id_saveandstay").css("marginLeft", "5px").insertAfter($("input[name=submitbutton]"));
                    var saveandstay = GetURLParameter("saveandstay");
                    if (saveandstay == 1) {
                        require(["core/notification"], function(notification) {
                            notification.alert("Info", "Your changes were saved successfully", "Ok");
                        });
                    }
                });
            </script>
        ';
        $html_app_form = '<legend class="legend_app"><a class="app_title" href="#"><i class="icon fa fa-caret-down"></i>Application Form</a></legend><div style="margin-bottom: 20px;">';
        $html_legend = '<legend class="legend_questions"><a href="#">Questions</a>
            <div class="add_question_dropdown">
                <a href="#" tabindex="0" class=" dropdown-toggle icon-no-margin" id="dropdown-9" data-toggle="dropdown">
                    Add Questions
                </a>
                <div class="dropdown-menu dropdown-menu-right menu" style="position: absolute; transform: translate3d(-118px, 20px, 0px); top: 0px; left: 0px; will-change: transform;">
                    <a href="javascript:;" id="add_a_new_question" class="dropdown-item menu-action cm-edit-action">
                        <span class="menu-action-text">+ a new question</span>
                    </a>
                    <a href="javascript:;" class="dropdown-item add_question_from_bank menu-action cm-edit-action">
                        <span class="menu-action-text">+ from question bank</span>
                    </a>
                </div>
            </div>
        </legend>';


        $questionids = optional_param_array('new_questionids', [], PARAM_RAW);

        if (!empty($instance->id)) {
            $tmp_arr = explode(',', $instance->customchar1);
            $questionids = array_merge($tmp_arr, $questionids);
            $questionids = array_filter($questionids);
            $questionids = array_unique($questionids);
        }

        $html_answer_list = '<div class="wrapper_table"><table border="0" class="table_answers" width="100%"><tbody>';
        $html_action_icons = '<span class="action_icons">';
        $html_action_icons .= '<i class="icon fa fa-cog" title="Edit"></i>';
        $html_action_icons .= '<i class="icon fa fa-search-plus" title="Preview"></i>';
        $html_action_icons .= '<i class="icon fa fa-trash" title="Delete"></i>';
        $html_action_icons .= '</span>';
        if (!empty($questionids)) {
            $sqlin = '('. implode(',',$questionids) .')';
            $sql = "SELECT * FROM {application_question} WHERE id IN $sqlin";
            $questions = $DB->get_records_sql($sql, []);
            $quest_order = 1;
            foreach($questionids as $questionidonly) {

                $question = $questions[$questionidonly]; // To keep the order
                $question_content = json_decode($question->question_content);

                $dragdrop = $OUTPUT->render_from_template('core/drag_handle', ['movetitle' => get_string('move')]);

                $html_answer_list .= '<tr>';
                $html_answer_list .= '<td width="5%" data-questiontype="'. $question_content->question_type .'" data-questionid="'.$question->id.'">'.$dragdrop.'<span class="quest_order">'
                                  . $quest_order . '</span></td>';
                $html_answer_list .= '<td width="90%" class="quest_content"><b>';
                $html_answer_list .= $question->required == 1? '<span class="star">*</span> ' : '';
                $html_answer_list .= $question_content->question_name.'</b> - '. strip_tags($question_content->question_text->text)              . '</td>';
                $html_answer_list .= '<td width="5%">' . $html_action_icons;
                $html_answer_list .= '</td></tr>';
                $quest_order += 1;
            }
        }
        else {
            $html_answer_list .= 'No questions to be displayed';
        }
        $html_answer_list .= '</tbody></table></div></div>';

        $html .= $html_app_form;
        $html .= $html_legend;
        $html .= $html_answer_list;

        $cur_questionids = array_diff($questionids, optional_param_array('new_questionids', [], PARAM_RAW));
        $html .= '
            <script type="text/javascript">
                var url_multiplechoices = "' . $url_multiplechoices->out(false) . '";
                var url_textquestion = "' . $url_textquestion->out(false) . '";
                var current_questlist = ['.implode(',', $cur_questionids).'];
            </script>';

        $mform->addElement('html', $html);
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @since Moodle 3.1.
     * @param array $data Array of ("fieldname"=>value) of submitted data.
     * @param array $files Array of uploaded files "element_name"=>tmp_file_path.
     * @param object $instance The instance data loaded from the DB.
     * @param context $context The context of the instance we are editing.
     * @return array Array of "element_name"=>"error_description" if there are errors, empty otherwise.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        // No errors by default.
        // There is no 'UI' for this plugin as all data comes from applicationenrolment system via web hook.
        return array();
    }

    /**
     * Return whether or not, given the current state, it is possible to add a new instance
     * of this enrolment plugin to the course.
     *
     * @param int $courseid.
     * @return bool.
     */
    public function can_add_instance($courseid) {
        return true;
    }

    /**
     * Wrapper for the parent enrol user method.
     *
     * @param stdClass $user
     * @param stdClass $course
     * @throws coding_exception
     * @throws dml_exception
     */
    public function enrol($instance, $user) {
        global $DB;

        $sendoptions = enrol_send_welcome_email_options();

        // Does user have a current active enrolment.
        $conditions = ['status' => ENROL_USER_ACTIVE, 'enrolid' => $instance->id, 'userid' => $user->id];
        $currentlyactive = $DB->record_exists('user_enrolments', $conditions);

        // Always update enrolment status, times and group.
        parent::enrol_user($instance,  $user->id, 5, time(), 0, ENROL_USER_ACTIVE);
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param stdClass $course
     * @return int id of new instance, null if can not be created
     */
    public function add_default_instance($course) {
        $expirynotify = $this->get_config('expirynotify', 0);
        if ($expirynotify == 2) {
            $expirynotify = 1;
            $notifyall = 1;
        } else {
            $notifyall = 0;
        }
        $fields = array(
            'status'          => $this->get_config('status'),
            'roleid'          => $this->get_config('roleid', 5), // 5 = Student.
            'enrolperiod'     => $this->get_config('enrolperiod', 0),
            'expirynotify'    => $expirynotify,
            'notifyall'       => $notifyall,
            'expirythreshold' => $this->get_config('expirythreshold', 86400),
        );
        return $this->add_instance($course, $fields);
    }

    /**
     * Add new instance of enrol plugin.
     * @param stdClass $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = null) {
        global $DB, $COURSE;

        $customtext1 = $fields['customtext1'];
        $fields['customtext1'] = $customtext1['text'];

        $customtext2 = $fields['customtext2'];
        $fields['customtext2'] = $customtext2['text'];

        $customtext3 = $fields['customtext3'];
        $fields['customtext3'] = $customtext3['text'];

        if ($DB->record_exists('enrol', array('courseid' => $course->id, 'enrol' => 'applicationenrolment'))) {
            // Only one instance allowed, sorry.
            return null;
        }

        $instanceid = parent::add_instance($course, $fields);

        $instance = $DB->get_record('enrol', ['id' => $instanceid]);
        $this->populate_event($instance, 'applicationenrolment');

        if (!empty($fields['submitbutton'])) {
            // Save and Return
            return $instanceid;
        }
        else {
            // Save and Stay
            $link = new moodle_url("/enrol/editinstance.php",
                                    ['id' => $instance->id,
                                    'courseid' => $instance->courseid,
                                    'type' => 'applicationenrolment',
                                    'saveandstay' => 1
                                    ]);
            redirect($link->out(false));
        }
    }

    /**
     * Get a applicationenrolment instance enrolment record based on id
     *
     * @param $id
     * @param int $strictness
     * @return mixed
     * @throws dml_exception
     */
    public static function get_instance_record($courseid, $strictness = IGNORE_MISSING) {
        global $DB;
        $conditions = [
            'courseid' => $courseid,
            'enrol' => 'applicationenrolment'
        ];
        return $DB->get_record('enrol', $conditions, '*', $strictness);
    }

    /**
     * Check if applicationenrolment instance exists based on passed in conditions.
     *
     * @param $conditions
     * @return bool
     * @throws dml_exception
     */
    public static function instance_exists($conditions) {
        global $DB;
        $conditions = array_merge($conditions, ['enrol' => 'applicationenrolment']);
        return $DB->record_exists('enrol', $conditions);
    }

    /**
     * Update instance of enrol plugin.
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        global $DB;

        // Delete all other instances, leaving only one.
        if ($instances = $DB->get_records('enrol', array('courseid' => $instance->courseid, 'enrol' => 'applicationenrolment'), 'id ASC')) {
            foreach ($instances as $anotherinstance) {
                if ($anotherinstance->id != $instance->id) {
                    $this->delete_instance($anotherinstance);
                }
            }
        }

		$customtext1 = $data->customtext1;
		$data->customtext1 = $customtext1['text'];

        $customtext2 = $data->customtext2;
        $data->customtext2 = $customtext2['text'];

        $customtext3 = $data->customtext3;
        $data->customtext3 = $customtext3['text'];

        $this->populate_event($instance, 'applicationenrolment');

        if(!empty($data->submitbutton)) {
            // Save and Return
            return parent::update_instance($instance, $data);
        }
        else {
            // Save and Stay
            $update = parent::update_instance($instance, $data);

            $link = new moodle_url("/enrol/editinstance.php",
                                    ['id' => $data->id,
                                    'courseid' => $data->courseid,
                                    'type' => 'applicationenrolment',
                                    'saveandstay' => 1
                                    ]);
            redirect($link->out(false));
        }

    }

    /**
     * Returns plugin config value
     * @param  string $name
     * @param  string $default value if config does not exist yet
     * @return string value or default
     */
    public function get_config($name, $default = null) {
        $this->load_config();
        return isset($this->config->$name) ? $this->config->$name : $default;
    }

    /**
     * Sets plugin config value
     * @param  string $name name of config
     * @param  string $value string config value, null means delete
     * @return string value
     */
    public function set_config($name, $value) {
        $pluginname = $this->get_name();
        $this->load_config();
        if ($value === null) {
            unset($this->config->$name);
        } else {
            $this->config->$name = $value;
        }
        set_config($name, $value, "enrol_$pluginname");
    }


    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/applicationenrolment:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/applicationenrolment:config', $context);
    }


    /**
     * Create/Update startdate, duedate in the event table
     *
     * @param $coursework
     */
     function populate_event($instance, $eventtype) {
        global $DB, $COURSE;

        if ($instance->enrolenddate != 0) {

            $event = "";
            $eventid = $DB->get_record('event', [
                                                'instance'      =>  $instance->id,
                                                'eventtype'     =>  $eventtype
                                                ]);

            if (!empty($eventid)) {
                $event = calendar_event::load($eventid->id);
            }

            $data = new stdClass();
            $data->type = CALENDAR_EVENT_TYPE_ACTION;

            $data->courseid = $instance->courseid;
            $data->name = $COURSE->fullname . ': Application Due Today';
            $data->instance = $instance->id;
            $data->groupid = 0;
            $data->userid = 0;
            $data->eventtype = $eventtype;
            $data->timestart = $instance->enrolenddate;
            $data->timeduration = 0;
            $data->visible = $instance->status == 0 ? true : false;

            if ($event) {
                $event->update($data); //update if event exists
            } else {
                calendar_event::create($data); //create new event as it doesn't exist
            }
        }

    }

    /**
     * Returns edit icons for the page with list of instances.
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        $context = context_course::instance($instance->courseid);

        $icons = array();
        if (has_capability('enrol/applicationenrolment:manage', $context)) {
            $managelink = new moodle_url("/enrol/applicationenrolment/displayapplicantlist.php",
                                            array('enrolid' => $instance->id, 'courseid' => $instance->courseid));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('t/user', 'Applicant list', 'core', array('class'=>'iconsmall')));
        }
        $parenticons = parent::get_action_icons($instance);
        $icons = array_merge($icons, $parenticons);

        return $icons;
    }
}

function send_reminder_emails () {

    global $CFG, $DB, $PAGE;

    $PAGE->set_context(\context_system::instance());

    $sql = "SELECT  u.id as studentid,
                    u.firstname,
                    u.lastname,
                    u.username,
                    u.email,
                    c.id as courseid,
                    c.fullname as coursefullname,
                    e.id as enrolid,
                    e.enrolenddate,
                    e.customtext3 as reminder_email
            FROM {user} u JOIN {student_apply_course} sac ON u.id = sac.studentid
            JOIN {enrol} e ON e.courseid = sac.courseid
            JOIN {course} c ON c.id = e.courseid
            WHERE e.enrol = 'applicationenrolment'
                AND sac.application_button_state = 'Started'
                AND e.enrolenddate > now()
                AND e.enrolenddate - 259200 < now()";   // => 259200 = 60*60*24*3 (3 days)
    $records = $DB->get_records_sql($sql, []);

    foreach($records as $record) {

        $template_reminder_email = $record->reminder_email;
        if(empty(trim($template_reminder_email))) {
            $template_reminder_email = get_string('emailtemplate_remindercontent', 'enrol_applicationenrolment');
        }

        $url_course = new moodle_url('/course/view.php', ['id' => $record->courseid]);
        $url_course = $url_course->out(false);
        $url_course = '<a href="'. $url_course .'">' . $record->coursefullname . '</a>';

        $duedate = userdate($record->enrolenddate, '%B %d, %Y %I:%M %p');

        $template_reminder_email = str_replace('[Student First Name]', $record->firstname, $template_reminder_email);
        $template_reminder_email = str_replace('[Course Fullname]', $record->coursefullname, $template_reminder_email);
        $template_reminder_email = str_replace('[Course URL]', $url_course, $template_reminder_email);
        $template_reminder_email = str_replace('[Due Date]', $duedate, $template_reminder_email);

        // echo $template_reminder_email;
        // $email_user = $DB->get_record('user', ['id' => $record->studentid]);

        $email_user = new stdClass;
        $email_user->email       = $record->email;
        $email_user->firstname   = $record->firstname;
        $email_user->lastname    = $record->lastname;
        $email_user->id          = $record->studentid;
        $email_user->username    = $record->username;
        $email_user->middlename   = '';
        $email_user->alternatename     = '';
        $email_user->firstnamephonetic = '';
        $email_user->lastnamephonetic  = '';
        $email_user->maildisplay = true;
        $email_user->mailformat  = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.

        $from_user = new stdClass;
        $from_user->id          = -99;
        $from_user->email       = 'DCRCCRE@partners.org';
        $from_user->firstname   = 'DCRCCRE@partners.org';
        $from_user->lastname    = '';
        $from_user->middlename  = '';
        $from_user->alternatename     = '';
        $from_user->firstnamephonetic = '';
        $from_user->lastnamephonetic  = '';

        $subject = 'Application Incomplete - ' . $record->coursefullname;

        $result = email_to_user($email_user, $from_user, $subject, $template_reminder_email);

        $log_info = ['status'   => $result,
                    'recipient' => $email_user,
                    'sender'    => 'DCRCCRE@partners.org',
                    'subject'   => $subject,
                    'content'   => $template_reminder_email];

        $event = \enrol_applicationenrolment\event\email_action::create(
                                                            array('other' => json_encode($log_info),
                                                            'context' => \context_system::instance()));
        $event->trigger();
    }

}

function send_email_and_log($studentid, $courseid, $subject, $emailtemplate) {

    global $CFG, $DB, $PAGE;

    $student = $DB->get_record('user', ['id' => $studentid]);

    $course =  $DB->get_record('course', ['id' => $courseid]);

    $url_course = new moodle_url('/course/view.php', ['id' => $course->id]);
    $url_course = $url_course->out(false);
    $url_course = '<a href="'. $url_course .'">' . $course->fullname . '</a>';

    $emailtemplate = str_ireplace('[Student First Name]', $student->firstname, $emailtemplate);
    $emailtemplate = str_ireplace('[Course Fullname]', $course->fullname, $emailtemplate);
    $emailtemplate = str_ireplace('[Hyperlink Course URL]', $url_course, $emailtemplate);

    $email_user = new stdClass;
    $email_user->email       = $student->email;
    $email_user->firstname   = $student->firstname;
    $email_user->lastname    = $student->lastname;
    $email_user->id          = $student->id;
    $email_user->username    = $student->username;
    $email_user->middlename   = '';
    $email_user->alternatename     = '';
    $email_user->firstnamephonetic = '';
    $email_user->lastnamephonetic  = '';
    $email_user->maildisplay = true;
    $email_user->mailformat  = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.

    $from_user = new stdClass;
    $from_user->id          = -99;
    $from_user->email       = 'DCRCCRE@partners.org';
    $from_user->firstname   = 'DCRCCRE@partners.org';
    $from_user->lastname    = '';
    $from_user->middlename  = '';
    $from_user->alternatename     = '';
    $from_user->firstnamephonetic = '';
    $from_user->lastnamephonetic  = '';

    $subject = $subject . $course->fullname;

    $result = email_to_user($email_user, $from_user, $subject, $emailtemplate);

    $log_info = ['status'   => $result,
                'recipient' => $email_user,
                'sender'    => 'DCRCCRE@partners.org',
                'subject'   => $subject,
                'content'   => $emailtemplate];

    $event = \enrol_applicationenrolment\event\email_action::create(
                                                        array('other' => json_encode($log_info),
                                                        'context' => \context_system::instance()));
    $event->trigger();
}

