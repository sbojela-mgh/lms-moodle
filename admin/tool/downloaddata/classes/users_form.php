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
 * File containing the index form.
 *
 * @package    tool_downloaddata
 * @copyright  2015 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/../locallib.php');

/**
 * Download users form.
 *
 * @package    tool_downloaddata
 * @copyright  2015 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_downloaddata_users_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;
        $selectedfields = $this->_customdata['selectedfields'];
        $selectedroles = $this->_customdata['selectedroles'];
		$overrides = $this->_customdata['overrides'];
        $fields = array_merge(tool_downloaddata_processor::get_valid_user_fields(),
                              tool_downloaddata_processor::get_profile_fields());
        $roles = tool_downloaddata_processor::get_all_valid_roles();

        if (empty($selectedfields)) {
            $selectedfields = array(get_string('noselectedfields', 'tool_downloaddata'));
        }
        if (empty($selectedroles)) {
            $selectedroles = array(get_string('noselectedroles', 'tool_downloaddata'));
        }

        $mform->addElement('header', 'generalhdr', get_string('downloadusersbyrole', 'tool_downloaddata'));

        $formatchoices = array(
            tool_downloaddata_processor::FORMAT_CSV => get_string('formatcsv', 'tool_downloaddata'),
            tool_downloaddata_processor::FORMAT_XLS => get_string('formatxls', 'tool_downloaddata')
        );
        $mform->addElement('select', 'format',
                           get_string('format', 'tool_downloaddata'), $formatchoices);
        $mform->setDefault('format', $this->_customdata['format']);

        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'tool_downloaddata'), $encodings);
        $mform->setDefault('encoding', $this->_customdata['encoding']);
        $mform->disabledIf('encoding', 'format', 'noteq', tool_downloaddata_processor::FORMAT_CSV);

        $delimiters = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name',
                           get_string('csvdelimiter', 'tool_downloaddata'), $delimiters);
        $mform->setDefault('delimiter_name', $this->_customdata['delimiter_name']);
        $mform->disabledIf('delimiter_name', 'format', 'noteq', tool_downloaddata_processor::FORMAT_CSV);

        $useoverrides = array('true' => 'Yes', 'false' => 'No');
        $mform->addElement('select', 'useoverrides',
                           get_string('useoverrides', 'tool_downloaddata'), $useoverrides);
        $mform->setDefault('useoverrides', $this->_customdata['useoverrides']);
        $mform->addHelpButton('useoverrides', 'useoverrides', 'tool_downloaddata');

        // Creating the role selection elements.
        $mform->addElement('header', 'roleshdr', get_string('roles', 'tool_downloaddata'));
        $mform->setExpanded('roleshdr', true);
        $objs = array();
        $objs[0] = $mform->createElement('select', 'availableroles', get_string('available', 'tool_downloaddata'),
                                         $roles, 'size="7"');
        $objs[0]->setMultiple(true);
        $objs[1] = $mform->createElement('select', 'selectedroles', get_string('selected', 'tool_downloaddata'),
                                         $selectedroles, 'size="7"');
        $objs[1]->setMultiple(true);
        $group = $mform->addElement('group', 'rolesgroup', get_string('roles', 'tool_downloaddata'), $objs, '  ', false);
        $mform->addHelpButton('rolesgroup', 'roles', 'tool_downloaddata');
        // Creating the buttons for role the selection elements.
        $objs = array();
        $objs[] = $mform->createElement('submit', 'addroleselection', get_string('addroleselection', 'tool_downloaddata'));
        $objs[] = $mform->createElement('submit', 'removeroleselection', get_string('removeroleselection', 'tool_downloaddata'));
        $objs[] = $mform->createElement('submit', 'addallroles', get_string('addallroles', 'tool_downloaddata'));
        $objs[] = $mform->createElement('submit', 'removeallroles', get_string('removeallroles', 'tool_downloaddata'));
        $group = $mform->addElement('group', 'rolesbuttonsgroup', '', $objs, array(' ', '<br/>'), false);

        // Creating the field selection elements.
        $mform->addElement('header', 'fieldshdr', get_string('fields', 'tool_downloaddata'));
        $mform->setExpanded('fieldshdr', true);
        $objs = array();
        $objs[0] = $mform->createElement('select', 'availablefields', get_string('available', 'tool_downloaddata'),
                                         $fields, 'size="10"');
        $objs[0]->setMultiple(true);
        $objs[1] = $mform->createElement('select', 'selectedfields', get_string('selected', 'tool_downloaddata'),
                                         $selectedfields, 'size="10"');
        $objs[1]->setMultiple(true);
        $group = $mform->addElement('group', 'fieldsgroup', get_string('fields', 'tool_downloaddata'), $objs, '  ', false);
        $mform->addHelpButton('fieldsgroup', 'fields', 'tool_downloaddata');
        // Creating the buttons for the field selection elements.
        $objs = array();
        $objs[] = $mform->createElement('submit', 'addfieldselection', get_string('addfieldselection', 'tool_downloaddata'));
        $objs[] = $mform->createElement('submit', 'removefieldselection', get_string('removefieldselection', 'tool_downloaddata'));
        $objs[] = $mform->createElement('submit', 'addallfields', get_string('addallfields', 'tool_downloaddata'));
        $objs[] = $mform->createElement('submit', 'removeallfields', get_string('removeallfields', 'tool_downloaddata'));
        $group = $mform->addElement('group', 'fieldsbuttonsgroup', '', $objs, array(' ', '<br/>'), false);

        $mform->addElement('header', 'overrideshdr', get_string('overrides', 'tool_downloaddata'));

        $mform->addElement('textarea', 'overrides', get_string('overrides', 'tool_downloaddata'),
                           'wrap="virtual" rows="3" cols="45"');
        $mform->setType('overrides', PARAM_RAW);
		$mform->setDefault('overrides', $overrides);
        $mform->addHelpButton('overrides', 'overrides', 'tool_downloaddata');
		if (empty($overrides)) {
			$mform->setExpanded('overrideshdr', false);
		} else {
			$mform->setExpanded('overrideshdr', true);
		}

        $this->add_action_buttons(false, get_string('download', 'tool_downloaddata'));

        $template = '<label class="qflabel" style="vertical-align:top">{label}</label> {element}';
        $mform->defaultRenderer()->setGroupElementTemplate($template, 'fieldsgroup');
        $mform->defaultRenderer()->setGroupElementTemplate($template, 'rolesgroup');
    }

    /**
     * Returns a list of default values for the form elements.
     *
     * @return string[] array of form elements and their default values.
     */
    public static function get_default_form_values() {
        $ret = array();
        $ret['selectedfields'] = tool_downloaddata_config::$userfields;
        $ret['selectedroles'] = array();
        $ret['format'] = tool_downloaddata_processor::FORMAT_CSV;
        $ret['encoding'] = 'UTF-8';
        $ret['delimiter_name'] = 'comma';
        $ret['useoverrides'] = 'false';
        $ret['overrides'] = null;

        return $ret;
    }
}
