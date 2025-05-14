<?php

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'chat_history' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'ttl' => 3600 * 24, // 24 hours
    ]
];
