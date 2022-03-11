<?php
/**
 *
 * @package   	local_course_id_generator
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_id_generator;

class course_id_generator {
	
	public function __construct() {

    }
	
	public function generate_new_course_id($source_course_id) {
		
        global $DB;
		
		$source_course = $DB->get_record('course', ['id' => $source_course_id]);
		
		$idnumber_parts = explode('-', $source_course->idnumber); // idnumber format: LE0001-001
		
		if (count($idnumber_parts) < 2) {
			// Wrong format, just return empty
			return '';
		}
		
		// Collect all instaces of this course
		$sql = "SELECT idnumber FROM {course} WHERE idnumber LIKE '" . $idnumber_parts[0] . "%'";

		$course_idnumbers = $DB->get_records_sql($sql, []);
		
		$idnumbers = array_column($course_idnumbers, 'idnumber');

		if(empty($idnumbers)) {
			return '';
		}

		for($i = 0; $i < count($idnumbers); $i++) {
			$idnumbers[$i] = str_replace($idnumber_parts[0] . '-', '', $idnumbers[$i]);
			$idnumbers[$i] = intval($idnumbers[$i]);
		}
		
		asort($idnumbers);
		
		$greatest_id = array_pop($idnumbers);
		
		$greatest_id += 1;
		
		// Zerofill following the format. Ex: 2 => 002
		$new_instance_number = str_pad($greatest_id, 3, "0", STR_PAD_LEFT);
		
		$new_course_id_number = $idnumber_parts[0] . '-' . $new_instance_number;
		
        return $new_course_id_number;

    }
	
	public function generate_new_course_short_name($source_course_id) {

        global $DB;
		
		$source_course = $DB->get_record('course', ['id' => $source_course_id]);
		
		$shortname_parts = explode('(', $source_course->shortname); // shortname format: Math (001)
		
		if (count($shortname_parts) < 2) {
			// Wrong format, just return empty
			return '';
		}
		
		// Collect all instaces of this course
		$sql = "SELECT shortname FROM {course} WHERE shortname LIKE '" . $shortname_parts[0] . "%'";

		$course_shortnames = $DB->get_records_sql($sql, []);
		
		$shortnames = array_column($course_shortnames, 'shortname');

		if(empty($shortnames)) {
			return '';
		}

		for($i = 0; $i < count($shortnames); $i++) {
			$shortnames[$i] = str_replace($shortname_parts[0] . '(', '', $shortnames[$i]);
			$shortnames[$i] = str_replace(')', '', $shortnames[$i]);
			$shortnames[$i] = intval($shortnames[$i]);
		}
		
		asort($shortnames);
		
		$greatest_instance = array_pop($shortnames);
		
		$greatest_instance += 1;
		
		// Zerofill following the format. Ex: 2 => 002
		$new_instance_number = str_pad($greatest_instance, 3, "0", STR_PAD_LEFT);
		
		$new_course_shortname = $shortname_parts[0] . '(' . $new_instance_number . ')';
		
        return $new_course_shortname;

    }

	public function generate_new_course_id_by_prefix($prefix) {

        global $DB;

		$sql = "SELECT idnumber FROM {course} WHERE idnumber LIKE '" . $prefix . "%'";

		$course_idnumbers = $DB->get_records_sql($sql, []);

		$idnumbers = array_column($course_idnumbers, 'idnumber');

		if(empty($idnumbers)) {
			return '';
		}

		for($i = 0; $i < count($idnumbers); $i++) {
			$idnumber_parts = explode('-', $idnumbers[$i]); // idnumber format: LE0001-001
			$idnumbers[$i] = str_replace($prefix, '', $idnumber_parts[0]); // remove LE from first part 'LE0001'
			$idnumbers[$i] = intval($idnumbers[$i]) > 9999 ? 0 : $idnumbers[$i]; // don't count any wrong format more than 4 digits
		}

		asort($idnumbers);

		$greatest_id = array_pop($idnumbers);

		$greatest_id += 1;

		// Zerofill following the format. Ex: 2 => 0002
		$new_instance_number = str_pad($greatest_id, 4, "0", STR_PAD_LEFT);

		$new_course_id_number = $prefix . $new_instance_number . '-001';

        return $new_course_id_number;
	}
	
}