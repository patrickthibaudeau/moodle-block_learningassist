<?php

namespace block_learningassist;

abstract class gen_ai
{
    const UNIVERSITY = 1;
    const COLLEGE = 2;
    const HIGH_SCHOOL = 3;
    const ELEMENTARY = 4;

    /**
     * This function uses the built in Moodle AI providers and placements
     * @param $context \stdClass
     * @param $prompt string
     * @param bool $decode bool Wheter to JSON decode the response or not
     * @return mixed
     */
    public static function make_call($context, $prompt, $lang = 'en', $decode = false)
    {
        global $USER;

        // Always return he response in the language of the course
        $prompt .= "\n\nYou must return the response in the language based on this language code: $lang.\n\n";

        $action = new \core_ai\aiactions\generate_text(
            contextid: $context->id,
            userid: $USER->id,
            prompttext: $prompt,
        );

// Send the action to the AI manager.
        $manager = \core\di::get(\core_ai\manager::class);
        $response = $manager->process_action($action);

        if ($decode) {
            return json_decode($response->get_response_data()['generatedcontent']);
        } else {
            return $response->get_response_data()['generatedcontent'];
        }
    }
}