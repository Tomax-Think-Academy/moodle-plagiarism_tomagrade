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

    $mform = new plagiarism_setup_form();
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

    $tomagradeuserrolestodisplayrelatedassign = "";
    $tomagradeuserrolestodisplayrelatedassignisfirst = true;

    $tomagradeuserrolespermissiongradedexam = "";
    $tomagradeuserrolespermissiongradedexamisfirst = true;

    foreach ($data as $field => $value) {
        if (isset($plagiarismsettings->$field) && $plagiarismsettings->$field == $value) {
            // Local property copy is equal to submitted property!
            continue; // Setting unchanged!
        }




        if (strpos($field, 'role_') !== false) {
            $roleid = str_replace("role_", "", $field);
            if (is_numeric($roleid) == false) {
                 continue;
            }

            if ($tomagradeuserrolestodisplayrelatedassignisfirst) {
                $tomagradeuserrolestodisplayrelatedassign = $roleid;
                $tomagradeuserrolestodisplayrelatedassignisfirst = false;
            } else {
                $tomagradeuserrolestodisplayrelatedassign = $tomagradeuserrolestodisplayrelatedassign . ",". $roleid;
            }
            continue;
        }

        if (strpos($field, 'rolePermissionGradedExam_') !== false) {
            $roleid = str_replace("rolePermissionGradedExam_", "", $field);
            if (is_numeric($roleid) == false) {
                continue;
            }

            if ($tomagradeuserrolespermissiongradedexamisfirst) {
                $tomagradeuserrolespermissiongradedexam = $roleid;
                $tomagradeuserrolespermissiongradedexamisfirst = false;
            } else {
                $tomagradeuserrolespermissiongradedexam = $tomagradeuserrolespermissiongradedexam . ",". $roleid;
            }
            continue;
        }



        // Save the setting!
        set_config($field, $value, 'plagiarism_tomagrade');

        if (strpos($field, 'tomagrade') === 0) {
            if ($tiiconfigfield = $DB->get_record('config_plugins', array('name' => $field, 'plugin' => 'plagiarism'))) {
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

    $plagiarismsettings['tomagrade_userRolesToDisplayRelatedAssign'] = $tomagradeuserrolestodisplayrelatedassign;
    set_config('tomagrade_userRolesToDisplayRelatedAssign', $tomagradeuserrolestodisplayrelatedassign, 'plagiarism_tomagrade');

    $plagiarismsettings['tomagrade_userRolesPermissionGradedExam'] = $tomagradeuserrolespermissiongradedexam;
    set_config('tomagrade_userRolesPermissionGradedExam', $tomagradeuserrolespermissiongradedexam, 'plagiarism_tomagrade');


    echo $OUTPUT->notification(get_string('savedconfigsuccess', 'plagiarism_tomagrade'), 'notifysuccess');
}


    $settingskeys = array_keys($plagiarismsettings);

    $rolesarrrelateduser = array();
    $rolesarrpermissiongradedexam = array();
if (in_array('tomagrade_userRolesToDisplayRelatedAssign', $settingskeys)) {
    $rolesarrrelateduser = explode(",", $plagiarismsettings['tomagrade_userRolesToDisplayRelatedAssign']);
}

if (in_array('tomagrade_userRolesPermissionGradedExam', $settingskeys)) {
    $rolesarrpermissiongradedexam = explode(",", $plagiarismsettings['tomagrade_userRolesPermissionGradedExam']);
}


foreach ($rolesarrrelateduser as $role) {
    $plagiarismsettings["role_".$role] = true;
}

foreach ($rolesarrpermissiongradedexam as $role) {
    $plagiarismsettings["rolePermissionGradedExam_".$role] = true;
}



    $mform->set_data($plagiarismsettings);

    echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
    $mform->display();

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
