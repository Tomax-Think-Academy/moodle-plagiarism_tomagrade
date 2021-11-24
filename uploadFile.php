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
 * uploadFile.php - Will be used to manually upload a file to the TomaGrade system if user wishes.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG, $DB, $USER;
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');
require_login();



$cmid = $_GET['cmid'];

$connection = new tomagrade_connection;
$connection->do_login();
$contextid = context_module::instance($cmid)->id;
if (isset($_GET['studentid'])) {
    $student = $_GET['studentid'];
    $data = $DB->get_record("plagiarism_tomagrade", array("cmid" => $cmid, "userid" => $student));
} else if (isset($_GET['groupid'])) {
    $group = $_GET['groupid'];
    $data = $DB->get_record("plagiarism_tomagrade", array("cmid" => $cmid, "groupid" => $group));
}
if (!isset($data) || is_null($data) || $data == false) {
    $data = new stdClass();
    $data->cmid = $cmid;
    $data->filehash = $_GET['filehash'];
    // $data->userid = $_GET["studentid"];

    $fs = get_file_storage();
    $file = $fs->get_file_by_hash($data->filehash);

    $data->userid = $file->get_userid();

}

$connection->upload_exam($contextid, $data);
echo "<script>window.close();</script>";
