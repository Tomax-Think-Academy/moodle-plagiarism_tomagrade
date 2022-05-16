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
require_once($CFG->dirroot . '/plagiarism/tomagrade/plagiarism_form.php');
require_login();

global $DB, $CFG;

$records = $DB->get_records_sql("SELECT * FROM {config_plugins} where name LIKE 'tomagrade_use'");
$plagiarismusepluginname = 'plagiarism';
$foundplagiarismuse = false;

$tomagradeusepluginname = 'plagiarism_tomagrade';
$foundtomagradeuse = false;
foreach ($records as $record) {
    $newdata = new stdClass();
    $newdata->id = $record->id;
    $newdata->plugin = $record->plugin;
    $newdata->name = $record->name;
    $newdata->value = 1;
    echo ("updating ".$record->plugin." record...<br>");
    if ($record->plugin == $tomagradeusepluginname) {
        $foundtomagradeuse = true;
    }
    if ($record->plugin == $plagiarismusepluginname) {
        $foundplagiarismuse = true;
    }

    $DB->update_record('config_plugins', $newdata);
}
if ($foundplagiarismuse == false ) {
    echo ($plagiarismusepluginname." record is missing inserting ... <br>");
    $newdata = new stdClass();
    $newdata->plugin = $plagiarismusepluginname;
    $newdata->name = 'tomagrade_use';
    $newdata->value = 1;
    $DB->insert_record('config_plugins', $newdata);
}
if ($foundtomagradeuse == false ) {
    echo ($tomagradeusepluginname." record is missing inserting ... <br>");
    $newdata = new stdClass();
    $newdata->plugin = $tomagradeusepluginname;
    $newdata->name = 'tomagrade_use';
    $newdata->value = 1;
    $DB->insert_record('config_plugins', $newdata);
}
echo ("Done, please recheck DB and go to Site administration-> Notifications and press on check for updates.");
