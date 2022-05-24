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
$string['Enable_TomaGrade'] = "בדיקה במערכת TomaGrade";
$string['Reset_the_exam'] = "החזרת המבחן למצב עריכה ב TomaGrade";
$string['ID_Match_On_Tomagrade'] = "העלאה לרשומה קיימת ב TomaGrade";
$string['Please_select'] = "אנא בחר";
$string['Irrelevant_regular_assignment'] = "לא רלוונטי - מטלה רגילה";
$string['No'] = "לא";
$string['Start_immediately'] = "העלאה אוטומטית של הגשות";
$string['Start_it_manual'] = "העלאה ידנית של הגשות";
$string['Click_here'] = "לחץ כאן";
$string['TomaGrade_did_not_recognise_any_file'] = "לא זוהה קובץ עבור TomaGrade";
$string['Press_here_to_view_the_graded_exam'] = "לחץ כאן לצפיה בהגשה הבדוקה";
$string['Check_with'] = "עבור לבדיקה";
$string['Upload_to_TomaGrade_again'] = "העלאה חוזרת ל TomaGrade";
$string['Upload_to_TomaGrade'] = "העלאה ל TomaGrade";
$string['The_test_has_been_reset'] = "לאחר שמירה המבחן יחזור למצב עריכה ב TomaGrade";
$string['assigns_savedInTG'] = "Assign was Saved in TG";
$string['Related_TomaGrade_User'] = "המשתמש ב TomaGrade עבורו המטלה תשוייך";
$string['tomagrade_username_help'] = "המשתמש ב TomaGrade אשר המטלה תשוייך עבורו. לא ניתן להחליף לאחר תחילת בדיקה של מטלה ב TomaGrade";
$string['user_does_not_exists_in_tomagrade'] = "המשתמש לא קיים ב TomaGrade";
$string['tomagrade_userRolesToDisplayRelatedAssign'] = "תפקידים מורשים לשיוך משתמש";
$string['tomagrade_userRolesToDisplayRelatedAssign_help'] = "תפקידים מורשים לשיוך משתמש אליו המטלה תשוייך בתומהגרייד";
$string['exam_is_already_exists_and_in_status_gt_zero'] = "שים לב: עבור הגדרות TomaGrade לא ניתן לשנות את השדות להלן, המשתמש ב TomaGrade עבורו המטלה תשוייך העלאה לרשומה קיימת ב TomaGradeמכיוון שהמבחן ב TomaGrade בתהליך בדיקה";
$string['tomagrade_zeroCompleteTeacher'] = "Number of digits to complete Teacher identifier (Padding zeros):";
$string['tomagrade_zeroCompleteTeacher_help'] = "For Teachers authentication on TomaGrade";
$string['assigns_syncedWithTG'] = "Assigns were synced";
$string['invalid_file_type_for_TomaGrade'] = "סוג קובץ לא תקין עבור TomaGrade";
$string['tomagrade_userRolesPermissionGradedExam'] = "View permission for graded exam";
$string['tomagrade_shareAddioionalTeachersTitle'] = "שיתוף בודקים נוספים ב TomaGrade";
$string['tomagrade_moodleServerID'] = "Moodle Server ID";
$string['tomagrade_currentExamIDonTomaGrade'] = "מזהה מבחן נוכחי ב TomaGrade";
$string['files_were_deleted'] = "הקבצים נמחקו ולא זמינים יותר לצפיה.";
$string['tomagrade'] = "TomaGrade";
$string['tomagrade_createUsers'] = "Add a new user to Tomagrade";
$string['error_during_creating_new_user_in_tomagrade_teacher_code_already_exists'] = "שגיאה במהלך יצירת משתמש בתומגרייד, קוד המרצה כבר קיים. שם משתמש: ";
$string['error_during_creating_new_user_in_tomagrade_email_already_exists'] = "שגיאה במהלך יצירת משתמש בתומגרייד, האימייל כבר קיים. שם משתמש: ";
$string['error_during_creating_new_user_in_tomagrade_missing_params'] = "שגיאה במהלך יצירת משתמש בתומגרייד, חסרים שדות חובה. שם משתמש: ";
$string['error_during_creating_new_user_in_tomagrade'] = "שגיאה במהלך יצירת משתמשים";
$string['simester'] = "סמסטר";
$string['moed'] = "מועד";
$string['tomagrade_keepBlindMarking'] = "Keep \"Blind marking\" setting on TomaGrade";
$string['tomagrade_DaysDisplayBeforeExamDate'] = "Number of days to display before exam date";
$string['tomagrade_DaysDisplayAfterExamDate'] = "Number of days to display after exam date";
$string['tomagrade_notAllowedToView'] = "רק הסטודנט יוכל לראות את המטלה הזו.";
$string['tomagrade_contactAdmin'] = "הייתה שגיאה, יש ליצור קשר עם אחראי המערכת.";
$string['tomagrade_id_email_incorrect'] = "המזהה או האימייל לא נמצאו במערכת ה TomaGrade.";
$string['tomagrade_exam_has_hidden_grades'] = "הציון כרגע מוסתר.";
$string['privacy:metadata'] = "לא שומרת שום מידע פרטי על המשתמשים.";
$string['tomagrade_FieldNameForCourseFiltering'] = "שם השדה ב TomaGrade עבור סינון רשימת קורסים ";
$string['tomagrade_FieldValueForCourseFiltering'] = "ערך השדה ב TomaGrade עבור סינון רשימת קורסים";
$string['error_filehash'] = "הקובץ הנבחר לא שייך למשימה";
$string['well_connected'] = "המערכת מחוברת!";
$string['connection_auth_error'] = "טעות בפרטי ההזדהות";
$string['no_open_connection'] = "לא קיים חיבור ";
$string['error_deleting_log'] = "שגיאה במחיקת הלוג";
