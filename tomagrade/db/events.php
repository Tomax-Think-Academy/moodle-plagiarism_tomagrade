<?php

$observers = array(
    array(
        'eventname'   => 'assignsubmission_file\event\assessable_uploaded',
        'callback'    => 'new_event_file_uploaded',
        'priority'    => 200,
        'internal'    => false,
        'includefile' => '/plagiarism/tomagrade/lib.php'
    )
);
