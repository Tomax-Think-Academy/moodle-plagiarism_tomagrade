<?php

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot.'/plagiarism/tomagrade/plagiarism_form.php');
require_login();

defined('MOODLE_INTERNAL') || die();
global $DB,$CFG;
    if ($CFG->version < 2011120100) {
        $context = get_context_instance(CONTEXT_SYSTEM);
    } else {
        $context = context_system::instance();
    }
#echo($USER->id."<br>");

if (!isset($_GET['id'])) {
    echo("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
    echo("<script>window.close();</script>");
}
$cmid = $_GET['id'];
$data = tomagrade_get_instance_config($cmid);

if ($data->complete > 0) {
    $DB->execute('UPDATE {plagiarism_tomagrade_config} SET complete = 0 WHERE cm = ?',array($cmid));
    resetMainGrades($cmid);
}
echo "<script>window.close();</script>";