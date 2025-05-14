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
    public static function chat_parameters(): external_function_parameters
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
                'chattype' => new external_value(PARAM_TEXT, 'Chat Type', VALUE_REQUIRED),
                'prompt' => new external_value(PARAM_TEXT, 'User prompt', VALUE_REQUIRED),
                'chatid' => new external_value(PARAM_TEXT, 'Chat ID', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Chat with AI
     * @param int $course_id
     * @param $chat_type
     * @param $prompt
     * @return string
     * @throws \core_external\restricted_context_exception
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws Exception
     */
    public static function chat(int $course_id, $chat_type, $prompt, $chatid): string
    {
        self::validate_parameters(
            self::chat_parameters(),
            [
                'courseid' => $course_id,
                'chattype' => $chat_type,
                'prompt' => $prompt,
                'chatid' => $chatid
            ]
        );

        $system_message = "\n\n" . get_string($chat_type . '_system_message', 'block_learningassist') . "\n";

        // Validate context
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Get or create history
        $history = gen_ai::get_history($chatid);
        if (empty($history)) {
            $history[] = ['role' => 'system', 'content' => $system_message];
            gen_ai::add_to_history($chatid, 'system', $system_message);
        }

        file_put_contents('/var/www/moodledata/temp/history.txt', print_r($history, true));
        // Add the user message
        gen_ai::add_to_history($chatid, 'user', $prompt);

        // Append full history
        $response = gen_ai::make_call($prompt, $history);

        // Save assistant reply to history
        gen_ai::add_to_history($chatid, 'assistant', $response);

        return $response;
    }

    /**
     * Returns method result value
     * @return external_value|external_description
     */
    public static function chat_returns(): external_value|external_description
    {
        return new external_value(PARAM_RAW, 'Response from AI');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function clear_history_parameters(): external_function_parameters
    {
        return new external_function_parameters(
            array(
                'chatid' => new external_value(PARAM_TEXT, 'Chat ID', VALUE_REQUIRED),
            )
        );
    }

    /**
     *
     * Deletes the history associated with the given chat ID.
     *
     * @param string $chatid The ID of the chat whose history will be deleted.
     * @return bool True if the history was successfully deleted.
     */
    public static function clear_history($chatid): bool
    {
        self::validate_parameters(
            self::clear_history_parameters(),
            [
                'chatid' => $chatid,
            ]
        );

        // Validate context
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        // Clear history
        gen_ai::clear_history($chatid);

        return true;
    }

    /**
     * Returns method result value
     * @return external_value
     */
    public static function clear_history_returns(): external_value
    {
        return new external_value(PARAM_BOOL, 'True or false');
    }

}