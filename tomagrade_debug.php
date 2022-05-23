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
 * checkConnection.php - Checks the connection using ApiKeys
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot . '/plagiarism/tomagrade/debug_form.php');
require_login();

global $DB, $CFG;$PAGE;
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Form name');
$PAGE->set_heading('Form name');

$mform = new debug_form();

if ($fromform = $mform->get_data()) {
    echo("<h3><u> tomagrade_use values</u></h3><br>");
    check_tomagrade_use();

    echo("<br><br><h3><u> assignment values for ". $fromform->assignment_id ."</u></h3><br>");
    check_exam($fromform->assignment_id);

} else {
    $mform->display();
}



function check_exam($assignid) {
    $sql = "select * from {plagiarism_tomagrade_config} inner join {plagiarism_tomagrade}
   on {plagiarism_tomagrade_config}.cm = {plagiarism_tomagrade}.cmid  where {plagiarism_tomagrade_config}.examid = '".$assignid."'";

    global $DB;
    $records = $DB->get_records_sql($sql);
    $table = "<table border='1'><tr>
    <th>cm</th>
    <th>examid</th>
    <th>username</th>
    <th>upload</th>
    <th>complete</th>
    <th>share_teachers</th>
    <th>userid</th>
    <th>groupid</th>
    <th>status</th>
    <th>updatestatus</th>
    <th>finishrender</th>
    <th>filehash</th>
    </tr>";

    foreach ($records as $record) {
        $table = $table."<tr>";
        $table = $table."<td>".$record->cm."</td>";
        $table = $table."<td>".$record->examid."</td>";
        $table = $table."<td>".$record->username."</td>";
        $table = $table."<td>".$record->upload."</td>";
        $table = $table."<td>".$record->complete."</td>";
        $table = $table."<td>".$record->share_teachers."</td>";
        $table = $table."<td>".$record->userid."</td>";
        $table = $table."<td>".$record->groupid."</td>";
        $table = $table."<td>".$record->status."</td>";
        $table = $table."<td>".$record->updatestatus."</td>";
        $table = $table."<td>".$record->finishrender."</td>";
        $table = $table."<td>".$record->filehash."</td>";
        $table = $table."</tr>";
    }
    $table = $table."</table>";
    echo($table);
}


function check_tomagrade_use() {
    global $DB;
    $records = $DB->get_records_sql("SELECT * FROM {config_plugins} where name LIKE 'tomagrade_use'");
    $table = "<table border='1'><tr>
    <th>id</th>
    <th>Plugin</th>
    <th>Name</th>
    <th>Value</th>
    </tr>";

    foreach ($records as $record) {
        $id = $record->id;
        $plugin = $record->plugin;
        $name = $record->name;
        $value = $record->value;

        $table = $table."<tr>";
        $table = $table."<td>".$id."</td>";
        $table = $table."<td>".$plugin."</td>";
        $table = $table."<td>".$name."</td>";
        $table = $table."<td>".$value."</td>";
        $table = $table."</tr>";
    }
    $table = $table."</table>";
    echo($table);

}

