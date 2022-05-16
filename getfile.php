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
 * getfile.php - Used to get the link to see the graded file for the student.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(__FILE__)) . '/../config.php');
global $CFG, $DB, $USER;
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');

require_login();

$context = get_context_from_cmid($_GET['cmid']);
// DISABLED DUE TO PERMISSIONS ISSUES!!!
$config = get_config('plagiarism_tomagrade');



$permission = false;
if (isset($_GET['userid'])) {
    $id = plagiarism_plugin_tomagrade::get_user_identifier($_GET['userid']);
    if ($_GET['userid'] == $USER->id) {
        $permission = true;

    }
} else if (isset($_GET['group'])) {
    $id = tomagrade_connection::format_group_name($_GET['group']);
    $permission = in_array($USER->id, plagiarism_plugin_tomagrade::get_user_id_by_group_identifier($id));
}
if ($permission == false) {
    if (isset($config->tomagrade_userRolesPermissionGradedExam) == true && $config->tomagrade_userRolesPermissionGradedExam != "") {

        // Check roles on course level.
        $teachersarr = $DB->get_records_sql("
        SELECT DISTINCT   u.id, u.username, u.firstname, u.lastname, u.email, u.idnumber
        FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
        WHERE ra.userid = u.id
        AND ra.contextid = cxt.id
        AND cxt.contextlevel =50
        AND cxt.instanceid = c.id
        AND c.id = (SELECT course FROM {course_modules} WHERE id = '$context->instanceid')
        AND u.id = '$USER->id'
        AND roleid in ($config->tomagrade_userRolesPermissionGradedExam); ");


        if (count($teachersarr) > 0) {
            $permission = true;
        }
    }
}
if ($permission === false) {
    echo ("<script>alert('".get_string('tomagrade_notAllowedToView', 'plagiarism_tomagrade')."');</script>");
    echo ("<script>window.close();</script>");
    exit;
}
$connection = new tomagrade_connection;
$connection->do_login();

if (isset($_GET['cmid'])) {
    $cmid = $_GET['cmid'];

    $matalasettings = tomagrade_get_instance_config($cmid);

    $ishiddenhrades = is_hidden_grades($cmid);

    if ($ishiddenhrades) {
        $message = get_string('tomagrade_exam_has_hidden_grades', 'plagiarism_tomagrade');
        echo ("<script>alert('$message');</script>");
        echo ("<script>window.close();</script>");
        exit;
    }


    $postdata = array();
    $postdata['id'] = $id;
    $postdata['examid'] = $matalasettings->examid;

    $response = $connection->post_request("GetMoodleExamLink", json_encode($postdata), true);

    if ($response == "deleted") {
        $message = get_string('files_were_deleted', 'plagiarism_tomagrade');
        echo ("<script>alert('$message');</script>");
        echo ("<script>window.close();</script>");
        exit;
    }

    if ($response == "0" || strpos($response, "Notice") == true) {
        if (strpos($id, '---') !== false) {
            $array = explode("---", $id);
            $id = substr($array[0], 0, -1);

            $postdata = array();
            $postdata['id'] = $id;
            $postdata['examid'] = $matalasettings->examid;

            $response = $connection->post_request("GetMoodleExamLink", json_encode($postdata), true);

            if ($response == "0" || strpos($response, "Notice") == true) {
                echo ("<script>alert('".get_string('tomagrade_contactAdmin', 'plagiarism_tomagrade')."');</script>");
                echo ("<script>window.close();</script>");
                exit;
            }

        } else {
            $user = $DB->get_record('user', array('id' => $_GET['userid']));

            $id = $id . " --- " . strip_tags($user->firstname) . " " . strip_tags($user->lastname);

            $postdata = array();
            $postdata['id'] = $id;
            $postdata['examid'] = $matalasettings->examid;

            $response = $connection->post_request("GetMoodleExamLink", json_encode($postdata), true);

            if ($response == "0" || strpos($response, "Notice") == true) {
                echo ("<script>alert('".get_string('tomagrade_contactAdmin', 'plagiarism_tomagrade')."');</script>");
                echo ("<script>window.close();</script>");
                exit;
            }
        }
    }
    $response = trim(preg_replace('/\s+/', ' ', $response));
    header('Location: ' . $response);
    exit;
} else {
    echo ("<script>alert('".get_string('tomagrade_contactAdmin', 'plagiarism_tomagrade')."');</script>");
    echo ("<script>window.close();</script>");
}
