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

$connection = new tomagrade_connection;
$res = $connection->get_courses();
if (isset($res)) {
    if ($res["IsTokenActive"] == true) {
        write(get_string('well_connected' , 'plagiarism_tomagrade'));
    } else {
        write(get_string('connection_auth_error' , 'plagiarism_tomagrade'));
    }
} else {
    write(get_string('no_open_connection' , 'plagiarism_tomagrade'));
}
echo ("<script>window.close();</script>");

function write($message) {
    echo ("<script>alert('$message');</script>");
}
