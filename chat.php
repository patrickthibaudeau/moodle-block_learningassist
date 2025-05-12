<?php
require_once("../../config.php");

use block_learningassist\gen_ai;
use block_learningassist\course_modules;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;

$course_id = required_param('courseid', PARAM_INT);
$chat_type = required_param('chattype', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);

require_login($course_id, false);

$chat_type_name = get_string($chat_type, 'block_learningassist');

$context = context_course::instance($course_id);

// Getting Module content.
$content = course_modules::get_module_content($cmid);
$chat_header = course_modules::get_instance_name($cmid);

$system_message = get_string($chat_type . '_system_message', 'block_learningassist') . "\n";
$system_message .= $content;
$prompt = get_string($chat_type . '_prompt', 'block_learningassist') ;

$messages = [
    [
        'is_human' => false,
        'message' => gen_ai::make_call($system_message, $prompt),
    ]
];

$data = [
    'courseid' => $course_id,
    'chattype' => $chat_type,
    'chatheader' => $chat_header,
    'messages' => $messages,
];

$PAGE->set_url(new moodle_url('/blocks/learningassist/chat.php', []));
$PAGE->set_title(get_string('learning_assist_chat', 'block_learningassist'));
$PAGE->set_heading($chat_type_name);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('block_learningassist/chat', 'sendMessage');

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('block_learningassist/chat_interface', $data);

echo $OUTPUT->footer();