<?php
require_once("../../config.php");

use block_learningassist\course_modules;
use block_learningassist\gen_ai;

global $CFG, $OUTPUT, $USER, $PAGE, $DB;

$course_id = required_param('courseid', PARAM_INT);
$chat_type = required_param('chattype', PARAM_TEXT);
$chat_id = required_param('chatid', PARAM_TEXT);
$cmid = required_param('cmid', PARAM_INT);

require_login($course_id, false);

$chat_type_name = get_string($chat_type, 'block_learningassist');

$context = context_course::instance($course_id);

// Getting Module content.
$content = course_modules::get_module_content($cmid);
$chat_header = course_modules::get_instance_name($cmid);

// The system message and promtp must be different based on the type of chat.
if ($chat_type == 'tutor') {
    $system_message = get_string($chat_type . '_system_message', 'block_learningassist') . "\n";
    $system_message .= $content;
    $prompt = get_string($chat_type . '_prompt', 'block_learningassist');
} else if ($chat_type == 'quiz') {
    $system_message = get_string($chat_type . '_system_message', 'block_learningassist');
    $prompt = $content . "\n\n" . get_string($chat_type . '_prompt', 'block_learningassist');
}


$messages = [
    [
        'is_human' => false,
        'message' => gen_ai::make_call($prompt, array()),
    ]
];

// add the response to the history
gen_ai::add_to_history($chat_id, 'system', $system_message);
gen_ai::add_to_history($chat_id, 'assistant', $messages[0]['message']);

$data = [
    'courseid' => $course_id,
    'chattype' => $chat_type,
    'chatheader' => $chat_header,
    'messages' => $messages,
    'chatid' => $chat_id,
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