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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @copyright  based on 2010 Dan Marsden http://danmarsden.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

// Get global class.
global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');

require_once($CFG->libdir.'/gradelib.php');

// TomaGrade Class.

function get_context_from_cmid($cmid) {
    try {
        $context = context_module::instance($cmid);
    } catch (Exception $e) {
        $context = null;
    }
    if (empty($context) || $context == null) {
        return false;
    }
    return $context;
}
function check_enabled() {

    $cfg = get_config('plagiarism_tomagrade');
    if (isset($cfg->enabled) && $cfg->enabled) {
        return true;
    }
    return false;
}

class plagiarism_plugin_tomagrade extends plagiarism_plugin
{

    const GOODEXTENSIONS = array(
        "pdf" => "application/pdf",
        "doc" => "application/msword",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "xls" => "application/vnd.ms-excel",
        "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "rtf" => "application/rtf",
        "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "ppt" => "application/vnd.ms-powerpoint",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png"
    );

    public static function check_if_good_file($extension) {
        $extension = strtolower($extension);
        $arr = array_keys(self::GOODEXTENSIONS);
        return in_array($extension, $arr);

    }

    public static function get_taodat_zaot($userid) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $userid));

        return $user->idnumber;
    }

    public static function get_orbit_id($userid) {
        global $DB;

        $orbitiddata = $DB->get_records_sql("select o.orbitid from {import_interface_user} o JOIN {user} m ON o.username=m.username where m.id = ?", array($userid) );

        if (count($orbitiddata) > 0) {

           return reset($orbitiddata)->orbitid;
          }
          else {
              return -1;
          }
    }

    public static function get_id_match_on_tg() {
        global $DB;

        $config = get_config('plagiarism_tomagrade');

        return $config->tomagrade_IDMatchOnTomagrade;
    }

    public static function complete_zeroes($string, $zeros) {
        $digitsInThodatZaot = strlen($string);
        if ($zeros > $digitsInThodatZaot) {
            $zerosToAdd = $zeros - $digitsInThodatZaot;

             for ($x=0; $x < $zerosToAdd; $x++) {
                 $string = "0".$string;
             }
        }
        return $string;
    }




    public static function get_user_identifier($userid) {
        global $DB;

        $config = get_config('plagiarism_tomagrade');
        $user = $DB->get_record('user', array('id' => $userid));
        $output = "";
        if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_EMAIL) {
            $output = $user->email;
        } else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_ID) {
            $output = $user->idnumber;
        } else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_USERNAME) {
            $output = $user->username;
        } else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_ORBITID) {
            $output = $user->idnumber;

            $orbitiddata = $DB->get_records_sql("select o.orbitid from {import_interface_user} o JOIN {user} m ON o.username=m.username where m.id = ?", array($userid) );

           if (count($orbitiddata) > 0) {

              $output = reset($orbitiddata)->orbitid;
             }

        } else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_HUJIID) {

              $hujiidData = $DB->get_records_sql("SELECT hujiid FROM huji.userdata INNER JOIN {user} as u on idnumber=tz WHERE u.id=?", array($userid));

              if (count($hujiidData) >0) {

                $output = reset($hujiidData)->hujiid;
               }

          }

        if (isset($config->tomagrade_DisplayStudentNameOnTG) && $config->tomagrade_DisplayStudentNameOnTG == "0") {
            return $output;
        }

         $output = $output . " --- " . strip_tags($user->firstname) . " " . strip_tags($user->lastname);

        return $output;
    }

    public static function get_teacher_identifier($userid) {
        global $DB;

        $config = get_config('plagiarism_tomagrade');
        $user = $DB->get_record('user', array('id' => $userid));
        $newObject = new stdClass();
        if ($config->tomagrade_DefaultIdentifier_TEACHER == self::IDENTIFIER_BY_EMAIL) {
            $newObject->identify = "Email";
            $newObject->data = $user->email;
            return $newObject;
        } else if ($config->tomagrade_DefaultIdentifier_TEACHER == self::IDENTIFIER_BY_ID) {
            $newObject->identify = "TeacherID";
            $newObject->data = $user->idnumber;

            if (isset($config->tomagrade_zeroCompleteTeacher)) {
                if (is_numeric($config->tomagrade_zeroCompleteTeacher)) {
                    $zeros = intval($config->tomagrade_zeroCompleteTeacher);
                    if ($zeros > 0 ) {
                        $newObject->data = plagiarism_plugin_tomagrade::complete_zeroes($user->idnumber."", $zeros);
                    }
                }
             }

            return $newObject;
        }
         else if ($config->tomagrade_DefaultIdentifier_TEACHER == self::IDENTIFIER_BY_USERNAME) {
                $newObject->identify = "TeacherID";
                $newObject->data = $user->username;

                if (isset($config->tomagrade_zeroCompleteTeacher)) {
                    if (is_numeric($config->tomagrade_zeroCompleteTeacher)) {
                        $zeros = intval($config->tomagrade_zeroCompleteTeacher);
                        if ($zeros > 0 ) {
                            $newObject->data = plagiarism_plugin_tomagrade::complete_zeroes($user->idnumber."", $zeros);
                        }
                    }
                 }

                return $newObject;
        }  else if ($config->tomagrade_DefaultIdentifier_TEACHER == self::IDENTIFIER_BY_HUJIID) {

            $newObject->identify = "TeacherID";

            $newObject->data = $DB->get_field_sql("SELECT hujiid FROM huji.userdata WHERE tz=?", array($user->idnumber));

            if (isset($config->tomagrade_zeroCompleteTeacher)) {
                if (is_numeric($config->tomagrade_zeroCompleteTeacher)) {
                    $zeros = intval($config->tomagrade_zeroCompleteTeacher);
                    if ($zeros > 0 ) {
                        $newObject->data = plagiarism_plugin_tomagrade::complete_zeroes($user->idnumber."", $zeros);
                    }
                }
             }

            return $newObject;

        }
    }


    public static function get_user_id_by_identifier($identifier) {
        global $DB;

        $config = get_config('plagiarism_tomagrade');
        $IdentifierTable = "";
        if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_EMAIL) {
            $IdentifierTable = "email";
        } else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_ID) {
            $IdentifierTable = "idnumber";
        } else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_USERNAME) {
            $IdentifierTable = "username";
        }else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_ORBITID) {
            $IdentifierTable = "orbitid";
        }else if ($config->tomagrade_DefaultIdentifier == self::IDENTIFIER_BY_HUJIID) {
            $IdentifierTable = "HUJIID";
        }

        if (strpos($identifier, '---') !== false) {
            $array = explode("---", $identifier);
            $identifier = substr($array[0], 0, -1);
        }

        if ($IdentifierTable == "orbitid") {

            $orbitiddata = $DB->get_records_sql("select m.id from {import_interface_user} o JOIN {user} m ON o.username=m.username where o.orbitid = ?", array($identifier));

            if (count($orbitiddata) >0) {
                $userid =  reset($orbitiddata)->id;
                return array($userid);
              } else {
                  return false;
              }

        } else if ($IdentifierTable == "HUJIID") {
             $hujiidData = $DB->get_records_sql("SELECT u.id FROM {user} as u  INNER JOIN huji.userdata on tz=idnumber where hujiid=?", array($identifier));

             if (count($hujiidData) >0) {
                $userid =  reset($hujiidData)->id;
                return array($userid);
              } else {
                  return false;
              }

        } else {
             $user = $DB->get_record('user', array($IdentifierTable => $identifier));
        }
        if ($user != false) {
            return array($user->id);
        }
        return false;
    }

    static function get_user_id_by_group_identifier($name) {
        global $DB;
        $posStart = strrpos($name, "(", -1);
        $posEnd = strrpos($name, ")", -1);
        $groupid = substr($name, $posStart + 1, $posEnd - ($posStart + 1));
        $rows = $DB->get_records("groups_members", array("groupid" => $groupid));
        $returnArray = array();
        foreach ($rows as $row) {
            array_push($returnArray, $row->userid);
        }
        if (empty($returnArray)) {
            return false;
        }
        return $returnArray;
    }


    const RUN_NO = 0;
    const RUN_MANUAL = 1;
    const RUN_IMMEDIATLY = 2;
    const RUN_AFTER_FIRST_DUE_DATE = 3;
    #const RUN_AFTER_LAST_DUE_DATE = 4;

    const ALL_SITE = false;
    const ACL = true;

    const KEN = 1;
    const LO = 0;


    const IDENTIFIER_BY_EMAIL = 0;
    const IDENTIFIER_BY_ID = 1;
    const IDENTIFIER_BY_USERNAME = 2;
    const IDENTIFIER_BY_ORBITID = 3;
    const IDENTIFIER_BY_HUJIID = 4;

    const SHOWSTUDENTS_NEVER = 0;
    const SHOWSTUDENTS_ALWAYS = 1;
    const SHOWSTUDENTS_ACTCLOSED = 2;

    const INACTIVE = 0;
    const IDENTIFIER_BY_CourseID = 1;
    const IDENTIFIER_BY_ExamID = 2;

    /**
     * hook to allow plagiarism specific information to be displayed beside a submission
     * @param array  $linkarraycontains all relevant information for the plugin to generate a link
     * @return string
     *
     */
    public function get_links($linkarray) {

        if (isset($linkarray['file'])) {
            if ($linkarray['file']->get_filearea() == "introattachment") { return; }
        }

        global $DB, $USER, $CFG;
        if (check_enabled() && !isset($linkarray["forum"]) && isset($linkarray['file']) && isset($linkarray['cmid']) && isset($linkarray['userid'])) {

            $cmid = $linkarray['cmid'];
            $userid = $linkarray['userid']; // Who uploaded -- could be admin.
            $file = $linkarray['file'];

            $config = tomagrade_get_instance_config($cmid);
            if ($config->upload == 0) {
                return false;
            }
            $status = $DB->get_record("plagiarism_tomagrade", array('cmid' => $cmid, "filehash" => $file->get_pathnamehash()));
            $result = "";
            if ($status != false) {
                if ($status->groupid != null) {
                    $urlbuild = "?cmid=$cmid&group=$status->groupid";
                } else {
                    $urlbuild = "?cmid=$cmid&userid=$userid";
                }
                $instance = $DB->get_record('course_modules', array('id' => $cmid));
                $matalaSettings = $DB->get_record("assign", array("id" => $instance->instance));
                $isHiddenGrades = is_hidden_grades($cmid);
                if ( $status->finishrender) { // Check if i can show the new file to the students.
                    if (($matalaSettings->blindmarking == "0" || $matalaSettings->revealidentities == "1") && !$isHiddenGrades) {
                         $result = $result . html_writer::link($CFG->wwwroot . '/plagiarism/tomagrade/getfile.php' . $urlbuild, "<br>". get_string('Press_here_to_view_the_graded_exam', 'plagiarism_tomagrade'), array("target" => "_blank", "class" => "linkgetfile"));
                    }
                }
                return $result;
            } else {
                // Uploaded but not when moodle was activated.

                $mimetype = $linkarray["file"]->get_mimetype();

                $ext = "";
                $arr = explode("/", $mimetype);
                if (count($arr) == 2) {
                    if (isset($arr[1])) {
                        $ext = strtolower($arr[1]);
                    }
                }

                $hash = $linkarray["file"]->get_pathnamehash();
                $urlbuild = "?cmid=$cmid&filehash=$hash";

               if (plagiarism_plugin_tomagrade::check_if_good_file($ext) == false) {
                   return "<br> " . get_string('invalid_file_type_for_TomaGrade', 'plagiarism_tomagrade') . "<br> " . html_writer::link($CFG->wwwroot . '/plagiarism/tomagrade/uploadFile.php' . $urlbuild, get_string('Upload_to_TomaGrade_again', 'plagiarism_tomagrade'), array("target" => "_blank"));
               }

                 return "<br> " . html_writer::link($CFG->wwwroot . '/plagiarism/tomagrade/uploadFile.php' . $urlbuild, "Submit to Toma Grade ", array("target" => "_blank"));
            }
        }
    }

    /**
     * hook to allow a disclosure to be printed notifying users what will happen with their submission
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
        global $OUTPUT;
    }

    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     */
    public function update_status($course, $cm) {
        // Called at top of submissions/grading pages - allows printing of admin style links or updating status.
        global $CFG, $DB;
        $config = tomagrade_get_instance_config($cm->id);
        if ($config->upload == 0) {
            return false;
        }
        $exams = $DB->get_records('plagiarism_tomagrade', array('cmid' => $cm->id));
        $userids = new stdClass();
        if (check_enabled()) {
            foreach ($exams as $exam) {
                if ($exam->groupid != null) {
                    $groupsMemebers = $DB->get_records('groups_members', array('groupid' => $exam->groupid));
                    foreach ($groupsMemebers as $group) {
                        $userids->{$group->userid} = $exam;
                    }
                } else {
                    $userids->{$exam->userid} = $exam;
                }
            }
            $urlOpenExam = $CFG->wwwroot . '/plagiarism/tomagrade/openexam.php';
            $urlReUpload = $CFG->wwwroot . '/plagiarism/tomagrade/uploadFile.php';
            return '
        <style>
            .link{
                text-decoration:none;
                color:green;
            }
            .link:hover{
                text-decoration:none;
                color:green;
            }
            .linkgetfile{
                color:black;
            }
            .tgname{
                font-size: 105%;
            }
            </style>
        <script>
        setTimeout(function(){
            let urlOpenExam = "' . $urlOpenExam . '"
            let urlReUpload = "' . $urlReUpload . '"
            let cmid = ' . $cm->id . '
            let location = 5;
            let x = document.querySelectorAll("tr");
            let thead = document.getElementsByTagName("table")[0].tHead.children[0];
            th = document.createElement("th");
            th.className = "header c"+(x.length + 2)
            th.innerHTML = "TomaGrade";
            // // thead.appendChild(th);
            thead.insertBefore(th,thead.children[location])
            // th.insertAfter(x)
            let y = document.querySelectorAll("tr");
            let users = JSON.parse(`' . JSON_ENCODE($userids) . '`)
            console.log(users)

            y.forEach((tr,index) => {
                let tbody = y[index];
                td = document.createElement("td");
                td.className = "";
                if (index != 0){
                    let className = tr.className;
                    let userID = className.split(" ")[0].substring(4);
                    if (isNaN(userID)) return;
                    let currentUser = users[userID];
                    console.log(userID);
                    if (currentUser){
                        let preHTML = ""
                        let urlToOpenGrade = urlOpenExam + "?cmid="+cmid
                        let urlReUploadTG = urlReUpload + "?cmid="+cmid
                        if(currentUser.groupid != null){
                            urlToOpenGrade += "&groupid="+currentUser.groupid;
                            urlReUploadTG += "&groupid="+currentUser.groupid;
                        }else{
                            urlToOpenGrade += "&studentid="+currentUser.userid;
                            urlReUploadTG += "&studentid="+currentUser.userid;
                        }
                        let textToReUpload = "'. get_string('Upload_to_TomaGrade', 'plagiarism_tomagrade') . '";
                        if (currentUser["status"] == 0){

                        }else{
                            preHTML = \'<div id="TomaGrade"><br><a class="link" href=\'+ urlToOpenGrade +\' target="_blank" >'. get_string('Check_with', 'plagiarism_tomagrade') .'<img style="padding-bottom:15px;" src="' . $CFG->wwwroot . '/plagiarism/tomagrade/pix/icon.png" alt="Go to TomaGrade!"></a></div>\'
                            textToReUpload = "'. get_string('Upload_to_TomaGrade_again', 'plagiarism_tomagrade') .'"
                        }
                        preHTML+= \'<div> <a target="_blank" href="\' + urlReUploadTG + \'">\' + textToReUpload + \' </a> </div>\'
                        td.innerHTML = preHTML;
                        // thead.appendChild(td);
                    }else{
                        td.innerHTML = "<p>'.  get_string('TomaGrade_did_not_recognise_any_file', 'plagiarism_tomagrade')  .'</p>";
                    }
                    tbody.insertBefore(td,tbody.children[location])
                }

            })
            // for(let i = 0; i < x.length; i++){
            //     if(x[i].className.indexOf("user") != -1 && x[i].className.indexOf("checked") == -1){
            //         x[i].childNodes[5].insertAdjacentHTML("beforeend","<p>The grade has not been submitted yet.</p>");
            //     }
            // }
        },1000)
        </script>';
        }
    }
}
    /* hook to save plagiarism specific settings on a module settings page
     * @param object $data - data from an mform submission.
    */
function plagiarism_tomagrade_coursemodule_edit_post_actions($data, $course) {
    global $DB, $USER;
    if (check_enabled()) {

        $config = get_config('plagiarism_tomagrade');

        $cmid = $data->coursemodule;
        $oldInformation = tomagrade_get_instance_config($cmid);
        if (isset($data->tomagrade_upload)) {
            if ($oldInformation->new == true && $data->tomagrade_upload == 0) {
                return $data;
            }
            $connection = new tomagrade_connection;
            $connection->do_login();
            $epoch = $data->duedate;
            $dt = new DateTime("@$epoch");
            $dt = $dt->format('d/m/y');
            $id = "";

            $isExam = false;
            if (isset($data->tomagrade_idmatchontg) && $data->tomagrade_idmatchontg != '0' && $data->tomagrade_idmatchontg != '' && is_null($data->tomagrade_idmatchontg) == false) {
                $isExam = true;
            }

            if ($oldInformation->new == true || (isset($oldInformation->idmatchontg) && isset($data->tomagrade_idmatchontg) && ($oldInformation->idmatchontg != $data->tomagrade_idmatchontg))) {
                $examIDinTG = calc_exam_id_in_tg($cmid, isset($data->tomagrade_idmatchontg) ? $data->tomagrade_idmatchontg : "0");

                if ($isExam == false) {
                    $isExamIDTafus = is_exam_exists_in_tg($examIDinTG);
                    if ($isExamIDTafus) {
                        $examIDinTG = calc_exam_id_in_tg($cmid, $data->tomagrade_idmatchontg);

                        $isExamIDTafus = is_exam_exists_in_tg($examIDinTG);
                        if ($isExamIDTafus) {
                            \core\notification::error('Tomagrade error, try again later');
                            return $data;
                        } else if ($isExamIDTafus == -1) {
                            \core\notification::error('Tomagrade server is not avaiable right now, try again later');
                            return $data;
                        }
                    } else if ($isExamIDTafus == -1) {
                        \core\notification::error('Tomagrade server is not avaiable right now, try again later');
                        return $data;
                    }
                }
            } else {
                $examIDinTG = $oldInformation->examid;
            }

            if ($config->tomagrade_DefaultIdentifier_TEACHER == plagiarism_plugin_tomagrade::IDENTIFIER_BY_EMAIL) { // To get teacherID.

                $id = $connection->get_teacher_code_from_email($data->tomagrade_username);

            } else if ($config->tomagrade_DefaultIdentifier_TEACHER == plagiarism_plugin_tomagrade::IDENTIFIER_BY_ID) {

                $ownerRow = $DB->get_record_sql(" select idnumber from {user} where lower(email) = ? ", array(strtolower($data->tomagrade_username)));
                $id = $ownerRow->idnumber;
                if ($id == null) { $id = 1; }

            } else if ($config->tomagrade_DefaultIdentifier_TEACHER == plagiarism_plugin_tomagrade::IDENTIFIER_BY_USERNAME) {

                $ownerRow = $DB->get_record_sql(" select username from {user} where lower(email) = ? ", array(strtolower($data->tomagrade_username)));
                $id = $ownerRow->username;

            } else if ($config->tomagrade_DefaultIdentifier_TEACHER == plagiarism_plugin_tomagrade::IDENTIFIER_BY_HUJIID) {

                $ownerRow = $DB->get_record_sql(" SELECT hujiid FROM {user} as u INNER JOIN huji.userdata ON idnumber=tz WHERE lower(u.email) = ?", array(strtolower($data->tomagrade_username)));
                $id = $ownerRow->hujiid;
            }

            if (isset($config->tomagrade_zeroCompleteTeacher)) {
                if (is_numeric($config->tomagrade_zeroCompleteTeacher)) {
                    $zeros = intval($config->tomagrade_zeroCompleteTeacher);
                    if ($zeros > 0 ) {
                        $id = plagiarism_plugin_tomagrade::complete_zeroes($id."", $zeros);
                    }
                }
            }

            $courseInfo = $DB->get_record('course', array('id' => $data->course));
            $courseName = $courseInfo->shortname;
            $examDate = date('d/m/Y', $data->duedate);

            if (isset($data->tomagrade_idmatchontg) == false ||  is_null($data->tomagrade_idmatchontg)) {
                $data->tomagrade_idmatchontg = 0;
            }

            if (isset($config->tomagrade_AllowOnlyIdMatchOnTG) && isset($data->tomagrade_upload)) {
                if ($config->tomagrade_AllowOnlyIdMatchOnTG == "1" && $data->tomagrade_idmatchontg == "0" && $data->tomagrade_upload != "0") {

                    $data->tomagrade_upload = "0";

                    \core\notification::error('For TomaGrade settings, please select compatible course on ID Match On TomaGrade ');
                    return null;

                }
            }

            $doNotChangeUsername = false;
            if (intval($data->tomagrade_idmatchontg) == 0) {
                $response = $connection->get_request("MoodleGetExamDetails", "/$examIDinTG");
                $responseDecoded = json_decode($response);

                    if (isset($responseDecoded->GetExamDetail->ExternalTeacherID) && isset($responseDecoded->GetExamDetail->ExamStatus) && $responseDecoded->GetExamDetail->ExamStatus > 0) {
                    // Exam is already exists and in status > 0 , do not change the teacher code.
                    $id = $responseDecoded->GetExamDetail->ExternalTeacherID;
                    $doNotChangeUsername = true;

                    \core\notification::error( get_string('exam_is_already_exists_and_in_status_gt_zero', 'plagiarism_tomagrade'));
                    }

                    $isCreateUsers = isset($config->tomagrade_createUsers) && $config->tomagrade_createUsers == 1;

                    $identifyByEmail = true;
                    if ($config->tomagrade_DefaultIdentifier_TEACHER != 0) {
                        $identifyByEmail = false;
                    }

                    if ($isCreateUsers) {

                    $checkIDsExists = array();
                    $teachersEmailsArray = array();

                    $teachersIDsArray = array();

                    $idInModdle = $DB->get_record_sql(" select id from {user} where email = ? ", array($data->tomagrade_username));
                    $idInModdle = $idInModdle->id;
                    array_push($checkIDsExists, "'".$idInModdle."'");
                    foreach ($data as $field=>$value) {
                        if (strpos($field, 'tomagrade_shareTeacher_') !== false) {
                            $teacherId = str_replace("tomagrade_shareTeacher_", "", $field);
                            if (is_numeric($teacherId) == false) { continue; }
                            array_push($checkIDsExists, "'".$teacherId."'");
                        }
                    }

                    $emailToIDNumber = array();
                    $emailToDetails = array();

                    if (count($checkIDsExists) > 0) {

                        if ($config->tomagrade_DefaultIdentifier_TEACHER != 4) {
                            $teachersArr = $DB->get_records_sql("
                            SELECT email,idnumber,firstname,lastname,lang,username from {user} where id in (". implode(",", $checkIDsExists) .") ");
                        } else {
                            $teachersArr = $DB->get_records_sql("
                            SELECT email,firstname,lastname,lang,username,hujiid as idnumber from {user} as u inner join huji.userdata as h on u.idnumber=h.tz where u.id in (". implode(",", $checkIDsExists) .") ");
                        }

                        foreach ($teachersArr as $row) {
                            array_push($teachersEmailsArray, $row->email);
                            if ($config->tomagrade_DefaultIdentifier_TEACHER != 2) {
                                    array_push($teachersIDsArray, $row->idnumber);
                                    $emailToIDNumber[$row->email] = $row->idnumber;
                            } else {
                                array_push($teachersIDsArray, $row->username);
                                $emailToIDNumber[$row->email] = $row->username;
                            }

                            $emailToDetails[$row->email] = array();
                            $emailToDetails[$row->email]['firstName'] = $row->firstname;
                            $emailToDetails[$row->email]['lastName'] = $row->lastname;
                            $emailToDetails[$row->email]['lang'] = $row->lang;
                            $emailToDetails[$row->email]['username'] = $row->username;

                        }

                        $postdata = array();
                        $postdata['emails'] = $teachersEmailsArray;
                        $response = $connection->post_request("GetTeacherIdMoodle", json_encode($postdata));
                        if (isset($response['Message']) && is_array($response['Message'])) {

                            $arrayTeachersEmailsAndTeacherCode = $response['Message'];
                            $emailThatExists = array();

                            foreach($arrayTeachersEmailsAndTeacherCode as $teacher) {
                                $emailThatExists[$teacher['Email']] = true;
                            }

                            $postdata = array();
                            $postdata['teacherCodes'] = $teachersIDsArray;
                            $response = $connection->post_request("GetTeacherIdMoodle", json_encode($postdata));
                            if (isset($response['Message']) && is_array($response['Message'])) {

                                $arrayTeachersEmailsAndTeacherCode = $response['Message'];
                                $teacherCodeExists = array();

                                foreach($arrayTeachersEmailsAndTeacherCode as $teacher) {
                                    $teacherCodeExists[$teacher['ExternalTeacherID']] = true;
                                }

                                $postdata = array();
                                $postdata['usersData'] = array();

                                foreach($teachersEmailsArray as $potentialUserToAdd) {
                                    if (isset($emailThatExists[$potentialUserToAdd]) == false) {
                                        if (isset($emailToIDNumber[$potentialUserToAdd])) {
                                            if (isset($teacherCodeExists[$emailToIDNumber[$potentialUserToAdd]]) == false) {
                                                $user = array();
                                                $user['Email'] = $potentialUserToAdd;
                                                $user['FirstName'] = $emailToDetails[$potentialUserToAdd]['firstName'];
                                                $user['LastName'] = $emailToDetails[$potentialUserToAdd]['lastName'];
                                                $user['RoleID'] = 0;
                                                $user['TeacherCode'] = $emailToIDNumber[$potentialUserToAdd];

                                                if (empty($user['TeacherCode']) || empty($user['FirstName']) == true || empty($user['LastName']) == true) {
                                                    \core\notification::error( get_string('error_during_creating_new_user_in_tomagrade_missing_params', 'plagiarism_tomagrade') . " " . $emailToDetails[$potentialUserToAdd]['username']);
                                                    continue;
                                                }

                                                if ($emailToDetails[$potentialUserToAdd]['lang'] == "he") {
                                                    $user['Language'] = "עברית";
                                                } else {
                                                    $user['Language'] = "English";
                                                }
                                                $user['IsOTP'] = 0;
                                                $user['choose'] = "insertNewUser";

                                                array_push($postdata['usersData'], $user);
                                            }
                                            else {
                                                if ($identifyByEmail) {
                                                    // Identify by email, email does not exists but teacher code exists.

                                                    // Error teacher code already exists in TomaGrade for user.

                                                    \core\notification::error( get_string('error_during_creating_new_user_in_tomagrade_teacher_code_already_exists', 'plagiarism_tomagrade') . " " . $emailToDetails[$potentialUserToAdd]['username']);
                                                }
                                            }
                                        }
                                    } else {
                                        // Email exists in tg.
                                        if ($identifyByEmail == false) {
                                            // Identify by teacher code.
                                            if (isset($emailToIDNumber[$potentialUserToAdd])) {
                                                if (isset($teacherCodeExists[$emailToIDNumber[$potentialUserToAdd]]) == false) {
                                                    // Teacher code does not exist.

                                                        // Error email already exists in TomaGrade for user.

                                                        \core\notification::error( get_string('error_during_creating_new_user_in_tomagrade_email_already_exists', 'plagiarism_tomagrade') . " " . $emailToDetails[$potentialUserToAdd]['username']);
                                                }
                                            }
                                        }
                                    }
                                }
                                }
                        }

                        $numOfUsersToAdd = count($postdata['usersData']);
                        if ($numOfUsersToAdd > 0) {
                                $result = $connection->post_request("SaveUsers", json_encode($postdata));
                                if ($result['NumInsertNewUser'] != $numOfUsersToAdd) {
                                \core\notification::error( get_string('error_during_creating_new_user_in_tomagrade', 'plagiarism_tomagrade'));
                                }

                        }

                    }

                }

                    $examName = strip_tags($data->name);
                    $courseNameToSend = strip_tags($courseName);
                    $examName = str_replace('"', '', $examName);
                    $courseNameToSend = str_replace('"', '', $courseNameToSend);

                    $postdata = array();
                    $CoursesData = array();

                    $CoursesDataItem = array();
                    $CoursesDataItem['Exam_ID'] = $examIDinTG;
                    $CoursesDataItem['MoedName'] = -1;
                    $CoursesDataItem['SemesterName'] = -1;
                    $CoursesDataItem['Year'] = -1;
                    $CoursesDataItem['Exam_Name'] = $examName;
                    $CoursesDataItem['Exam_Date'] = $examDate;
                    $CoursesDataItem['Course'] = $courseNameToSend;
                    $CoursesDataItem['TeacherCode'] = $id;
                    $CoursesDataItem['source'] = "moodle_assign";
                    $CoursesDataItem['choose'] = "0";
                    array_push($CoursesData, $CoursesDataItem);

                    $postdata['CoursesData'] = $CoursesData;

                $result = $connection->post_request("SaveCourses", json_encode($postdata));

                $log = "the assign has been saved";

                if (isset($result["Message"]) == false) {

                    $log = get_string('tomagrade_id_email_incorrect', 'plagiarism_tomagrade');
                    echo "<script>alert('$log');</script>";
                    \core\notification::error($log);
                } else if (strpos($result["Message"], "NoTeacher") !== false || (strpos($result["Message"], "#New Exam: 0") !== false && strpos($result["Message"], "#Updated Exam: 0") !== false)) {

                    $log = get_string('tomagrade_id_email_incorrect', 'plagiarism_tomagrade');
                    if (isset($result["Message"])) {
                        $log = $log . "" . $result["Message"];
                    }
                    echo "<script>alert('$log');</script>";
                    \core\notification::error($log);
                }
                else if (isset($result["Response"]) && $result["Response"] == "Failed") {

                    $log = "error ";
                    if (isset($result["Message"])) {
                        $log = $log . "" . $result["Message"];
                    }
                    echo "<script>alert('$log');</script>";
                    \core\notification::error($log);
                }

            } else {
                $log = "the exam has been linked";
            }

            $event = \plagiarism_tomagrade\event\assigns_savedInTG::create(array(
            'context' => context_module::instance($data->coursemodule),
            'other' => $log
        ));
        $event->trigger();

        $doAnonymousCheck = true;

        if (isset($config->tomagrade_keepBlindMarking)) {
            if (intval($config->tomagrade_keepBlindMarking) == 0) {
                $doAnonymousCheck = false;
            }
        }

        if ($doAnonymousCheck) {
            if ($data->blindmarking == "0" && intval($data->tomagrade_idmatchontg) > 0) {
                // In this case, anonymous by agdarot mosad.
            }   else {
                    $anonymousBool = false;
                    if ($data->blindmarking == "1") {
                    $anonymousBool = true;
                    }

                    $postdata = array();
                    $postdata['ExamID'] = $examIDinTG;
                    $postdata['Anonymous'] = $anonymousBool;

                    $result = $connection->post_request("SetExamAnonymous", json_encode($postdata));
                    if (isset($result["Response"]) == false || $result["Response"] == "Failed") {
                        \core\notification::error('Error during setting exam anonymous in TomaGrade');
                    }
            }
        }

            // Share teachers.
            $tomagrade_shareAddioionalTeachers = "";
            $tomagrade_shareAddioionalTeachers_isFirst = true;

            $changedInSharedTeacher = false;

            // Avoid share for related tg user.
            $idRelatedTgUser = -1;
            $idRelatedTgUserQuery = $DB->get_record_sql(" select id from {user} where email = ? ", array($data->tomagrade_username));
            if (isset($idRelatedTgUserQuery->id)) {
                $idRelatedTgUser  = $idRelatedTgUserQuery->id;
            }

            foreach ($data as $field=>$value) {
            if (strpos($field, 'tomagrade_shareTeacher_') !== false) {
                $teacherId = str_replace("tomagrade_shareTeacher_", "", $field);
                if (is_numeric($teacherId) == false) { continue; }

                if ($teacherId == $idRelatedTgUser) { continue; }

                if ($tomagrade_shareAddioionalTeachers_isFirst) {
                    $tomagrade_shareAddioionalTeachers = $teacherId;
                    $tomagrade_shareAddioionalTeachers_isFirst = false;
                } else {
                    $tomagrade_shareAddioionalTeachers = $tomagrade_shareAddioionalTeachers . ",". $teacherId;
                }
                continue;
            }

            }

            if (isset($oldInformation->share_teachers) && $oldInformation->share_teachers != $tomagrade_shareAddioionalTeachers) {
            $changedInSharedTeacher = true;
            } else if (isset($oldInformation->share_teachers) == false && empty($tomagrade_shareAddioionalTeachers) == false) {
            $changedInSharedTeacher = true;
            }

            $errorInshare_teachersync = false;

            if ($changedInSharedTeacher) {
                $identifyByEmail = true;

                if ($config->tomagrade_DefaultIdentifier_TEACHER != 0) {
                        $identifyByEmail = false;
                }

                    // Delete teachers from share.
                    $teachersToDeleteStr = "";
                if (empty($oldInformation->share_teachers) == false) {
                    $newShare = array();
                    if (empty($tomagrade_shareAddioionalTeachers) == false) {
                        $newShare = explode(",", $tomagrade_shareAddioionalTeachers);
                    }
                    $oldShare = explode(",", $oldInformation->share_teachers);

                    $teachersToDelete = array();
                    foreach($oldShare as $id) {
                        if (in_array($id, $newShare) == false) {
                            array_push($teachersToDelete, $id);
                        }
                    }

                    if (count($teachersToDelete) > 0) {
                        // There are teachers that were removed from share.

                        $teachersToDeleteStr = implode(",", $teachersToDelete);

                    }

                }

                    $examIdInTG = $examIDinTG;

                // Share teachers.
                if (empty($tomagrade_shareAddioionalTeachers) == false || empty($teachersToDeleteStr) == false) {
                    $errorInshare_teachersync = share_teachers($tomagrade_shareAddioionalTeachers, $teachersToDeleteStr, $identifyByEmail, $examIdInTG);
                    $errorInshare_teachersync = !$errorInshare_teachersync;
                }

            }

            $config = new stdClass();
            $config->upload = $data->tomagrade_upload;
            $config->idmatchontg = $data->tomagrade_idmatchontg;
            $config->examid = $examIDinTG;

            $ownerRow = $DB->get_record_sql(" select id from {user} where lower(email) = ? ", array(strtolower($data->tomagrade_username)));
            $config->username = $ownerRow->id;

            if ($errorInshare_teachersync == false) {
                    $config->share_teachers = $tomagrade_shareAddioionalTeachers;
            } else {
                \core\notification::error("Error during share teachers. error in tomagrade server.");
            }

            if ($doNotChangeUsername == true) {
                $config->username = $oldInformation->username;
            }
            if ($config->upload !== plagiarism_plugin_tomagrade::RUN_NO) {
                $oldconfig = $oldInformation;
                $config->show_report_to_students = 0;
                // Nondisclosure document.
                if (isset($data->nondisclosure_notice) && $data->nondisclosure_notice == 1 && get_config('plagiarism_tomagrade', 'tomagrade_nondisclosure_notice_email')) {
                    $config->nondisclosure = 1;
                    $config->username = get_config('plagiarism_tomagrade', 'tomagrade_nondisclosure_notice_email');
                }
                // End nondisclosure document.
            }
            tomagrade_set_instance_config($cmid, $config);
        }
    }
    return $data;
}

function plagiarism_tomagrade_coursemodule_standard_elements($formwrapper, $mform) {
    $context = context_course::instance($formwrapper->get_course()->id);
    $modulename = isset($formwrapper->get_current()->modulename) ? 'mod_'.$formwrapper->get_current()->modulename : '';
    global $DB, $USER, $CFG;
    if (check_enabled()) {
        if ($modulename == 'mod_assign') {
            $course = $DB->get_record('course', array("id" => $context->instanceid));
            $courseid = $course->idnumber;
            $config = get_config('plagiarism_tomagrade');
            $courseLIST = explode("\r\n", $config->ACL_COURSE);
            $categoryLIST = explode("\r\n", $config->ACL_CATEGORY);
            $categoryid = $DB->get_record('course_categories', array("id" => $course->category))->idnumber;
            if ($config->tomagrade_ACL == "1"){
                if ($courseid == "" && count($courseLIST) > 0) { return; }
                if ($categoryid == "" && count($categoryLIST) > 0) { return; }
                if (in_array($courseid, $courseLIST) === false && in_array($categoryid, $categoryLIST) === false){
                    return;
                }
            }
            $tomaplagopts = array(
                plagiarism_plugin_tomagrade::RUN_NO => get_string('No', 'plagiarism_tomagrade'),
                plagiarism_plugin_tomagrade::RUN_IMMEDIATLY => get_string('Start_immediately', 'plagiarism_tomagrade'),
                plagiarism_plugin_tomagrade::RUN_MANUAL => get_string('Start_it_manual', 'plagiarism_tomagrade'),
            );

            $showstudentsopt = array(
                plagiarism_plugin_tomagrade::SHOWSTUDENTS_NEVER => "Never",
                plagiarism_plugin_tomagrade::SHOWSTUDENTS_ALWAYS => "Always",
                plagiarism_plugin_tomagrade::SHOWSTUDENTS_ACTCLOSED => "After due date"
            );

            $mform->addElement('header', 'tomaplagdesc', "TomaGrade");
            $mform->addElement('select', 'tomagrade_upload', get_string('Enable_TomaGrade', 'plagiarism_tomagrade'), $tomaplagopts);

            $config = get_config('plagiarism_tomagrade');

                $courses = array("0" =>  get_string('Irrelevant_regular_assignment', 'plagiarism_tomagrade'));

                if (isset($config->tomagrade_AllowOnlyIdMatchOnTG)) {
                    if ($config->tomagrade_AllowOnlyIdMatchOnTG == "1") {
                        $courses = array("0" =>  get_string('Please_select', 'plagiarism_tomagrade'));
                    }
                }

                $connection = new tomagrade_connection;

                $teacherCode2 = plagiarism_plugin_tomagrade::get_teacher_identifier($USER->id);

                $cmid = optional_param('update', 0, PARAM_INT);

                if (isset($cmid) && $cmid != 0) {
                $data = tomagrade_get_instance_config($cmid);
                }

                $teachersZero = 0;

                if (isset($config->tomagrade_zeroCompleteTeacher)) {
                    if (is_numeric($config->tomagrade_zeroCompleteTeacher)) {
                        $zeros = intval($config->tomagrade_zeroCompleteTeacher);
                        if ($zeros > 0 ) {
                            $teachersZero = $zeros;
                        }
                    }
                    }

                    if (isset($data->username)) {
                    if (is_numeric($data->username)) {
                        $ownerRow = $DB->get_record_sql(" select lower(email) as email from {user} where id = ? ", array($data->username));
                        $data->username = $ownerRow->email;
                    }
                    }

                // Teachers list.
                $teachers = array();
                $teachersIDs = array();
                $teacherCodeToEmail = array();
                $teachersEmailToIDinMoodle = array();
                $idInMoodleToEmail = array();

                $isCurrentOwnerExistsInTeachersList = false;
                $isLoggedUserExistsInTeachersList = false;

                $loggedUserIdNumber = $USER->idnumber;

                if ($teachersZero > 0) {
                    $loggedUserIdNumber = plagiarism_plugin_tomagrade::complete_zeroes($USER->idnumber, $teachersZero);
                }

                if (isset($config->tomagrade_userRolesToDisplayRelatedAssign) == true && $config->tomagrade_userRolesToDisplayRelatedAssign != "") {
                    $teachersArr = $DB->get_records_sql("
                    SELECT DISTINCT   u.id, u.username, u.firstname, u.lastname, lower(u.email) as email, u.idnumber
                    FROM {role_assignments} ra, {user} u, {course} c, {context} cxt
                    WHERE ra.userid = u.id
                    AND ra.contextid = cxt.id
                    AND cxt.contextlevel =50
                    AND cxt.instanceid = c.id
                    AND c.id = :instanceid
                    AND roleid in  ($config->tomagrade_userRolesToDisplayRelatedAssign)  ", array('instanceid' => $context->instanceid));

                    $idnumberToHuji = array();

                    if ($config->tomagrade_DefaultIdentifier_TEACHER == 4) {
                        $arrTeachersIDs = array();

                        foreach($teachersArr as $teacher) {
                            array_push($arrTeachersIDs, '"'.$teacher->idnumber.'"');
                        }

                        $hujiArr = $DB->get_records_sql("
                        SELECT tz, hujiid FROM  huji.userdata where tz in (". implode(",", $arrTeachersIDs) ." )");

                        foreach($hujiArr as $huji) {
                            $idnumberToHuji[$huji->tz] = $huji->hujiid;

                        }

                    }

                    foreach($teachersArr as $teacher) {
                                if ($config->tomagrade_DefaultIdentifier_TEACHER == 4) {
                                    if (isset($idnumberToHuji[$teacher->idnumber])) {
                                        $teacher->idnumber = $idnumberToHuji[$teacher->idnumber];
                                    } else {
                                        $teacher->idnumber = null;
                                    }
                                }
                                if ($config->tomagrade_DefaultIdentifier_TEACHER == 2) {
                                $teacher->idnumber = $teacher->username;
                                }

                            if ($teachersZero > 0 && empty($teacher->idnumber) == false) {
                                $teacher->idnumber = plagiarism_plugin_tomagrade::complete_zeroes($teacher->idnumber, $teachersZero);
                            }

                            $teachers[$teacher->email] = $teacher->firstname . " " . $teacher->lastname;
                            $teachersIDs[$teacher->email] = $teacher->idnumber; // Email to id map.
                            $teacherCodeToEmail[$teacher->idnumber] = $teacher->email; // Id to email map.
                            $teachersEmailToIDinMoodle[$teacher->email] = $teacher->id;
                            $idInMoodleToEmail[$teacher->id] = $teacher->email;

                            if ($cmid != 0 && isset($data->username)) {
                                if ($teacher->email == $data->username) {
                                    $isCurrentOwnerExistsInTeachersList = true;
                                }
                            }
                            if (strtolower($USER->email) == $teacher->email) {
                                $isLoggedUserExistsInTeachersList = true;
                            }
                    }
                } else {
                    if (isset($USER->firstname) && isset($USER->lastname)) {
                        $teachers[strtolower($USER->email)] = $USER->firstname . " " . $USER->lastname;
                    } else {
                        $teachers[strtolower($USER->email)] = "Me";
                    }
                    $teachersIDs[strtolower($USER->email)] = $loggedUserIdNumber;
                    $teacherCodeToEmail[$loggedUserIdNumber] = strtolower($USER->email);
                    $idInMoodleToEmail[$USER->id] = strtolower($USER->email);
                    $isLoggedUserExistsInTeachersList = true;
                }

                if ($isCurrentOwnerExistsInTeachersList == false && $cmid != 0 && isset($data->username)) {
                    $ownerRow = $DB->get_record_sql(" select firstname,lastname,lower(email) as email,idnumber,id from {user} where email = ? ", array($data->username));
                    if (isset($ownerRow->email)) {
                        $teachers[$ownerRow->email] = $ownerRow->firstname . " " . $ownerRow->lastname;
                        if ($teachersZero > 0) {
                            $ownerRow->idnumber = plagiarism_plugin_tomagrade::complete_zeroes($ownerRow->idnumber, $teachersZero);
                        }
                        $teachersIDs[$ownerRow->email] = $ownerRow->idnumber;
                        $teacherCodeToEmail[$ownerRow->idnumber] = $ownerRow->email;
                        $idInMoodleToEmail[$ownerRow->id] = $ownerRow->email;
                        if ($data->username == strtolower($USER->email)) {
                            $isLoggedUserExistsInTeachersList = true;
                        }
                    }
                }

                if (count($teachers) == 0 || $isLoggedUserExistsInTeachersList == false) {
                    if (isset($USER->firstname) && isset($USER->lastname)) {
                        $teachers[strtolower($USER->email)] = $USER->firstname . " " . $USER->lastname;
                    } else {
                        $teachers[strtolower($USER->email)] = "Me";
                    }
                    $teachersIDs[strtolower($USER->email)] = $loggedUserIdNumber;
                    $teacherCodeToEmail[$loggedUserIdNumber] = strtolower($USER->email);
                    $idInMoodleToEmail[$USER->id] = strtolower($USER->email);
                }

                $teachersEmailsArray = array();
                $teachersIDsArray = array();
                foreach($teachers as $email=>$name) {
                    array_push($teachersEmailsArray, $email);
                    if (isset($teachersIDs[$email])) {
                        array_push($teachersIDsArray, $teachersIDs[$email]);
                    }
                }

                $config = get_config('plagiarism_tomagrade');
                $identifyByEmail = true;
                if ($config->tomagrade_DefaultIdentifier_TEACHER != 0) {
                $identifyByEmail = false;
                }

                $postdata = array();
                if ($identifyByEmail) {
                    $postdata['emails'] = $teachersEmailsArray;
                } else {
                    $postdata['teacherCodes'] = $teachersIDsArray;
                }

                $response = $connection->post_request("GetTeacherIdMoodle", json_encode($postdata));

                $arrayTeachersEmailsAndTeacherCode = $response['Message'];

                $emailTeacherCodeMap = array();
                $teacherCodeExists = array();

                foreach($arrayTeachersEmailsAndTeacherCode as $teacher) {
                    $emailTeacherCodeMap[strtolower($teacher['Email'])] = $teacher['ExternalTeacherID'];
                    $teacherCodeExists[$teacher['ExternalTeacherID']] = true;
                }

                $teachersThatExistsInTM = array();
                $teachersIDsThatExistsInTM = array();

                $isLoggedUserExistsInTM = true;

                $select = $mform->createElement('select', 'tomagrade_username', get_string('Related_TomaGrade_User', 'plagiarism_tomagrade'));

                $isCreateUsers = isset($config->tomagrade_createUsers) && $config->tomagrade_createUsers == 1;

                foreach ($teachers as $value => $label) {
                    $teacherCode = $teachersIDs[$value];
                    if (($identifyByEmail == true && isset($emailTeacherCodeMap[$value]) == false)
                    || ($identifyByEmail == false && isset($teacherCodeExists[$teacherCode]) == false)) {
                        if ($value == strtolower($USER->email)) { $isLoggedUserExistsInTM = false; }
                        if ($isCreateUsers == false) {
                            $select->addOption($label . " - " . get_string('user_does_not_exists_in_tomagrade', 'plagiarism_tomagrade'), $value, array('disabled' => 'disabled'));
                        } else {
                            $select->addOption($label, $value);
                        }
                    } else {
                        $teachersThatExistsInTM[$value] = $label;
                            $select->addOption($label, $value);
                    }
                }
                $mform->addElement($select);

                if (isset($cmid) == false || $cmid == 0) {
                    // This is a new course.
                    if ($isLoggedUserExistsInTM) {
                    $select->setSelected(strtolower($USER->email));
                    }

                }

            if (isset($config->tomagrade_IDMatchOnTomagrade) && $config->tomagrade_IDMatchOnTomagrade != plagiarism_plugin_tomagrade::INACTIVE) {

                // Courses list.
                $paramsToSend = "/".$teacherCode2->data;
                if (isset($config->tomagrade_MatchingDue) && isset($cmid)) {
                    if (is_null($config->tomagrade_MatchingDue) == false) {
                        if (is_numeric($config->tomagrade_MatchingDue)) {
                            if ($config->tomagrade_MatchingDue > 0) {

                                $dueDateString = $DB->get_records_sql("
                                select a.duedate from {course_modules} c inner join {assign} a on c.instance = a.id where c.id = ?", array($cmid));
                                if (is_array($dueDateString)) {
                                    $dueDate = reset($dueDateString);
                                    if (isset($dueDate->duedate)) {
                                        $timeString = $dueDate->duedate;

                                        // ParamsToSend parm is not in use anymore, just for testing old versions.
                                        $paramsToSend = $paramsToSend . "/" . $config->tomagrade_MatchingDue . "/" . $timeString;
                                    }

                                }
                            }

                        }
                    }
                }

                $teachersEmailsArray = array();
                foreach($teachersThatExistsInTM as $email=>$name) {
                    array_push($teachersEmailsArray, $email);
                }

                $postdata = array();

                if ($identifyByEmail) {
                    $postdata['emails'] = $teachersEmailsArray;
                } else {
                    $postdata['teacherCodes'] = $teachersIDsArray;
                }

                if (isset($config->tomagrade_MatchingDue) && $config->tomagrade_MatchingDue > 0) {
                    $postdata['days'] = $config->tomagrade_MatchingDue;
                }
                if (isset($timeString)) {
                    $postdata['dueDateStr'] = $timeString;
                }

                if (isset($config->tomagrade_DaysDisplayBeforeExamDate) && is_numeric($config->tomagrade_DaysDisplayBeforeExamDate)) {
                    $postdata['daysDisplayBeforeExamDate'] = intval($config->tomagrade_DaysDisplayBeforeExamDate);
                }
                if (isset($config->tomagrade_DaysDisplayAfterExamDate) && is_numeric($config->tomagrade_DaysDisplayAfterExamDate)) {
                    $postdata['daysDisplayAfterExamDate'] = intval($config->tomagrade_DaysDisplayAfterExamDate);
                }

                $response = $connection->post_request("MoodleGetExamsList", json_encode($postdata), true);

                $response = json_decode($response, true);

                $examsByTeachersMap = array();

                $existingExams = $DB->get_records_sql("
                select distinct idmatchontg from {plagiarism_tomagrade_config} where idmatchontg != '0' ");
                $existingExamsMap = array();

                foreach($existingExams as $exam) {

                    $existingExamsMap[$exam->idmatchontg] = true;
                }

                $cmid = optional_param('update', 0, PARAM_INT);

                if (isset($cmid)) {
                        if (isset($data->idmatchontg)) {
                        unset($existingExamsMap[$data->idmatchontg]);
                    }
                }

                $isChoosenExamInList = false;

                if (isset($response['Exams'])) {

                    foreach($response['Exams'] as $exam) {
                        $stringForExam = $exam['ExamID'];

                        if (isset( $data->idmatchontg)) {
                            if ($exam['ExamID'] == $data->idmatchontg ) {
                                $isChoosenExamInList = true;
                            }
                        }

                        $exam['Email'] = strtolower($exam['Email']);

                        if (isset($existingExamsMap[$stringForExam]) == false) {
                            if (isset($exam['CourseID'])) {
                                $stringForExam = $stringForExam . " , ";
                                $stringForExam = $stringForExam . $exam['CourseID'];
                            }
                            if (isset($exam['ExamName'])) {
                                $stringForExam = $stringForExam . " , ";
                                $stringForExam = $stringForExam . $exam['ExamName'];
                            }
                            if (isset($exam['ExamDate'])) {
                                $stringForExam = $stringForExam . " , ";
                                try {
                                    $date = date_create($exam['ExamDate']);
                                    $stringForExam = $stringForExam . date_format($date, " d/m/Y ");
                                } catch(Exception $e) {
                                    $stringForExam = $stringForExam . $exam['ExamDate'];
                                }
                            }
                            if (isset($exam['Year'])) {
                                $stringForExam = $stringForExam . " , ";
                                $stringForExam = $stringForExam . $exam['Year'];
                            }
                            if (isset($exam['SimesterID'])) {
                                $stringForExam = $stringForExam . " , ". get_string('simester', 'plagiarism_tomagrade') .": ";
                                $stringForExam = $stringForExam . $exam['SimesterID'];
                            }
                            if (isset($exam['MoadID'])) {
                                $stringForExam = $stringForExam . " , " . get_string('moed', 'plagiarism_tomagrade') .": ";
                                $stringForExam = $stringForExam . $exam['MoadID'];
                            }
                            $courses[$exam['ExamID']] = $stringForExam;

                            $teacherEmailInMoodle = "";
                            if (isset($exam['Email']) && $identifyByEmail == true) {
                                $teacherEmailInMoodle = $exam['Email'];
                            }
                            else if (isset($exam['TeacherCode']) && $identifyByEmail == false) {
                                $teacherEmailInMoodle = $teacherCodeToEmail[$exam['TeacherCode']];
                            }

                            if ($teacherEmailInMoodle != "") {
                                    if (isset($examsByTeachersMap[$teacherEmailInMoodle]) == false) {
                                        $examsByTeachersMap[$teacherEmailInMoodle] = array();
                                }
                                $examsByTeachersMap[$teacherEmailInMoodle][$exam['ExamID']] = $stringForExam;
                            }
                            }
                    }
                }

                if (isset( $data->idmatchontg) && $isChoosenExamInList == false ) {

                    if ($data->idmatchontg != '0' && $data->idmatchontg != '' && is_null($data->idmatchontg) == false) {

                        $response = $connection->get_request("MoodleGetExamDetails", "/$data->idmatchontg");

                        $responseDecoded = json_decode($response);

                        if (isset($responseDecoded->Response) == true && isset($responseDecoded->GetExamDetail->Exam_Name) == true) {
                            $exam = $responseDecoded->GetExamDetail;

                            $stringForExam = $data->idmatchontg;

                            if (isset($exam->Courses)) {
                                $stringForExam = $stringForExam . " , ";
                                $stringForExam = $stringForExam . $exam->Courses;
                            }
                            if (isset($exam->Exam_Name)) {
                                $stringForExam = $stringForExam . " , ";
                                $stringForExam = $stringForExam . $exam->Exam_Name;
                            }
                            if (isset($exam->Exam_Date)) {
                                $stringForExam = $stringForExam . " ,";
                                try {
                                    $date = explode(" ", $exam->Exam_Date);
                                    $stringForExam = $stringForExam . $date[0];
                                } catch(Exception $e) {
                                    $stringForExam = $stringForExam . $exam->Exam_Date;
                                }
                            }
                            if (isset($exam->Year)) {
                                $stringForExam = $stringForExam . " , ";
                                $stringForExam = $stringForExam . $exam->Year;
                            }
                            if (isset($exam->Simester)) {
                                $stringForExam = $stringForExam . " , " . get_string('simester', 'plagiarism_tomagrade') .": ";
                                $stringForExam = $stringForExam . $exam->Simester;
                            }
                            if (isset($exam->Moed)) {
                                $stringForExam = $stringForExam . " , " . get_string('moed', 'plagiarism_tomagrade') .": ";
                                $stringForExam = $stringForExam . $exam->Moed;
                            }

                            $courses[$data->idmatchontg] = $stringForExam;

                            if (isset($data->username)) {
                                $teacherEmailInMoodle = $data->username;

                                if (isset($examsByTeachersMap[$teacherEmailInMoodle]) == false) {
                                        $examsByTeachersMap[$teacherEmailInMoodle] = array();
                                }
                                $examsByTeachersMap[$teacherEmailInMoodle][$data->idmatchontg] = $stringForExam;
                            }

                        }
                    }
                }

                $mform->addElement('select', 'tomagrade_idmatchontg', get_string('ID_Match_On_Tomagrade', 'plagiarism_tomagrade'), $courses);

                $buildJSTeachersMap = "var teachersmap = {}; ";
                foreach($examsByTeachersMap as $teacher=>$value) {
                    $buildJSTeachersMap = $buildJSTeachersMap . " var examArr = {}; ";
                    foreach($value as $exam=>$examString) {
                        $examString = str_replace("'", "", $examString);
                        $buildJSTeachersMap = $buildJSTeachersMap . "examArr['$exam'] = '$examString';";
                    }
                    $buildJSTeachersMap = $buildJSTeachersMap . " teachersmap['$teacher'] = examArr;";

                }

                $defaultOptionExam = "''";
                if (isset( $data->idmatchontg)) {
                    $defaultOptionExam = "'".$data->idmatchontg."'";
                }

                echo ("<script>
                var teachersHashMap = {};
                var defaultOptionExam = $defaultOptionExam;

                    var x = 0;
                var interval = setInterval( function() {
                    var currentTeacher = document.getElementById('id_tomagrade_username');
                    if (currentTeacher == undefined || currentTeacher == null) {
                        x++;
                        if (x > 100) {
                            clearInterval(interval);
                        }
                        return;
                    }

                    var currentTeacherEmail = document.getElementById('id_tomagrade_username').value;

                    clearInterval(interval);
                    initTeachersHashMap();
                    cleanSelectOptions();
                    setSelectByTeacher(currentTeacherEmail);

                    if (defaultOptionExam != '') {
                        setDefaultOptionToSelect(defaultOptionExam);
                    }

                    console.log('hello');

                    document.getElementById('id_tomagrade_username').addEventListener('change', function() {
                        var email = this.value;
                        cleanSelectOptions();
                        setSelectByTeacher(email);

                    } )},250);

                function setDefaultOptionToSelect(exam) {

                    var mySelect = document.getElementById('id_tomagrade_idmatchontg');


                    var length = mySelect.options.length;
                    for (i = length-1; i >= 1; i--) {
                        if (mySelect.options[i].value == exam) {
                            mySelect.selectedIndex = i;
                                break;
                        }
                    }

                }


                function cleanSelectOptions() {
                    var select = document.getElementById('id_tomagrade_idmatchontg');
                    var length = select.options.length;
                    for (i = length-1; i >= 1; i--) {
                        select.options[i] = null;
                    }
                }

                function initTeachersHashMap() {
                    $buildJSTeachersMap
                    teachersHashMap = teachersmap;
                }

                function setSelectByTeacher(email) {
                    var exams = teachersHashMap[email];
                    var select = document.getElementById('id_tomagrade_idmatchontg');


                    if (exams == null || exams == undefined) { return; }

                    Object.keys(exams).forEach( function (exam) {
                        var opt = document.createElement('option');
                        opt.value = exam;
                        opt.innerHTML = exams[exam];
                        select.appendChild(opt);
                    });

                }

                </script>");

            }

            if (count($teachersEmailToIDinMoodle) > 0) {
                $mform->addElement('static', 'tomagrade_shareAddioionalTeachers', get_string('tomagrade_shareAddioionalTeachersTitle', 'plagiarism_tomagrade'), null);

                foreach($teachersEmailToIDinMoodle as $email => $idInMoodle) {
                    $label = $teachers[$email];
                    $options = array('class' => 'checkboxgroup1');
                    if (isset($teachersThatExistsInTM[$email]) == false && $isCreateUsers == false) {
                        $label = $label . " - " .  get_string('user_does_not_exists_in_tomagrade', 'plagiarism_tomagrade');
                        $options = array('class' => 'checkboxgroup1', 'disabled' => 'disabled');
                    }
                    $mform->addElement('checkbox', "tomagrade_shareTeacher_".$idInMoodle, $label, null, $options);
                }
            }

            echo "<style>
            .checkboxgroup1 { margin-top:0 !important;  margin-bottom:0 !important; }
            </style>";

            if (isset($data)) {
                if (isset($data->examid)) {
                    $mform->addElement('text', 'tomagrade_currentExamID', get_string('tomagrade_currentExamIDonTomaGrade', 'plagiarism_tomagrade'), array('disabled' => 'disabled'));
                    $mform->setType('tomagrade_currentExamID', PARAM_TEXT);
                    $mform->setDefault('tomagrade_currentExamID', $data->examid);
                }
            }

            $cmid = optional_param('update', 0, PARAM_INT);
            if ($cmid) {
                if (isset($data) == false) {
                    $data = tomagrade_get_instance_config($cmid);
                }

                if (isset($data->share_teachers)) {
                    $share_teachers = explode(",", $data->share_teachers);

                    foreach($share_teachers as $id) {

                        $mform->setDefault('tomagrade_shareTeacher_'.$id, true);
                    }
                }

                $mform->setDefault('tomagrade_upload', $data->upload);

                if (isset( $data->idmatchontg)) {
                    $mform->setDefault('tomagrade_idmatchontg', $data->idmatchontg);
                }

                if ($data->new) {
                    if (isset($USER->email)) {
                        $mform->setDefault('tomagrade_username', strtolower($USER->email));
                    }
                }
                else if (isset($data->username)) {
                        $mform->setDefault('tomagrade_username', $data->username);
                }

                if ($data->complete > 0) {
                    $linkcontent = '<span id="changeme"><a id="removeclick" href="' . $CFG->wwwroot . '/plagiarism/tomagrade/resetexam.php?id=' . $cmid . '" target="_blank" onclick="removebutton()" >'. get_string('Click_here', 'plagiarism_tomagrade') .'</a></span>';
                    $mform->addElement('static', 'mylink', get_string('Reset_the_exam', 'plagiarism_tomagrade'), $linkcontent);
                    echo ("<script> function removebutton() {
                var elem = document.getElementById('removeclick');
                elem.parentNode.removeChild(elem);
                var elem2 = document.getElementById('changeme');
                elem2.innerHTML+='". get_string('The_test_has_been_reset', 'plagiarism_tomagrade') ."';
                }</script>");
                }
            } else {
                $config = get_config('plagiarism_tomagrade');
                $mform->setDefault('tomagrade_upload', $config->tomagrade_DefaultUseTomax);
            }
        }
    }
}

function get_teacher_codes_from_moodle_ids($teachers, $identifyByEmail) {
    global $DB;

    $connection = new tomagrade_connection;

    $teachersEmailsArray = array();
    $teachersCodesArray = array();
    $tempTeachersCodeArr = array();

    if (empty($teachers)) { return false; }

    $config = get_config('plagiarism_tomagrade');

    $selectedTeachersToShare = $DB->get_records_sql(" select id,email,idnumber,username from {user} where id in ($teachers)");

    foreach($selectedTeachersToShare as $teacher) {
        array_push($teachersEmailsArray, $teacher->email);
        if ($config->tomagrade_DefaultIdentifier_TEACHER == 2) {
            array_push($teachersCodesArray, $teacher->username);
        } else {
            array_push($teachersCodesArray, $teacher->idnumber);
        }
        array_push($tempTeachersCodeArr, '"'.$teacher->idnumber.'"');
    }

    if ($config->tomagrade_DefaultIdentifier_TEACHER == 4) {
        $selectedTeachersToShare2 = $DB->get_records_sql(" select tz,hujiid from huji.userdata where tz in (". implode(",", $tempTeachersCodeArr) .")");

        $teachersCodesArray = array();

        foreach($selectedTeachersToShare2 as $teacher) {
            array_push($teachersCodesArray, $teacher->hujiid);
        }

    }

    $teachersCodesToShare = array();

    if ($identifyByEmail) {
        $postdata = array();
        $postdata['emails'] = $teachersEmailsArray;

        $response = $connection->post_request("GetTeacherIdMoodle", json_encode($postdata));
        $arrayTeachersEmailsAndTeacherCode = $response['Message'];

        foreach($arrayTeachersEmailsAndTeacherCode as $teacher) {
            $teacherCode = $teacher['ExternalTeacherID'];

            array_push($teachersCodesToShare, $teacherCode);
        }

    } else {
        $teachersCodesToShare = $teachersCodesArray;
    }

    return $teachersCodesToShare;
}

function share_teachers($teachers, $teachersToRemove, $identifyByEmail, $ExamIdInTG) {

    if (empty($teachers) && empty($teachersToRemove)) { return false; }

    global $DB;

    $connection = new tomagrade_connection;
    $connection->do_login();

    $errorInshare_teachersync = false;

    $postdata = array();
    $postdata['usersSharedData'] = array();

    if (empty($teachers) == false) {
        $teachersCodesToShare = get_teacher_codes_from_moodle_ids($teachers, $identifyByEmail);
        if ($teachersCodesToShare == false) { return false; }

        foreach($teachersCodesToShare as $teacher) {
            $examinfo = array();
            $examinfo['ExamID'] = $ExamIdInTG;
            $examinfo['TeacherID'] = $teacher;
            $examinfo['ShareType'] = "Share";
            $examinfo['choose'] = "insertNewSharedUser";
            $examinfo['shareCurrentParticipants'] = true;

            array_push($postdata['usersSharedData'], $examinfo);
        }
    }

    if (empty($teachersToRemove) == false) {
        $teachersCodesToShareDelete = get_teacher_codes_from_moodle_ids($teachersToRemove, $identifyByEmail);
        if ($teachersCodesToShareDelete == false) { return false; }

        foreach($teachersCodesToShareDelete as $teacher) {
            $examinfo = array();
            $examinfo['ExamID'] = $ExamIdInTG;
            $examinfo['TeacherID'] = $teacher;
            $examinfo['ShareType'] = "Share";
            $examinfo['choose'] = "1";
            $examinfo['shareCurrentParticipants'] = true;

            array_push($postdata['usersSharedData'], $examinfo);
        }
    }

    if (count($postdata['usersSharedData']) > 0) {
         $response = $connection->post_request("SaveSharedUsers", json_encode($postdata));

        if (isset($response['NumInsertRows']) || isset($response['NumUpdatedRows']) || isset($response['NumDeletedRows'])) {
            return true;
        }

    }

    return false;

}

function is_hidden_grades($cmid) {
    global $DB;
    $current = $DB->get_record('grade_items', array('id' => $cmid));
    if ($current){
        if($current->hidden == "1"){
            return true;
        }
    }
    return false;
}

function tomagrade_set_instance_config($cmid, $data) {

    global $DB;
    $current = $DB->get_record('plagiarism_tomagrade_config', array('cm' => $cmid));

    if ($current) {
        $data->id = $current->id;
        $DB->update_record('plagiarism_tomagrade_config', $data);
    } else {
        $data->cm = $cmid;
        $DB->insert_record('plagiarism_tomagrade_config', $data);
    }
}

function calc_exam_id_in_tg($cmid, $idmatchontg) {
    global $DB;
    $examIDinTG = "";

    $isExam = false;
    if (isset($idmatchontg) && $idmatchontg != '0' && $idmatchontg != '' && is_null($idmatchontg) == false) {
        $isExam = true;
    }

    if ($isExam == false) {
        $uniqid = uniqid();
        $examIDinTG = "Assign$cmid-$uniqid";

    } else {
        $examIDinTG = $idmatchontg;
    }

    return $examIDinTG;
}

function is_exam_exists_in_tg($ExamID) {
    $connection = new tomagrade_connection;

    $isExamExistsRequest = $connection->get_request("MoodleGetExamDetails", "/$ExamID");
    $responseDecoded = json_decode($isExamExistsRequest);

    if (isset($responseDecoded->Response) == true && isset($responseDecoded->GetExamDetail->Exam_Name) == false) {
        // Exam 100% not exists.
        return 0;
    } else if (isset($responseDecoded->Response) == false) {
        // Tomagrade server is unavilable right now.
        return -1;
    }

    // Exam exists.
    return 1;
}

function tomagrade_get_instance_config($cmid) {
    global $DB;

    if ($config = $DB->get_record('plagiarism_tomagrade_config', array('cm' => $cmid))) {
        $config->new = false;
        return $config;
    }
    $config = get_config('plagiarism_tomagrade');

    $default = new stdClass();
    $default->upload = $config->tomagrade_DefaultUseTomax;
    $default->complete = 0;
    $default->new = true;
    return $default;
}

function new_event_file_uploaded($eventdata) {
    global $DB;
    $result = true;

    if (check_enabled()) {
        $eventdata = $eventdata->get_data();

        $matalaID = $eventdata['contextinstanceid'];

        $filePathNameHash = array_pop($eventdata['other']['pathnamehashes']);
        $fs = get_file_storage();
        $file = $fs->get_file_by_hash($filePathNameHash);
        if ($file == false){ // File doesn't exist.
            return;
        }
        $assign_submission = $DB->get_record('assign_submission', array('id' => $file->get_itemid())); // Get sumbmitted ID.

       $mimetype = $file->get_mimetype();

        $arr = explode("/", $mimetype);
        $ext = "";
        if (count($arr) == 2) {
            if (isset($arr[1])) {
                $ext = strtolower($arr[1]);
            }
        }

        if (plagiarism_plugin_tomagrade::check_if_good_file($ext) != false || plagiarism_plugin_tomagrade::check_if_good_file(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) != false) {
            $data = new stdClass();
            $data->cmid = $eventdata["contextinstanceid"];
            $data->filehash = $file->get_pathnamehash();
            $data->status = 0;
            $data->updatestatus = 1;
            if ($assign_submission->groupid != 0) {
                $group = $DB->get_record('groups', array('id' => $assign_submission->groupid));
                $data->groupid = $assign_submission->groupid;
                $current = $DB->get_record('plagiarism_tomagrade', array('cmid' => $eventdata["contextinstanceid"], 'groupid' => $assign_submission->groupid));
            } else {
                $data->userid = (isset($assign_submission->userid) && $assign_submission->userid != 0) ? $assign_submission->userid : $eventdata["userid"];
                $current = $DB->get_record('plagiarism_tomagrade', array('cmid' => $eventdata["contextinstanceid"], 'userid' => $assign_submission->userid));
            }
            if ($current) {
                $data->id = $current->id;
                $DB->update_record('plagiarism_tomagrade', $data);
            } else {
                $DB->insert_record('plagiarism_tomagrade', $data);
            }
            $DB->execute('UPDATE {plagiarism_tomagrade_config} SET complete = "0" WHERE cm = "' . $eventdata["contextinstanceid"] . '"');
            // Check completed.
            return $result;
        } else {
            $checkIfTomaGradeActive = $DB->get_record_sql('SELECT upload FROM {plagiarism_tomagrade_config} WHERE cm = ?', array($matalaID));

            $printErrMsg = true;
            if (isset($checkIfTomaGradeActive) == false || $checkIfTomaGradeActive == false) {
                $printErrMsg = false;
            }
            if (isset($checkIfTomaGradeActive) && isset($checkIfTomaGradeActive->upload)) {
                if ($checkIfTomaGradeActive->upload == "0") {
                    $printErrMsg = false;
                }
            }

            if ($printErrMsg) {
             \core\notification::error("The file you have submitted has been uploaded but cannot be checked by the teacher.The files that will be able to be checked with the teacher are: doc, docx, pdf, ttp, ttpx, xls, xlsx, rtf, ppt, jpeg, jpg, png.");
            }
        }
    }
}



function tomagrade_log($data) {
    global $CFG;

}

function set_grade($cmid, $userid, $grade, $grader)
{
    global $DB;
    $instance = $DB->get_record('course_modules', array('id' => $cmid));

    // Temp grade.
    $dbrec = $DB->get_record("assign_grades", array("assignment" => $instance->instance, "userid" => $userid));
    if (empty($dbrec)) {

        $data = new stdClass();
        $data->grade = $grade;
        $data->assignment = $instance->instance;
        $data->userid = $userid;
        $data->timecreated = time();
        $data->timemodified = time();
        $data->grader = $grader;
        $DB->insert_record('assign_grades', $data);

    } else {
        $DB->execute("UPDATE {assign_grades} SET   grade = :grade WHERE assignment = :instance AND userid = :userid", array('grade' => $grade, 'instance' => $instance->instance, 'userid' => $userid));
    }

    $matalaSettings = $DB->get_record("assign", array("id" => $instance->instance));
    if ($matalaSettings->blindmarking == "1" && $matalaSettings->revealidentities == "0") {
        // Do not set final grade because this is an anonymous exam and the lecturer have not revealed grades yet.
        return;
    }
    // Final grade.
    $gradeObj = array();
    $gradeObj['userid'] = $userid;
    $gradeObj['rawgrade'] = $grade;
    grade_update('mod/assign', $instance->course, 'mod', 'assign', $instance->instance, 0, $gradeObj);

}



function reset_main_grades($cmid) {
    global $DB;
    $instance = get_instance_id($cmid);
    $gradeid = get_grade_id($instance);
    $DB->execute('UPDATE {grade_grades} SET finalgrade = null WHERE itemid = ?', array($gradeid));
}

function get_instance_id($cmid) {
    global $DB;
    $instance = $DB->get_record('course_modules', array('id' => $cmid));
    return $instance->instance;
}

function get_grade_id($instance) {
    global $DB;
    $result = $DB->get_record_sql('SELECT id FROM {grade_items} WHERE iteminstance = ?', array($instance));
    return $result->id;
}


class tomagrade_connection
{

    const STATUS_NOT_STARTED = 0;
    const STATUS_WAITING = 1;
    const STATUS_ONGOING = 2;
    const STATUS_FINISHED = 3;
    const STATUS_QUEUED = 4;
    const STATUS_FAILED = 1000;
    const STATUS_FAILED_FILETYPE = 1001;
    const STATUS_FAILED_UNKNOWN = 1002;
    const STATUS_FAILED_OPTOUT = 1003;
    const STATUS_FAILED_CONNECTION = 1004;

    const REPORT_STATS = 0;
    const REPORT_LINKS = 1;
    const REPORT_SOURCES = 2;
    const REPORT_DOCX = 3;
    const REPORT_HTML = 4;
    const REPORT_MATCHES = 5;
    const REPORT_PS = 6;
    const REPORT_RESERVED = 7;
    const REPORT_PDFHTML = 8;
    const REPORT_PDFREPORT = 9;
    const REPORT_HIGHLIGHT = 25;
    const REPORT_GETSOURCE = 26;

    const SUBMIT_OK = 0;
    const SUBMIT_UNSUPPORTED = 1;
    const SUBMIT_OPTOUT = 2;

    protected $config;
    protected $username = -1;
    protected $nondisclosure = false;

    function __construct() {
        $this->config = get_config('plagiarism_tomagrade');
    }

    public function do_login() {
        global $CFG;

        return 'success';
    }

    function post_request($method, $postdata, $dontDecode=false, $parameters = "") {
        global $CFG;
        $params = null;
        $config = $this->config;
        tomagrade_log("================== post $method to $config->tomagrade_server ====================");
        if ($method !== "DoLogin") {
            $params =  "TOKEN/" . $config->tomagrade_username;
        }
        $url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/Server/php/WS.php/$method/" . $params . $parameters;
        tomagrade_log("url : " . $url);
        tomagrade_log("postdata : " . json_encode($postdata));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postdata,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array("cache-control: no-cache", "x-apikey: $config->tomagrade_password", "x-userid: $config->tomagrade_username")

        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        tomagrade_log("response : " . json_encode($response));
        tomagrade_log("================== end post $method to $config->tomagrade_server ====================");

        if ($dontDecode) {
            return $response;
        }

        return json_decode($response, true);
    }



    function get_request($method, $getdata) {
        global $CFG;
        $params = null;
        $config = $this->config;
        tomagrade_log("================== get $method to $config->tomagrade_server ====================");
        $params =  "TOKEN/" . $config->tomagrade_username;
        $url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/Server/php/WS.php/$method/" . $params . $getdata;
        tomagrade_log("url : " . $url);
        tomagrade_log("getdata : " . $getdata);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array("cache-control: no-cache", "x-apikey: $config->tomagrade_password", "x-userid: $config->tomagrade_username")
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        tomagrade_log("response : " . json_encode($response));
        tomagrade_log("================== get $method to $config->tomagrade_server ====================");
        return $response;
    }





    public function teacher_login($id) {
        $config = $this->config;
        $inforamtion = plagiarism_plugin_tomagrade::get_teacher_identifier($id);
        $postdata = "{\"$inforamtion->identify\":\"$inforamtion->data\"}";
        $response_post = $this->post_request("DoLogin", $postdata);
        return $response_post;
    }

    public function get_teacher_code_from_email($email)
    {
        global $CFG;
        $config = $this->config;
        $response = $this->get_request("GetTeacherIdMoodle", "/Email/$email");
        $response = json_decode($response);
        return $response->Message;
    }




    public function upload_exam($contextid, $row, $sendMail = false) {
        $log = "";
        try {
            $isExam = false;
            $matalaInfo = tomagrade_get_instance_config($row->cmid);
            if (isset($matalaInfo->idmatchontg) && $matalaInfo->idmatchontg != '0' && $matalaInfo->idmatchontg != '' && is_null($matalaInfo->idmatchontg) == false) {
                $isExam = true;
            }

            $doNotSendMail = !$sendMail;

            $cmid = $row->cmid;
            $filehash = $row->filehash;
            $useridtable = $row->userid;
            $groupid = $row->groupid;
            $log .= "Working with cmid=$cmid and $useridtable (or group=$groupid) in addition filehash is: ($filehash).";
            global $DB;
            if (isset($this->token) == false || isset($this->userID) == false) {
                $this->do_login();
            }
            $config = $this->config;
            $fs = get_file_storage();
            $component = 'assignsubmission_file';
            $filearea = 'submission_files';
            $files = $fs->get_area_files($contextid, $component, $filearea);
            $exam_id = $cmid;
            $file = $files[$filehash];
            if(!isset($file) || $file === null ){
                $log .= 'There is no file, returning!';
                return;
            }
            $mainfile = $fs->get_file(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            );
            $curl = curl_init();
            $extensionname = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
            $stdc = new stdClass; // Make the CURL file.
            $stdc->_tmp_file_post_params = array();
            $mainfile->add_to_curl_request($stdc, "Key"); // Function to create it.
            $fields['file'] = $stdc->_tmp_file_post_params["Key"];

            $examIDinTG = $matalaInfo->examid;

            $isExamExist = is_exam_exists_in_tg($examIDinTG);

            if ($isExamExist == -1) {
                $log .= '######### skipped, error in tomagrade or tomagrade is not responding right now ';
                return;
            }else if ($isExamExist == 0) {
                $DB->execute('UPDATE {plagiarism_tomagrade_config} SET upload = 0 WHERE cm = ?', array($cmid));
                $log .= '######### skipped, exam not found in tomagrade. ';
                return;
            }

            if ($config->tomagrade_DefaultIdentifier == 3) {
                $studentThodatZaot = plagiarism_plugin_tomagrade::get_orbit_id($useridtable);
            } else {
                $studentThodatZaot = plagiarism_plugin_tomagrade::get_taodat_zaot($useridtable);
            }

            if ($isExam) {
                if (isset($config->tomagrade_zeroComplete)) {
                    if (is_numeric($config->tomagrade_zeroComplete)) {
                        $zeros = intval($config->tomagrade_zeroComplete);
                        if ($zeros > 0 ) {
                          $studentThodatZaot = plagiarism_plugin_tomagrade::complete_zeroes($studentThodatZaot, $zeros);
                        }
                    }
                }

                $OriginalName = $studentThodatZaot;
            } else {
                if ($groupid != null) {
                    $OriginalName = $this->format_group_name($groupid);
                } else {
                    $userIdentefier = plagiarism_plugin_tomagrade::get_user_identifier($useridtable);

                    $OriginalName = $userIdentefier;
                }

            }

            if (empty($extensionname) == false) {
                $OriginalName = $OriginalName. "." . $extensionname;
             }

            $namefile = uniqid() . "-" . round(microtime(true)) . ".$extensionname"; // Add the identifier.
            $fields['file']->postname = $namefile;
            $fields['file']->mime = $mainfile->get_mimetype();

            $responseDecoded = $this->get_request("RestartExamStatus", "/$examIDinTG");

            $response = json_decode($responseDecoded);
            if ($response->Response == "OK") {
                $DB->execute('UPDATE {plagiarism_tomagrade_config} SET complete = 0 WHERE cm = ?', array($cmid));
                $tempdir = "TempDir_" . time();
                if ($isExam == true) {
                  $url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/libs/fileUploader/uploadManagerZip.php?Exam_ID=" . $examIDinTG . "&TempDirName=" . $tempdir;
                } else {
                    $url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/libs/fileUploader/uploadManagerZip.php?Exam_ID=" . $examIDinTG . "&TempDirName=" . $tempdir;
                }
                  $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data', "x-apikey: $config->tomagrade_password", "x-userid: $config->tomagrade_username"));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $responseDecoded = curl_exec($ch);
                $requestContentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                curl_close($ch);

                $response = json_decode($responseDecoded);
                if ($response->answer == "File transfer completed Success") {
                    // Post upload file.
                    $url = "https://$config->tomagrade_server.tomagrade.com/TomaGrade/Server/php/WS.php/PostUploadFile/TOKEN/" . $config->tomagrade_username . "/" . $namefile;
                    $post = [
                        "fileType" => "AssignZip",
                        "fileName" => $namefile,
                        "examID" => $examIDinTG,
                        "TempDirName" => $tempdir,
                        "isVisible" => true,
                        "ShouldCheckExamID" => false,
                        "source" => "moodle_assign",
                        "doNotSendEmail" => $doNotSendMail,
                        "Files" => [array(
                            "OriginalName" => $OriginalName,
                            "EncryptedName" => $namefile
                        )]
                    ];

                    if ($isExam) {

                        $idMatch = plagiarism_plugin_tomagrade::get_id_match_on_tg();

                        $idMatchStr = "ExamID";
                        if ($idMatch == 1) {
                            $idMatchStr = "CourseID";
                        }

                        $post = [
                            "fileType" => "AssignZip",
                            "fileName" => $namefile,
                            "examID" => $examIDinTG,
                            "TempDirName" => $tempdir,
                            "isVisible" => true,
                            "ShouldCheckExamID" => false,
                            "source" => "moodle_exam",
                            "doNotSendEmail" => $doNotSendMail,
                            "MoodleMode" => $idMatchStr,
                            "MoodleStudentID" => $studentThodatZaot,
                            "Files" => [array(
                                "OriginalName" => $OriginalName,
                                "EncryptedName" => $namefile
                            )]
                        ];
                    }

                    $post = json_encode($post);
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("x-apikey: $config->tomagrade_password", "x-userid: $config->tomagrade_username"));

                    $responseDecoded = curl_exec($ch);
                    curl_close($ch);

                    $response = json_decode($responseDecoded);
                    if ($response->Response == "OK") {
                        if (isset($row->id)) {
                            $DB->execute('UPDATE {plagiarism_tomagrade} SET status = 1, updatestatus = 0 WHERE id = ?', array($row->id));
                        } else {
                            $data = new stdClass();
                            $data->status = 1;
                            $data->updatestatus = 0;
                            $data->userid = $useridtable;
                            $data->cmid = $cmid;
                            $data->filehash = $filehash;
                            $DB->insert_record('plagiarism_tomagrade', $data);
                        }
                    }else{
                        throw new Exception('PostUploadFile Exception is:' . $responseDecoded);
                    }
                } else {
                    throw new Exception('Upload Manager Zip Exception is:' . $responseDecoded);
                }
            } else {
                throw new Exception('RestartExamStatus Exception is:' . $responseDecoded);
            }
        } catch (Exception $e) {
            $log .= "There was a problem.";
           $log .= "$e";
        }
        return $log;
    }

    static public function format_group_name($groupid) {
        global $DB;

        $group = $DB->get_record('groups', array('id' => $groupid));
        return "Group - " . $group->name . " (" . $groupid . ")";
    }





    function get_courses() {
        $response = $this->get_request("GetCourses", "");
        $response = json_decode($response, true);
        return $response;
    }



    function check_course($examIDinTG) {
        global $DB;

        $this->do_login();
        $response = $this->get_request("MoodleGetExamDetails", "/$examIDinTG");
        $response = json_decode($response);

        $cmidExam = str_replace("Assign", "", $examIDinTG);
        if (strpos($cmidExam, '-') !== false) {
            $cmidExam = substr($cmidExam, 0, strpos($cmidExam, "-"));
        }

        $matalaInfo = tomagrade_get_instance_config($cmidExam);
        $grader = 2; // Usually it's the admin.
        if (is_numeric($matalaInfo->username)) {
            $grader = intval($matalaInfo->username);
        }

        if ($response->Response != "Failed") {
            foreach ($response->CourseParticipant as $value) {
                if ($value->ParGrade != "" || isset($value->ParGrade) || $value->ParGradeNoFactor != "" || isset($value->ParGradeNoFactor)) {
                    $current = plagiarism_plugin_tomagrade::get_user_id_by_identifier($value->OriginalFileName);
                    if ($current == false) {
                        $current = plagiarism_plugin_tomagrade::get_user_id_by_group_identifier($value->OriginalFileName);
                    }
                    if ($current != false && $value->ParExamStatus == 2) {
                        foreach ($current as $userID) {
                            if (isset($value->ParGrade)) {
                                set_grade($cmidExam, $userID, $value->ParGrade, $grader);
                            } else {
                                set_grade($cmidExam, $userID, $value->ParGradeNoFactor, $grader);
                            }
                        }
                    }
                }
            }
            if ($response->GetExamDetail->ExamStatus == "3") {
                $newdb = new stdClass();
                $DB->execute('UPDATE {plagiarism_tomagrade_config} SET complete = 2 WHERE cm = ?', array($cmidExam));
            }
        } else {
            // Check if deleted, if so remove.
        }
    }
}
