<?php
global $CFG, $DB, $USER;
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');


defined('MOODLE_INTERNAL') || die();
require_login();

$context = getContextFromCMID($_GET['cmid']);
// $id = plagiarism_plugin_tomagrade::getUserIdentifier($USER->id);
// DISABLED DUE TO PERMISSIONS ISSUES!!!
$config = get_config('plagiarism_tomagrade');



$permission = false;
if (isset($_GET['userid'])) { #Check Permissions!!
    $id = plagiarism_plugin_tomagrade::getUserIdentifier($_GET['userid']);
    if ($_GET['userid'] == $USER->id){
        $permission = true;

    }
} elseif (isset($_GET['group'])) {
    $id = tomagrade_connection::formatGroupName($_GET['group']);
    $permission = in_array($USER->id, plagiarism_plugin_tomagrade::getUserIDByGroupIdentifier($id));
}
if ($permission == false) {
    if (isset($config->tomagrade_userRolesPermissionGradedExam) == true && $config->tomagrade_userRolesPermissionGradedExam != "") {
    
        // check roles on course level
        $teachersArr = $DB->get_records_sql("
        SELECT DISTINCT   u.id, u.username, u.firstname, u.lastname, u.email, u.idnumber 
        FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
        WHERE ra.userid = u.id
        AND ra.contextid = cxt.id
        AND cxt.contextlevel =50
        AND cxt.instanceid = c.id
        AND c.id = (SELECT course FROM {course_modules} WHERE id = '$context->instanceid')
        AND u.id = '$USER->id'
        AND roleid in ($config->tomagrade_userRolesPermissionGradedExam); ");

    
        if (count($teachersArr) > 0) {
            $permission = true;
        }
    }
}
if ($permission === false){
    echo ("<script>alert('Only the student who submitted this work is allowed to view this!');</script>");
    echo ("<script>window.close();</script>");
    exit;
}
$connection = new tomagrade_connection;
$connection->do_login();

if (isset($_GET['cmid'])) {
    $cmid = $_GET['cmid'];

    $matalaSettings = tomagrade_get_instance_config($cmid);

 
    $postdata = array();
    $postdata['id'] = $id;
    $postdata['examid'] = $matalaSettings->examid;

    // $postdata = "{\"id\":\"$id\",\"cmid\":\"$cmid\"}";

    $response = $connection->post_request("GetMoodleExamLink", json_encode($postdata),true);

//    $response = $connection->get_request("GetMoodleExamLink", "?id=" . urlencode($id) . "&cmid=" . urlencode($cmid));
    // $url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/Server/php/WS.php/GetMoodleExamLink/Token/UserID?id=".urlencode($id)."&cmid=".urlencode($cmid);
    // $ch = curl_init($url);
    // curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POST, 0);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //     'Content-Type: text/plain',
    //     'Connection: Keep-Alive'
    //     ));


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
            $postdata['examid'] = $matalaSettings->examid;

            $response = $connection->post_request("GetMoodleExamLink", json_encode($postdata),true);

            if ($response == "0" || strpos($response, "Notice") == true) {
                echo ("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
                echo ("<script>window.close();</script>");
                exit;
            }

        } else {
            $user = $DB->get_record('user', array('id' => $_GET['userid']));

            $id = $id . " --- " . strip_tags($user->firstname) . " " . strip_tags($user->lastname);

            $postdata = array();
            $postdata['id'] = $id;
            $postdata['examid'] = $matalaSettings->examid;

            $response = $connection->post_request("GetMoodleExamLink", json_encode($postdata),true);

            if ($response == "0" || strpos($response, "Notice") == true) {
                echo ("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
                echo ("<script>window.close();</script>");
                exit;
            }
        }
    }
    $response = trim(preg_replace('/\s+/', ' ', $response));
    //($resp);
    header('Location: ' . $response);
    exit;
} else {
    echo ("<script>alert('There was an error, Please contact a system adminstrator.');</script>");
    echo ("<script>window.close();</script>");
}
