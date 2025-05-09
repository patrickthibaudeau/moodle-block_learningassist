<?php
require_once("../../config.php");

use block_learningassist\course;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/blocks/learningassist/test.php', []));
$PAGE->set_title('Testing');
$PAGE->set_heading('Testing');
$PAGE->set_pagelayout('base');
$PAGE->set_context($context);

echo $OUTPUT->header();

course_modules::

echo $OUTPUT->footer();