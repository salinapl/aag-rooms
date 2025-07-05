<?php

Kirby::plugin('salinapl/aag-rooms', [
    'blueprints' => [
        'pages/rooms' => __DIR__ . '/blueprints/rooms.yml',
        'pages/room' => __DIR__ . '/blueprints/room.yml'
    ],
    'controllers' => [
        'room' => require __DIR__ . '/controllers/room.php'
    ],
    'templates' => [
        'rooms' => __DIR__ . '/templates/rooms.php',
        'room' => __DIR__ . '/templates/room.php'
    ],
    'snippets' => [
        'header-rooms' => __DIR__ . '/snippets/header-rooms.php',
        'schedule' => __DIR__ . '/snippets/schedule.php',
        'timeline' => __DIR__ . '/snippets/timeline.php'
    ]
        // plugin magic happens here
]);