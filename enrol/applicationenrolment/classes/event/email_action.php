<?php
/**
 * @package     enrol_applicationenrolment
 * @author      hieu.van.han@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace enrol_applicationenrolment\event;

class email_action extends \core\event\base {


    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public static function get_name() {
        return 'Application Enrollment Email Action';
    }


    /**
     * @return string
     */
    public function get_description() {
        return "An email was sent as: {$this->other}";
    }

    /**
     * @return array
     */
    public function get_legacy_logdata() {
        return array(SITEID,
                     'applicationenrolment',
                     'mail',
                     '',
                     "SENT email as: {$this->other}");

    }
}