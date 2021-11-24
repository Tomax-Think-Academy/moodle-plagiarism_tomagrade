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
 * upgrade.php - Used to upgrade the database between versions.
 *
 * @package    plagiarism_tomagrade
 * @subpackage plagiarism
 * @copyright  2021 Tomax ltd <roy@tomax.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_plagiarism_tomagrade_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020220214) {

        // Define field idmatchontg to be added to plagiarism_tomagrade_config.
        $table = new xmldb_table('plagiarism_tomagrade_config');
        $field = new xmldb_field('idmatchontg', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'show_report_to_students');

        // Conditionally launch add field idmatchontg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tomagrade savepoint reached.
        upgrade_plugin_savepoint(true, 2020220214, 'plagiarism', 'tomagrade');
    }

    if ($oldversion < 2020220268) {
        // Define field idmatchontg to be added to plagiarism_tomagrade_config.
        $table = new xmldb_table('plagiarism_tomagrade_config');
        $field = new xmldb_field('share_teachers', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'idmatchontg');

        // Conditionally launch add field idmatchontg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tomagrade savepoint reached.
        upgrade_plugin_savepoint(true, 2020220268, 'plagiarism', 'tomagrade');
    }

    if ($oldversion < 2020220272) {
        // Define field idmatchontg to be added to plagiarism_tomagrade_config.
        $table = new xmldb_table('plagiarism_tomagrade_config');
        $field = new xmldb_field('examid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'share_teachers');

        // Conditionally launch add field idmatchontg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $records = $DB->get_records_sql(" select * from {plagiarism_tomagrade_config}  ");
        foreach($records as $record) {
            if ($record->idmatchontg == "0") {
                 $update = " update {plagiarism_tomagrade_config} set examid = 'Assign$record->cm' where id = $record->id";
            } else {
                $update = " update {plagiarism_tomagrade_config} set examid = '$record->idmatchontg' where id = $record->id";
            }
            $DB->execute($update);
        }
        // Tomagrade savepoint reached.
        upgrade_plugin_savepoint(true, 2020220272, 'plagiarism', 'tomagrade');
    }

    if ($oldversion < 2020220279) {

        $records = $DB->get_records_sql(" select * from {plagiarism_tomagrade_config}  ");

        foreach($records as $record) {
            if (strpos($record->username, '@') !== false) {
                $update = " update {plagiarism_tomagrade_config} set username = ( select id from {user}
                 where email = '$record->username' limit 1 ) where id = $record->id";
                $DB->execute($update);
            }
        }
         // Tomagrade savepoint reached.
         upgrade_plugin_savepoint(true, 2020220279, 'plagiarism', 'tomagrade');

    }

    if ($oldversion < 2020220301) {

        $tomagradeenabled = get_config('plagiarism', 'tomagrade_use');
        if (!empty($tomagradeenabled)) {
            set_config('enabled', $tomagradeenabled, 'plagiarism_tomagrade');
            unset_config('tomagrade_use', 'plagiarism');
        }

        upgrade_plugin_savepoint(true, 2020220301, 'plagiarism', 'tomagrade');
    }



    return true;
}
