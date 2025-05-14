<?php

/**
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/config.php");

use block_learningassist\course_modules;

class block_learningassist_course_modules extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function display_modules_parameters()
    {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course Id', VALUE_REQUIRED),
                'chattype' => new external_value(PARAM_TEXT, 'Chat Type', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Dispalys course modules
     * @param int $course_id
     * @return bool
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws restricted_context_exception
     * @throws \core\exception\moodle_exception
     */
    public static function display_modules($course_id, $chat_type)
    {
        global $OUTPUT;

        //Parameter validation
        $params = self::validate_parameters(
            self::display_modules_parameters(),
            array(
                'courseid' => $course_id,
                'chattype' => $chat_type
            )
        );

        //Context validation
        $context = \context_course::instance($course_id);
        self::validate_context($context);

        $course_modules = course_modules::get_course_modules($course_id);
        $course_modules->chat_type = $chat_type;
        $course_modules->chatid = self::generateChatId();
        return $OUTPUT->render_from_template('block_learningassist/course_modules', $course_modules);
    }

    private static function generateChatId(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    /**
     * Returns method result value
     * @return external_description
     */
    public static function display_modules_returns()
    {
        return new external_value(PARAM_RAW, 'HTML of course modules');
    }
}