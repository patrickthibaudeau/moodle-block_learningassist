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

use aiplacement_editor\utils;
use core_ai\aiactions\generate_text;
use core_ai\manager;

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
        global $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        // Check if AI generate_text is available
        $is_html_editor_placement_action_available = utils::is_html_editor_placement_action_available(
            context_course::instance($this->page->course->id),
            'generate_text',
            generate_text::class
        );

        $policy_status = manager::get_user_policy_status($USER->id);

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $course_context = context_course::instance($this->page->course->id);

        $this->page->requires->js_call_amd('block_learningassist/ai_policy', 'init');
        $this->page->requires->js_call_amd('block_learningassist/course_modules', 'init');

        // Check to see if block_ai_assistant exists
        $installed_blocks = $this->page->blocks->get_installed_blocks();
        $block_ai_assistant = array_filter($installed_blocks, function($block) {
            return $block->name === 'ai_assistant';
        });

        if ($block_ai_assistant) {
            $block_ai_assistant = true;
        } else {
            $block_ai_assistant = false;
        }


        $data = array(
            'ai_placement_editor_enabled' => $is_html_editor_placement_action_available,
            'userid' => $USER->id,
            'courseid' => $this->page->course->id,
            'blockid' => $this->instance->id,
            'course_contextid' => $course_context->id,
            'ai_policy_status' => $policy_status,
            'ai_assistant_block_exists' => $block_ai_assistant
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
