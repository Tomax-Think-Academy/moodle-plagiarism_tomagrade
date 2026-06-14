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

$cmid= required_param('cmid', PARAM_INT);

$userid = optional_param('userid',null, PARAM_INT);
$group = optional_param('group',null, PARAM_INT);

$context = context_module::instance($cmid);

$permission = false;
// Students may view their own graded file; teachers/graders may view any file via has_capability below.
if ($userid === $USER->id) {
    $id = plagiarism_plugin_tomagrade::get_user_identifier($userid);
    $permission = true;
} else if (!is_null($group)) {
    $id = tomagrade_connection::format_group_name($group);
    if (in_array($USER->id, plagiarism_plugin_tomagrade::get_user_id_by_group_identifier($id))) {
        $permission = true;
    }
}
if ($permission === false) {
    if (!is_null($userid) && has_capability('mod/assign:grade', $context)) {
        $id = plagiarism_plugin_tomagrade::get_user_identifier($userid);
        $permission = true;
    } else if (!is_null($group) && has_capability('mod/assign:grade', $context)) {
        $id = tomagrade_connection::format_group_name($group);
        $permission = true;
    }
}

if ($permission === false) {
    echo ("<script>alert('".get_string('tomagrade_notAllowedToView', 'plagiarism_tomagrade')."');</script>");
    echo ("<script>window.close();</script>");
    exit;
}
$connection = new tomagrade_connection;
$connection->do_login();

if (!is_null($cmid)) {

    $matalasettings = tomagrade_get_instance_config($cmid);

    $ishiddenhrades = plagiarism_tomagrade_is_hidden_grades($cmid);

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
            $user = $DB->get_record('user', array('id' => $userid));

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
