<<<<<<< HEAD
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'enrol_applicationenrolment\task\send_email',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
=======
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'enrol_applicationenrolment\task\send_email',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
>>>>>>> 50f475fc90ed57a62c9dd5bf62b657f8b9598e76
);