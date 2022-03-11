<?php
/**
 *
 * @package     local_course_change_name
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    global $ADMIN, $CFG;

    $ADMIN->add('localplugins', new admin_externalpage ('local_course_change_name',
            get_string('pluginname', 'local_course_change_name'),
            new moodle_url('/local/course_change_name/index.php'),
            array('local/course_change_name:view')
        )
    );

}