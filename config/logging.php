<?php

return [
    'default' => $_ENV['LOG_CHANNEL'] ?? 'daily',

    'channels' => [
        'daily' => [
            'driver' => 'daily',
            'path' => __DIR__ . '/../storage/logs/app.log',
            'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
            'days' => 30,
        ],

        'error' => [
            'driver' => 'single',
            'path' => __DIR__ . '/../storage/logs/error.log',
            'level' => 'error',
        ],

        'security' => [
            'driver' => 'daily',
            'path' => __DIR__ . '/../storage/logs/security.log',
            'level' => 'warning',
            'days' => 90,
        ],

        'api' => [
            'driver' => 'daily',
            'path' => __DIR__ . '/../storage/logs/api.log',
            'level' => 'info',
            'days' => 30,
        ],
    ],

    'formatters' => [
        'default' => [
            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'date_format' => 'Y-m-d H:i:s',
        ],
    ],

    'processors' => [
        'web' => [
            'ip' => true,
            'user_agent' => true,
            'request_id' => true,
        ],
    ],
]; 