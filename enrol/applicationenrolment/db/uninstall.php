<?php
/**
 *
 * @package   	enrol_applicationenrolment
 * @Author		Hieu Han (hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_applicationenrolment_uninstall() {
	
    global $DB;
	
	$setting = $DB->get_record('config', array('name' => 'additionalhtmlhead'));
	if (!empty($setting)) {

		$path_to_jquery = new moodle_url('/enrol/applicationenrolment/js/jquery-3.3.1.min.js', []);
		$path_to_script = new moodle_url('/enrol/applicationenrolment/js/script.js', []);

		$script = '<script src="' . $path_to_jquery->out(false) . '"></script>';
		$script .= '<script src="' . $path_to_script->out(false) . '"></script>';

		$setting->value = str_replace($script, '', $setting->value);
		set_config('additionalhtmlhead', $setting->value);
	}

}

