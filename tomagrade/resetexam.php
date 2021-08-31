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
 * resetexam.php - Will be used to reset the exam status in TomaGrade system.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il> 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
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
    reset_main_grades($cmid);
}
echo "<script>window.close();</script>";