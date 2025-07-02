<?php

namespace block_learningassist\task;

use core\task\adhoc_task;
use block_ai_assistant\cria;
use block_ai_assistant\course_modules;

class course_module_trainer extends adhoc_task
{

    public static function instance(
        int $courseid
    ): self
    {
        $task = new self();
        $task->set_custom_data((object)[
            'courseid' => $courseid,
        ]);

        return $task;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        global $DB;

        $data = $this->get_custom_data();
        $courseid = $data->courseid;

        // Get course modules.
        $course_modules = course_modules::get_course_modules($courseid, true);

        foreach ($course_modules->sections as $section) {
            if (isset($section->modules)) {
                foreach ($section->modules as $module) {

                    // Check to see if module already exists in the block_aia_course_modules table
                    if (!$DB->record_exists(
                        'block_aia_course_modules',
                        [
                            'courseid' => $courseid,
                            'cmid' => $module->cmid
                        ]
                    )) {
                        // Upload to cria
                        $cria_fileid = cria::upload_content_to_bot(
                            $courseid,
                            $module->content->file_name,
                            $module->content->content,
                            'GENERIC'
                        );

                        if ($cria_fileid > 0) {
                            // Insert record into block_aia_course_modules
                            $record = new stdClass();
                            $record->courseid = $courseid;
                            $record->cmid = $module->cmid;
                            $record->modname = $module->modname;
                            $record->modtimemodified = $module->modtimemodified;
                            $record->cria_fileid = $cria_fileid;
                            $record->plugin = 'block_learningassist';
                            $record->timecreated = time();
                            $record->timemodified = time();
                            $DB->insert_record('block_aia_course_modules', $record);
                        } else {
                            // Log error if upload failed
                            mtrace("Failed to upload content for module: {$module->cmid}");
                        }

                    } else {
                        // Log that the module already exists
                        mtrace("Module {$module->cmid} already exists in block_aia_course_modules.");
                    }
                }
            }
        }


        // If no course modules, return.
        if (empty($course_modules)) {
            return;
        }
    }
}