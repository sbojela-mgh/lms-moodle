<?php
/**
 *
 * @package   	local_course_id_generator
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_course_id_generator_uninstall() {
	
    global $DB;
	
	$setting = $DB->get_record('config', array('name' => 'additionalhtmlhead'));
	if (!empty($setting)) {

		$path_to_jquery = new moodle_url('/local/course_id_generator/js/jquery-3.3.1.min.js', []);
		$path_to_script = new moodle_url('/local/course_id_generator/js/script.js', []);

		$script = '<script src="' . $path_to_jquery->out(false) . '"></script>';
		$script .= '<script src="' . $path_to_script->out(false) . '"></script>';

		$setting->value = str_replace($script, '', $setting->value);
		set_config('additionalhtmlhead', $setting->value);
	}

}

