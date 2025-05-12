<?php


require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/config.php");

use block_learningassist\gen_ai;

class block_learningassist_chat extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function chat_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'chattype' => new external_value(PARAM_TEXT, 'Chat Type', VALUE_REQUIRED),
                'prompt' => new external_value(PARAM_TEXT, 'User prompt', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Chat with AI
     * @param int $course_id
     * @param string $message
     * @return string
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     */
    public static function chat($course_id, $chat_type, $prompt)
    {
        global $OUTPUT;

        //Parameter validation
        $params = self::validate_parameters(
            self::chat_parameters(),
            array(
                'courseid' => $course_id,
                'chattype' => $chat_type,
                'prompt' => $prompt
            )
        );

        $system_message =  "\n\n" . '' . get_string($chat_type . '_system_message', 'block_learningassist') . "\n";

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        $response = gen_ai::make_call($system_message, $prompt);

        return $response;
    }

    /**
     * Returns method result value
     * @return external_description
     */
    public static function chat_returns()
    {
        return new external_value(PARAM_RAW, 'Response from AI');
    }

}