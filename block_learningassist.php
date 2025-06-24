<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block learningassist is defined here.
 *
 * @package     block_learningassist
 * @copyright   2025 <patrick.thibaudeau@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_ai_assistant\cria;
use block_learningassist\task\course_module_trainer;

class block_learningassist extends block_base
{

    /**
     * Initializes class member variables.
     * @throws coding_exception
     */
    public function init(): void
    {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_learningassist');
    }

    /**
     * Returns the block contents.
     *
     * @return string|stdClass The block contents.
     * @throws \core\exception\moodle_exception
     */
    public function get_content(): string|stdClass
    {
        global $OUTPUT, $USE, $DB, $CFG, $USER;

        // Because this block is dependent on the AI Assistant block, we need to ensure that the
        // bot either exists or is created.
        $ai_assistant_config = get_config('block_ai_assistant');

        $availability = cria::get_availability();
        if ($availability->exception == 'success') {
            if (!$course_record = $DB->get_record(
                'block_aia_settings',
                array(
                    'courseid' => $this->page->course->id
                )
            )) {
                $record = new stdClass();
                $record->courseid = $this->page->course->id;
                $record->blockid = $this->instance->id;
                $record->bot_name = cria::create_bot_instance($this->page->course->id);
                $record->no_context_message = $ai_assistant_config->no_context_message;
                $record->subtitle = $ai_assistant_config->subtitle;
                $record->welcome_message = $ai_assistant_config->welcome_message;
                $record->lang = $ai_assistant_config->default_language;
                $record->published = 0;
                $record->usermodified = $USER->id;
                $record->timecreated = time();
                $record->timemodified = time();
                $DB->insert_record('block_aia_settings', $record);
                $small_talk = cria::create_small_talk_questions($this->page->course->id);
                $course_record = $DB->get_record(
                    'block_aia_settings',
                    array(
                        'courseid' => $this->page->course->id
                    )
                );
            }
        } else {
            $course_record = new stdClass();
            $course_record->courseid = $this->page->course->id;
            $course_record->cria_file_id = '';
            $course_record->block_id = '';
            $course_record->bot_name = '';
            $course_record->no_context_message = '';
            $course_record->subtitle = '';
            $course_record->welcome_message = '';
            $course_record->lang = '';
            $course_record->published = 0;
        }

        if ($availability->exception == 'success') {
            // Update $course_record->bot_api_key if empty
            if (empty($course_record->bot_api_key)) {
                $course_record->bot_api_key = cria::get_api_key(cria::get_bot_id($this->page->course->id));

                $DB->set_field(
                    'block_aia_settings',
                    'bot_api_key',
                    $course_record->bot_api_key,
                    [
                        'courseid' => $this->page->course->id
                    ]
                );
            }
        }

        // Lets check to see that some content has been trained for the bot.
        if (!$DB->record_exists('block_aia_course_modules', array('courseid' => $this->page->course->id))) {
            // If no content has been trained, we will not display the block.
            $this->content = get_string('no_content_trained', 'block_learningassist');
            // Run ad hoc task to train course modules if not already running.
            $task = course_module_trainer::instance($this->page->course->id);
            $task->set_component('block_learningassist');
            \core\task\manager::queue_adhoc_task($task);

            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }


        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $course_context = context_course::instance($this->page->course->id);

        $this->page->requires->js_call_amd('block_learningassist/course_modules', 'init');

        $data = array(
            'userid' => $USER->id,
            'courseid' => $this->page->course->id,
            'blockid' => $this->instance->id,
            'course_contextid' => $course_context->id,
        );

        $this->content->text = $OUTPUT->render_from_template('block_learningassist/block_learningassist', $data);
        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     * @throws coding_exception
     */
    public function specialization(): void
    {
        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_learningassist');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config(): bool
    {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats(): array
    {
        return array(
            'course-view' => true,
        );
    }
}
