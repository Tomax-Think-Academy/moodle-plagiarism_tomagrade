<?php

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');
require_login();

defined('MOODLE_INTERNAL') || die();
global $DB, $CFG;

$connection = new tomagrade_connection;
$res = $connection->getCourses();
if (isset($res)) {
    if ($res["IsTokenActive"] == true) {
        write("Your system is well connected!");
    } else {
        write("Please check your APIKey and UserID.");
    }
} else {
    write("It seems you do not have an open connection to TomaGrade");
}
// echo ("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
echo ("<script>window.close();</script>");


function write($message)
{
    echo ("<script>alert('$message');</script>");
}
