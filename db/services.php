<?php
$functions = array(
    'block_learningassist_chat' => array(
        'classname' => 'block_learningassist_chat',
        'methodname' => 'chat',
        'classpath' => 'blocks/learningassist/classes/external/chat.php',
        'description' => 'This function allows the user to chat with the AI assistant.',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_learningassist_display_course_modules' => array(
        'classname' => 'block_learningassist_course_modules',
        'methodname' => 'display_modules',
        'classpath' => 'blocks/learningassist/classes/external/course_modules.php',
        'description' => 'This function displays all course modules',
        'type' => 'read',
        'capabilities' => '',
        'ajax' => true
    ),
    'block_learningassist_remove_chat_history' => array(
        'classname' => 'block_learningassist_chat',
        'methodname' => 'clear_history',
        'classpath' => 'blocks/learningassist/classes/external/chat.php',
        'description' => 'This function clears the chat history',
        'type' => 'write',
        'capabilities' => '',
        'ajax' => true
    ),
);