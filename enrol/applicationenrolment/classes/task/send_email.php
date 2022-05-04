<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_applicationenrolment\task;

class send_email extends \core\task\scheduled_task {

    public function get_name() {
        // Shown on admin screens
        return 'Application Enrollment - Auto Reminder Email Scheduler';
    }

    public function execute() {

        global $CFG;

        require_once($CFG->dirroot . '/enrol/applicationenrolment/lib.php');

        send_reminder_emails(); //function to execute
    }
}
?>