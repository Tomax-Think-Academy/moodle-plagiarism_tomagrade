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
 * plagiarism.php - allows the admin to configure plagiarism stuff
 *
 * @package   plagiarism_tomagrade
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @copyright  based on 2010 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    require_once(dirname(dirname(__FILE__)) . '/../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/plagiarismlib.php');
    require_once($CFG->dirroot.'/plagiarism/tomagrade/lib.php');
    require_once($CFG->dirroot.'/plagiarism/tomagrade/plagiarism_form.php');

    require_login();
    admin_externalpage_setup('plagiarismtomagrade');
    if ($CFG->version < 2011120100) {
        $context = get_context_instance(CONTEXT_SYSTEM);
    } else {
        $context = context_system::instance();
    }
    require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

    //require_once('plagiarism_form.php');
    $mform = new plagiarism_setup_form();
    //$plagiarismplugin = new plagiarism_plugin_tomagrade();
    $plagiarismsettings = (array)get_config('plagiarism_tomagrade');

    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/plagiarism/tomagrade/settings.php'));
    }

    echo $OUTPUT->header();

    if (($data = $mform->get_data()) && confirm_sesskey()) {
        if (!isset($data->tomagrade_use)) {
            $data->tomagrade_use = 0;
        }
        set_config('enabled', $data->tomagrade_use, 'plagiarism_tomagrade');

        $tomagrade_userRolesToDisplayRelatedAssign = "";
        $tomagrade_userRolesToDisplayRelatedAssign_isfirst = true;

        $tomagrade_userRolesPermissionGradedExam = "";
        $tomagrade_userRolesPermissionGradedExam_isfirst = true;

        foreach ($data as $field=>$value) {
            if (isset($plagiarismsettings->$field) && $plagiarismsettings->$field == $value) {
                //local property copy is equal to submitted property
                continue; //Setting unchanged
            }




            if (strpos($field, 'role_') !== false) {
                $roleId = str_replace("role_","",$field);
                if (is_numeric($roleId) == false) { continue; }

                if ($tomagrade_userRolesToDisplayRelatedAssign_isfirst) {
                    $tomagrade_userRolesToDisplayRelatedAssign = $roleId;
                    $tomagrade_userRolesToDisplayRelatedAssign_isfirst = false;
                } else {
                    $tomagrade_userRolesToDisplayRelatedAssign = $tomagrade_userRolesToDisplayRelatedAssign . ",". $roleId;
                }
               continue;
            }

            if (strpos($field, 'rolePermissionGradedExam_') !== false) {
                $roleId = str_replace("rolePermissionGradedExam_","",$field);
                if (is_numeric($roleId) == false) { continue; }

                if ($tomagrade_userRolesPermissionGradedExam_isfirst) {
                    $tomagrade_userRolesPermissionGradedExam = $roleId;
                    $tomagrade_userRolesPermissionGradedExam_isfirst = false;
                } else {
                    $tomagrade_userRolesPermissionGradedExam = $tomagrade_userRolesPermissionGradedExam . ",". $roleId;
                }
               continue;
            }



            // Save the setting
            set_config($field, $value, 'plagiarism_tomagrade');

            if (strpos($field, 'tomagrade')===0) {
                if ($tiiconfigfield = $DB->get_record('config_plugins', array('name'=>$field, 'plugin'=>'plagiarism'))) {
                    $tiiconfigfield->value = $value;
                    if (! $DB->update_record('config_plugins', $tiiconfigfield)) {
                        error("errorupdating");
                    }
                } else {
                    $tiiconfigfield = new stdClass();
                    $tiiconfigfield->value = $value;
                    $tiiconfigfield->plugin = 'plagiarism';
                    $tiiconfigfield->name = $field;
                    if (! $DB->insert_record('config_plugins', $tiiconfigfield)) {
                        error("errorinserting");
                    }
                }
            }
        }

        $plagiarismsettings['tomagrade_userRolesToDisplayRelatedAssign'] = $tomagrade_userRolesToDisplayRelatedAssign;
        set_config('tomagrade_userRolesToDisplayRelatedAssign', $tomagrade_userRolesToDisplayRelatedAssign, 'plagiarism_tomagrade');

        $plagiarismsettings['tomagrade_userRolesPermissionGradedExam'] = $tomagrade_userRolesPermissionGradedExam;
        set_config('tomagrade_userRolesPermissionGradedExam', $tomagrade_userRolesPermissionGradedExam, 'plagiarism_tomagrade');


        echo $OUTPUT->notification(get_string('savedconfigsuccess', 'plagiarism_tomagrade'), 'notifysuccess');
    }

    // cant do isset($plagiarismsettings['tomagrade_userRolesToDisplayRelatedAssign']) because php 5.4 support

    $settingsKeys = array_keys($plagiarismsettings);

    $rolesArrRelatedUser = array();
    $rolesArrPermissionGradedExam = array();
    if (in_array('tomagrade_userRolesToDisplayRelatedAssign',$settingsKeys)) {
      $rolesArrRelatedUser = explode(",",$plagiarismsettings['tomagrade_userRolesToDisplayRelatedAssign']);
    }

    if (in_array('tomagrade_userRolesPermissionGradedExam',$settingsKeys)) {
     $rolesArrPermissionGradedExam = explode(",",$plagiarismsettings['tomagrade_userRolesPermissionGradedExam']);
    }


    foreach($rolesArrRelatedUser as $role) {
        $plagiarismsettings["role_".$role] = true;
    }

    foreach($rolesArrPermissionGradedExam as $role) {
        $plagiarismsettings["rolePermissionGradedExam_".$role] = true;
    }



    $mform->set_data($plagiarismsettings);

    echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
    $mform->display();

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
