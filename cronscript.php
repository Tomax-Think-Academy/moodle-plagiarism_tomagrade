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
 * cronscript.php - A file which runs by a task definition.It syncs the files from Moodle to the TomaGrade system,
 *                  and also reads the grades back.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
mtrace("Define INTERNAL");
global $DB, $CFG;
require_login();
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');

mtrace("Starting the TomaGrade cron");


 $log = "cron job log: ";

function logandprint($msg, &$log) {
    echo $msg;
    echo "\n";

    $log .= "\n" . $msg;
}


if (check_enabled()) {

    $connection = new tomagrade_connection;



    echo ("TomaConnection:");

    $DB->execute(" delete FROM {plagiarism_tomagrade_config} where cm not in ( SELECT id from {course_modules} )");


        // UPDATE RENDERING  - START
        $response = $connection->get_request("GetUnDownloadedCourses", "/assigns");
        $response = json_decode($response, true);
    if (isset($response['Exams'])) {
             $exams = $response['Exams'];
    } else {
        logandprint("error in tomagrade server, GetUnDownloadedCourses did not response", $log);

        $exams = array();
    }



        $moodleassignsarr = array();
    foreach($exams as $exam) {
        if (strpos($exam['ExamID'], '_') !== false) {
             // This is not a moodle assignment!
            continue;
        }
        array_push($moodleassignsarr, $exam['ExamID']);
    }




        $examsCmidsList = "";
        $examsIDsInCurrentMoodleServer = array();
        if (count($moodleassignsarr)>0) {
            $moodleAssignsStr =  "";
            $isFirst = true;
            foreach($moodleassignsarr as $examid) {
                if ($isFirst) {
                    $moodleAssignsStr .= "'".$examid."'";
                    $isFirst = false;
                } else {
                    $moodleAssignsStr .= ",'".$examid."'";
                }
            }

            $examsInThisMoodle = $DB->get_records_sql(" select cm,examid from {plagiarism_tomagrade_config} where examid in ($moodleAssignsStr) ");
            $isFirst = true;
            foreach ($examsInThisMoodle as $key=>$value) {
                if ($isFirst) {
                    $examsCmidsList .= "'".$value->cm."'";
                    $isFirst = false;
                } else {
                    $examsCmidsList .= ",'".$value->cm."'";
                }
                array_push($examsIDsInCurrentMoodleServer,$value->examid);
            }
        }




        if (empty($examsCmidsList) == false) {

                $NotRendered = $DB->execute("
    update {plagiarism_tomagrade}  set finishrender = 1 where id in (  select id from ( select student.id as id  from {plagiarism_tomagrade_config} as config
     inner join {plagiarism_tomagrade} as student on config.cm = student.cmid
     where cmid in ($examsCmidsList) ) as x ) ");

            if ($NotRendered == true) {

                logandprint("all the exams $examsCmidsList has been synced and rendered",$log);

                foreach($examsIDsInCurrentMoodleServer as $exam) {
                    try {


                        $connection->check_course($exam);

                        $res = $connection->get_request("SaveDownloadDate", "/$exam");
                        $res = json_decode($res,true);
                        $result = $res['Response'];

                        if ($result == 'Failed') {
                            logandprint("error in SaveDownloadDate for exam $exam",$log);
                        }

                    } catch (Exception $e) {
                        logandprint('happend in check_course - for ' . $currentCmid . " cmid.",$log);
                        logandprint($e,$log);
                    }
                }
            }
        } else {
            logandprint("there are no exams that rendered since the last sync",$log);
        }
        // #### UPDATE RENDERING  - END

        $data = $DB->get_records_sql("
select * from {plagiarism_tomagrade_config} as config
 inner join {plagiarism_tomagrade} as student on config.cm = student.cmid
 where complete = 0 and upload != 0 and status = 0 and updatestatus = 1 order by cmid");


    // foreach ($data as $key=>$value) {
    //     var_dump($key);
    $keys = array_keys($data);
    foreach(array_keys($keys) as $index){
        $current_key = current($keys); // or $current_key = $keys[$index];
        $value = $data[$current_key]; // or $current_value = $a[$keys[$index]];

        $next_key = next($keys);
        $next_value = $data[$next_key] ?? null; // for php version >= 7.0

        $sendMail = false;
        if (empty($value->share_teachers) == false) {
            if (isset($next_value) == false) {
                $sendMail = true;
            } else if ($value->cmid != $next_value->cmid) {
                $sendMail = true;
            }
        }




        // if ($value->status == 0 || $value->updatestatus == 1) {
        try {
            $context = context_module::instance($value->cm);
        } catch (Exception $e) {
            continue;
        }
        if (empty($context) || $context == null) {

            logandprint("context is empty.. -",$log);
            continue;
        }
        $contextid = $context->id;
        try {
            switch ($value->upload) {
                case plagiarism_plugin_tomagrade::RUN_IMMEDIATLY:
                    // mtrace("Should upload immediately.");
                    logandprint("Should upload immediately.",$log);
                    $tmpLog = $connection->upload_exam($contextid, $value,$sendMail);
                    logandprint($tmpLog,$log);
                    break;
                case plagiarism_plugin_tomagrade::RUN_MANUAL:
                    // mtrace("Should be uploaded manual.");
                    logandprint("Should be uploaded manual.",$log);
                    break;
                case plagiarism_plugin_tomagrade::RUN_AFTER_FIRST_DUE_DATE:
                    // mtrace("Should be uploaded at first due date.");
                    logandprint("Should be uploaded at first due date.",$log);
                    $checkdate = $DB->get_record("event", array('id' => $value->cmid));
                    if ($checkdate->timestart < time()) {
                        $tmpLog = $connection->upload_exam($contextid, $value,$sendMail);
                        logandprint($tmpLog,$log);
                    }
                    break;
            }
        } catch (Exception $e) {
            logandprint("Couldn't Sync Student, Exception:",$log);
            logandprint($e,$log);
        }
        // }
    }



    // $event = \plagiarism_tomagrade\event\assigns_syncedWithTG::create(array(
    //     'context' => context_system::instance(),
    //     'userid' => -1,
    //     'other' => $log
    // ));
    // $event->trigger();



    function resetforDev()
    {
        global $DB, $CFG;
        $data = $DB->get_records("plagiarism_tomagrade");
        foreach ($data as $val) {
            $newdata = new stdClass();
            $newdata->id = $val->id;
            $newdata->status = 0;
            $DB->update_record('plagiarism_tomagrade', $newdata);
        }
    }
}
