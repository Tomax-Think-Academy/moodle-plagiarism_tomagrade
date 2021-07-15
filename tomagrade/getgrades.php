<?php

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/tomagrade/lib.php');
require_once($CFG->dirroot.'/plagiarism/tomagrade/plagiarism_form.php');


require_login();

defined('MOODLE_INTERNAL') || die();

 require_login();
    if ($CFG->version < 2011120100) {
        $context = get_context_instance(CONTEXT_SYSTEM);
    } else {
        $context = context_system::instance();
    }

echo($USER->id);
