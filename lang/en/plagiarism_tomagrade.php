<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['studentdisclosuredefault']  = 'All files uploaded will be submitted to a plagiarism detection service';
$string['pluginname'] = 'TomaGrade';
$string['studentdisclosure'] = 'Student Disclosure';
$string['studentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['newexplain'] = 'For more information on this plugin see: ';
$string['new'] = 'New template plagiarism plugin';
$string['usenew'] = 'Enable New';
$string['savedconfigsuccess'] = 'Plagiarism Settings Saved';
$string['tomagradeserver'] = 'TomaGrade domain name:';
$string['tomagradeserver_help'] = 'Set your client subdomain server for the TomaGrade service.';
$string['tomagradeusername'] = 'TomaGrade Username:';
$string['tomagradeusername_help'] = 'Set your administrator account name from TomaGrade.';
$string['tomagradepassword'] = 'TomaGrade Password:';
$string['tomagradepassword_help'] = 'Set your administrator account password from TomaGrade.';
$string['tomagrade:EditTomagradeMainSettings'] = "Edit Tomagrade Main permissions.";
$string['tomagrade_DefaultUseTomax'] = "Set the Default field for Tomax Use";
$string['tomagrade_DefaultShareTomax'] = "Set the default field for sharing with students";
$string['tomagrade_ACL'] = "Allow Tomagrade plugin";
$string['tomagrade_ACL_help'] = "Should use ACL or not to block";
$string['tomagrade_DefaultUseTomax_help'] = "This field is for setting the default field for 'Using Tomagrade' for teachers who will start a moodle excercise.";
$string['tomagrade_DefaultShareTomax_help'] = "This field is for setting the default field for 'Share with students' for teachers who will start a moodle excercise.";
$string['tomagrade_DefaultIdentifier'] = "Set the Default identifier:";
$string['tomagrade_zeroComplete'] = "Number of digits to complete student identifier (Padding zeros):";
$string['tomagrade_DefaultIdentifier_help'] = "Set the default identifier for the TomaGrade system, Please set this variable up ONCE!";
$string['tomagrade_DefaultIdentifier_TEACHER'] = "Set the Default identifier for Teachers";
$string['tomagrade_IDMatchOnTomagrade'] = "ID Match on TomaGrade";
$string['tomagrade_DefaultIdentifier_TEACHER_help'] = "This setting will set the default identefier which uploads the asssignments to the teacher, If you do not use a default ID, please use the email option.";
$string['tomagrade_MatchingDue'] = "Matching Due date for ID Match On TomaGrade(Days +/-)";
$string['tomagrade_AllowOnlyIdMatchOnTG'] = "Allow only ID Match On TomaGrade";
$string['tomagarde_DisplayStudentNameOnTG'] = "Display student name on TomaGrade";
$string['Enable_TomaGrade'] = "Enable TomaGrade";
$string['Reset_the_exam'] = "Return the exam to edit mode in TomaGrade";
$string['ID_Match_On_Tomagrade'] = "ID Match On Tomagrade";
$string['Please_select'] = "Please select";
$string['Irrelevant_regular_assignment'] = "Irrelevant - regular assignment";
$string['No'] = "No";
$string['Start_immediately'] = "Automatic submissions upload";
$string['Start_it_manual'] = "Manually submissions upload";
$string['Click_here'] = "Click here";
$string['TomaGrade_did_not_recognise_any_file'] = "TomaGrade did not recognise any file";
$string['Press_here_to_view_the_graded_exam'] = "Press here to view the graded submission";
$string['Check_with'] = "Check with";
$string['again'] = "again";
$string['Upload_to_TomaGrade'] = "Upload to TomaGrade";
$string['Upload_to_TomaGrade_again'] = "Upload to TomaGrade again";
$string['The_test_has_been_reset'] = "After saving, the exam will return to edit mode on TomaGrade";
$string['assigns_savedInTG'] = "Assign was saved in TG";
$string['Related_TomaGrade_User'] = " Related TomaGrade user";
$string['tomagrade_username_help'] = "The TomaGrade user to whom a task will be related to . Can not be replaced after starting a task examination in TomaGrade";
$string['user_does_not_exists_in_tomagrade'] = "User does not exist in TomaGrade";
$string['tomagrade_userRolesToDisplayRelatedAssign'] = "Authorized rolls for \"Related TomaGrade user\"";
$string['tomagrade_userRolesToDisplayRelatedAssign_help'] = "Authorized rolls for user to whom a task will be related to in TomaGrade";
$string['exam_is_already_exists_and_in_status_gt_zero'] = "For TomaGrade settings the fields below cannot be changed,Related TomaGrade user\nID Match on TomaGrade\nBecause the Exam in TomaGrade is in a examination process";
$string['tomagrade_zeroCompleteTeacher'] = "Number of digits to complete Teacher identifier (Padding zeros):";
$string['tomagrade_zeroCompleteTeacher_help'] = "For Teachers authentication on TomaGrade";
$string['assigns_syncedWithTG'] = "Assigns were synced";
$string['invalid_file_type_for_TomaGrade'] = "Invalid file type for TomaGrade";
$string['tomagrade_userRolesPermissionGradedExam'] = "View permission for graded exam";
$string['tomagrade_shareAddioionalTeachersTitle'] = "Share additional teachers on TomaGrade";
$string['tomagrade_moodleServerID'] = "Moodle Server ID";
$string['tomagrade_currentExamIDonTomaGrade'] = "Current ExamID on TomaGrade";
$string['files_were_deleted'] = "The files have been Deleted and are no longer available for viewing.";
$string['tomagrade'] = "TomaGrade";
$string['tomagrade_createUsers'] = "Add a new user to Tomagrade";
$string['error_during_creating_new_user_in_tomagrade_teacher_code_already_exists'] = "Error during creating new user in TomaGrade, Teacher code already exists for user";
$string['error_during_creating_new_user_in_tomagrade_email_already_exists'] = "Error during creating new user in TomaGrade, email already exists for user";
$string['error_during_creating_new_user_in_tomagrade_missing_params'] = "Error during creating new user in TomaGrade, missing parameters for username ";
$string['error_during_creating_new_user_in_tomagrade'] = "Error during creating new user in TomaGrade";
$string['simester'] = "simester";
$string['moed'] = "moed";
$string['tomagrade_keepBlindMarking'] = "Keep \"Blind marking\" setting on TomaGrade";
$string['tomagrade_DaysDisplayBeforeExamDate'] = "Number of days to display before exam date";
$string['tomagrade_DaysDisplayAfterExamDate'] = "Number of days to display after exam date";
$string['tomagrade_notAllowedToView'] = "Only the student who submitted this work is allowed to view this!";
$string['tomagrade_contactAdmin'] = "There was an error, Please contact a system adminstrator.";
$string['tomagrade_id_email_incorrect'] = "ID number or Email is not identified in TomaGrade, Please contact support";
$string['tomagrade_exam_has_hidden_grades'] = "The grade is currently hidden.";
$string['privacy:metadata'] = "Does not store any personal information in the moodle store";
$string['tomagrade_FieldNameForCourseFiltering'] = "Field name on TomaGrade for courses list filtering";
$string['tomagrade_FieldValueForCourseFiltering'] = "Field value on TomaGrade for courses list filtering";
$string['error_filehash'] = "The provided filehash is not part of this assignment";
$string['well_connected'] = "Your system is well connected!";
$string['connection_auth_error'] = "Please check your APIKey and UserID";
$string['no_open_connection'] = "It seems you do not have an open connection to TomaGrade";
$string['error_deleting_log'] = "we have an issue deleting the log";

