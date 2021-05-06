<?php

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');
require_login();

defined('MOODLE_INTERNAL') || die();
global $DB, $CFG, $USER;
if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_SYSTEM);
} else {
    $context = context_system::instance();
}
#echo($USER->id."<br>");

if (!isset($_GET['cmid'])) {
    echo ("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
    echo ("<script>window.close();</script>");
}
if (!isset($_GET['studentid']) && !isset($_GET['groupid']) ) {
    echo ("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
    echo ("<script>window.close();</script>");
}
$cmid = $_GET['cmid'];
$config = get_config('plagiarism_tomagrade');

$isExam = false;
$matalaInfo = tomagrade_get_instance_config($cmid);
if (isset($matalaInfo->idmatchontg) && $matalaInfo->idmatchontg != '0' && $matalaInfo->idmatchontg != '' && is_null($matalaInfo->idmatchontg) == false) {
    $isExam = true;
}

$connection = new tomagrade_connection;
//$connection->do_login();

if ($isExam) {
    $studentidInTG = plagiarism_plugin_tomagrade::getTaodatZaot($_GET['studentid']);
} else {
    if (!isset($_GET['groupid'])) {
        $studentid = $_GET['studentid'];
        $studentidInTG = plagiarism_plugin_tomagrade::getUserIdentifier($studentid);
    } else {
        $groupid = $_GET['groupid'];
        $studentidInTG = tomagrade_connection::formatGroupName($groupid);
    }
}

$json =$connection->teacherLogin($USER->id);



$url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/Server/php/SAMLLogin.php/" . $json["Token"] . "/" . $json["UserID"] . "?coursename=" . $matalaInfo->examid;

header("Location: $url");
// echo $url;-
exit;
