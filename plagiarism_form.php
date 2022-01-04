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
class plagiarism_setup_form extends moodleform {

    // Define the form!
    public function definition () {
        global $CFG, $DB;

        $tomaplagopts = array( plagiarism_plugin_tomagrade::RUN_NO => "No",
        plagiarism_plugin_tomagrade::RUN_IMMEDIATLY => "Start immediately",
        plagiarism_plugin_tomagrade::RUN_MANUAL => "Start it manual");

        $tomagradeaclopts = array(
            plagiarism_plugin_tomagrade::ALL_SITE => "All Site",
            plagiarism_plugin_tomagrade::ACL => "Use ACL"
            );

        $showstudentsopt = array(
        plagiarism_plugin_tomagrade::SHOWSTUDENTS_NEVER => "Never",
        plagiarism_plugin_tomagrade::SHOWSTUDENTS_ALWAYS => "Always",
        plagiarism_plugin_tomagrade::SHOWSTUDENTS_ACTCLOSED => "After due date");

        $identifierarray = array(
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_EMAIL => "Email address",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_ID => "ID number",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_USERNAME => "User name",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_ORBITID => "Orbit id"
        );

        $identifierarrayteacher = array(
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_EMAIL => "Email address",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_ID => "ID number",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_HUJIID => "HUJI id",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_USERNAME => "User name"
        );

        $idmatchontg = array(
        plagiarism_plugin_tomagrade::INACTIVE => "Inactive",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_COURSE_ID => "CourseID",
        plagiarism_plugin_tomagrade::IDENTIFIER_BY_EXAM_ID => "ExamID"
        );

        $allowonlyidmatchontg = array(
        plagiarism_plugin_tomagrade::LO => "No",
        plagiarism_plugin_tomagrade::KEN => "Yes"
        );

        $displaystudentnameontg = array(
        plagiarism_plugin_tomagrade::KEN => "Yes",
        plagiarism_plugin_tomagrade::LO => "No"
        );

        $createusersoptions = array(
        plagiarism_plugin_tomagrade::LO => "Inactive",
        plagiarism_plugin_tomagrade::KEN => "Active"
        );

        $keepblindmarkingoptions = array(
        plagiarism_plugin_tomagrade::KEN => "Yes",
        plagiarism_plugin_tomagrade::LO => "No"
        );

        $mform =& $this->_form;
        $choices = array('No', 'Yes');

        $resultroles = $DB->get_records_sql('SELECT id,shortname FROM {role}');
        $roles = array();
        $stringroles = "";
        foreach ($resultroles as $role) {
            $roles[$role->id] = $role->shortname;
        }

        // Build form.
        $mform->addElement('html');
        $mform->addElement('checkbox', 'enabled', "Enable TomaGrade");

        $mform->addElement('text', 'tomagrade_server',
         get_string('tomagradeserver', 'plagiarism_tomagrade'), array('size' => '40', 'style' => 'height: 33px'));
        $mform->addRule('tomagrade_server', null, 'required', null, 'client');
        $mform->setType('tomagrade_server', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_server', 'tomagradeserver', 'plagiarism_tomagrade');

        $mform->addElement('text', 'tomagrade_username',
         get_string('tomagradeusername', 'plagiarism_tomagrade'), array('style' => 'height: 33px'));
        $mform->addRule('tomagrade_username', null, 'required', null, 'client');
        $mform->setType('tomagrade_username', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_username', 'tomagradeusername', 'plagiarism_tomagrade');

        $mform->addElement('password', 'tomagrade_password',
         get_string('tomagradepassword', 'plagiarism_tomagrade'), array('style' => 'height: 33px'));
        $mform->addRule('tomagrade_password', null, 'required', null, 'client');
        $mform->setType('tomagrade_password', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_password', 'tomagradepassword', 'plagiarism_tomagrade');

        $mform->addElement('select', 'tomagrade_DefaultUseTomax',
         get_string('tomagrade_DefaultUseTomax', 'plagiarism_tomagrade'), $tomaplagopts);
        $mform->addRule('tomagrade_DefaultUseTomax', null, 'required', null, 'client');
        $mform->setType('tomagrade_DefaultUseTomax', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_DefaultUseTomax', 'tomagrade_DefaultUseTomax', 'plagiarism_tomagrade');

        $mform->addElement('select', 'tomagrade_DefaultIdentifier',
         get_string('tomagrade_DefaultIdentifier', 'plagiarism_tomagrade'), $identifierarray);
        $mform->addRule('tomagrade_DefaultIdentifier', null, 'required', null, 'client');
        $mform->setType('tomagrade_DefaultIdentifier', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_DefaultIdentifier', 'tomagrade_DefaultIdentifier', 'plagiarism_tomagrade');

        $mform->addElement('text', 'tomagrade_zeroComplete',
         get_string('tomagrade_zeroComplete', 'plagiarism_tomagrade'), array('style' => 'height:33px'));
        $mform->setType('tomagrade_zeroComplete', PARAM_TEXT);

        $mform->addElement('select', 'tomagrade_DefaultIdentifier_TEACHER',
         get_string('tomagrade_DefaultIdentifier_TEACHER', 'plagiarism_tomagrade'), $identifierarrayteacher);
        $mform->addRule('tomagrade_DefaultIdentifier_TEACHER', null, 'required', null, 'client');
        $mform->setType('tomagrade_DefaultIdentifier_TEACHER', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_DefaultIdentifier_TEACHER', 'tomagrade_DefaultIdentifier_TEACHER', 'plagiarism_tomagrade');

        $mform->addElement('text', 'tomagrade_zeroCompleteTeacher',
         get_string('tomagrade_zeroCompleteTeacher', 'plagiarism_tomagrade'), array('style' => 'height:33px'));
        $mform->setType('tomagrade_zeroCompleteTeacher', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_zeroCompleteTeacher', 'tomagrade_zeroCompleteTeacher', 'plagiarism_tomagrade');

        $mform->addElement('text', 'tomagrade_MatchingDue',
         get_string('tomagrade_MatchingDue', 'plagiarism_tomagrade'), array('style' => 'height:33px'));
        $mform->setType('tomagrade_MatchingDue', PARAM_TEXT);

        $mform->addElement('select', 'tomagrade_AllowOnlyIdMatchOnTG',
         get_string('tomagrade_AllowOnlyIdMatchOnTG', 'plagiarism_tomagrade'), $allowonlyidmatchontg);
        $mform->addRule('tomagrade_AllowOnlyIdMatchOnTG', null, 'required', null, 'client');
        $mform->setType('tomagrade_AllowOnlyIdMatchOnTG', PARAM_TEXT);

        $mform->addElement('select', 'tomagrade_DisplayStudentNameOnTG',
         get_string('tomagarde_DisplayStudentNameOnTG', 'plagiarism_tomagrade'), $displaystudentnameontg);
        $mform->addRule('tomagrade_DisplayStudentNameOnTG', null, 'required', null, 'client');
        $mform->setType('tomagrade_DisplayStudentNameOnTG', PARAM_TEXT);

        $mform->addElement('select', 'tomagrade_IDMatchOnTomagrade',
         get_string('tomagrade_IDMatchOnTomagrade', 'plagiarism_tomagrade'), $idmatchontg);
        $mform->addRule('tomagrade_IDMatchOnTomagrade', null, 'required', null, 'client');
        $mform->setType('tomagrade_IDMatchOnTomagrade', PARAM_TEXT);

        $mform->addElement('text', 'tomagrade_DaysDisplayBeforeExamDate',
         get_string('tomagrade_DaysDisplayBeforeExamDate', 'plagiarism_tomagrade'));
        $mform->setType('tomagrade_DaysDisplayBeforeExamDate', PARAM_TEXT);

        $mform->addElement('text', 'tomagrade_DaysDisplayAfterExamDate',
         get_string('tomagrade_DaysDisplayAfterExamDate', 'plagiarism_tomagrade'));
        $mform->setType('tomagrade_DaysDisplayAfterExamDate', PARAM_TEXT);

        $mform->addElement('text', 'tomagrade_FieldNameForCourseFiltering',
        get_string('tomagrade_FieldNameForCourseFiltering', 'plagiarism_tomagrade'));
        $mform->setType('tomagrade_FieldNameForCourseFiltering', PARAM_TEXT);

        $mform->addElement('text', 'tomagrade_FieldValueForCourseFiltering',
        get_string('tomagrade_FieldValueForCourseFiltering', 'plagiarism_tomagrade'));
        $mform->setType('tomagrade_FieldValueForCourseFiltering', PARAM_TEXT);

        $mform->addElement('static', 'tomagrade_userRolesToDisplayRelatedAssign1',
         get_string('tomagrade_userRolesToDisplayRelatedAssign', 'plagiarism_tomagrade'), null);

        foreach ($roles as $id => $name) {
            $mform->addElement('checkbox', "role_".$id, $name, null, array('class' => 'checkboxgroup1'));
        }

        $mform->addHelpButton('tomagrade_userRolesToDisplayRelatedAssign1',
         'tomagrade_userRolesToDisplayRelatedAssign', 'plagiarism_tomagrade');
        echo "<style>
.checkboxgroup1 { margin-top:0 !important;  margin-bottom:0 !important; }
</style>";

        $mform->addElement('static', 'tomagrade_userRolesPermissionGradedExam1',
         get_string('tomagrade_userRolesPermissionGradedExam', 'plagiarism_tomagrade'), null);

        foreach ($roles as $id => $name) {
            $mform->addElement('checkbox', "rolePermissionGradedExam_".$id, $name, null, array('class' => 'checkboxgroup1'));
        }

        $mform->addElement('select', 'tomagrade_createUsers',
         get_string('tomagrade_createUsers', 'plagiarism_tomagrade'), $createusersoptions);

        $mform->setType('tomagrade_createUsers', PARAM_TEXT);

        $mform->addElement('select', 'tomagrade_keepBlindMarking',
         get_string('tomagrade_keepBlindMarking', 'plagiarism_tomagrade'), $keepblindmarkingoptions);

        $mform->setType('tomagrade_keepBlindMarking', PARAM_TEXT);

        $mform->addElement('select', 'tomagrade_ACL', get_string('tomagrade_ACL', 'plagiarism_tomagrade'), $tomagradeaclopts);
        $mform->addRule('tomagrade_ACL', null, 'required', null, 'client');
        $mform->setType('tomagrade_ACL', PARAM_TEXT);
        $mform->addHelpButton('tomagrade_ACL', 'tomagrade_ACL', 'plagiarism_tomagrade');

        $mform->addElement('textarea', 'ACL_CATEGORY', 'ACL - Category ID number', array('style' => 'height: 100px;width:400px'));
        $mform->setType('ACL_CATEGORY', PARAM_TEXT);

        $mform->addElement('textarea', 'ACL_COURSE', 'ACL - Course ID number', array('style' => 'height: 100px;width:400px'));
        $mform->setType('ACL_COURSE', PARAM_TEXT);

        $this->add_action_buttons(true);
        $checkconnection = $CFG->wwwroot . '/plagiarism/tomagrade/checkConnection.php';
        $mform->addElement('button', "onclick='asdfasfd'", "Check Connection",
         array("onclick" => "window.open('$checkconnection')"));
    }
}
