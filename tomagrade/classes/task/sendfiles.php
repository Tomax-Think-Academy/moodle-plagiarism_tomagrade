<?php

namespace plagiarism_tomagrade\task;

class sendfiles extends \core\task\scheduled_task {
    public function get_name() {
        return 'sendfiles';
    }

    public function execute() {
      global $CFG;
      require_once($CFG->dirroot.'/plagiarism/tomagrade/cronscript.php');
    }
}
