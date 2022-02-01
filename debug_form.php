<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * plagiarism_form.php - Will be used to setup the admin section of the plugin.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/formslib.php');
require_once('./lib.php');
class debug_form extends moodleform {

    // Define the form!
    public function definition () {
        global $CFG, $DB;

        $mform =& $this->_form;
        // Build form.
        $mform->addElement('html');

        $checkconnection = $CFG->wwwroot . '/plagiarism/tomagrade/checkConnection.php';
        $mform->addElement('button', "onclick='asdfasfd'", "Check Connection",
         array("onclick" => "window.open('$checkconnection')"));

        $checklogs = $CFG->wwwroot . '/plagiarism/tomagrade/showLogs.php';
        $mform->addElement('button', "onclick='asdfasfd'", "Show logs",
         array("onclick" => "window.open('$checklogs')"));

        $clearlogs = $CFG->wwwroot . '/plagiarism/tomagrade/clearLogs.php';
        $mform->addElement('button', "onclick='asdfasfd'", "Clear logs",
         array("onclick" => "window.open('$clearlogs')"));

        $checkassignindb = $CFG->wwwroot . '/plagiarism/tomagrade/checkassigninDB.php';

        $mform->addElement('text', 'assignment_id',
           "Assignment id", array('size' => '30', 'style' => 'height: 33px'));
        $mform->setType('assignment_id', PARAM_TEXT);

        $this->add_action_buttons($cancel = false, $submitlabel = "Check DB");

    }

}
