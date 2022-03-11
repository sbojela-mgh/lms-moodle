<?php
/**
 *
 * @package     local_course_id_generator
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/local/course_id_generator/classes/course_id_generator.php');

require_login();

$course_id_prefix = optional_param('course_id_prefix', -1, PARAM_TEXT);

if($course_id_prefix == -1) {
    echo json_encode(['course_id_number' => '']);
}
else {

    $generator = new \local_course_id_generator\course_id_generator();

    $new_course_id_number = $generator->generate_new_course_id_by_prefix($course_id_prefix);

    echo json_encode(['course_id_number' => $new_course_id_number]);
}

exit;