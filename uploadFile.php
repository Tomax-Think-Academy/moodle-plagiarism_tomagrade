<?php



global $CFG, $DB, $USER;
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');
require_login();

defined('MOODLE_INTERNAL') || die();

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

$connection->uploadExam($contextid, $data);
echo "<script>window.close();</script>";
