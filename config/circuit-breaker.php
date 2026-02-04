<?php

declare(strict_types=1);

return [

    'driver' => 'memory',

    'logger' => [
        'channel' => '',
    ],

    'connections' => [
        'redis' => [
            'connection' => 'default',
        ],
        'database' => [
            'connection' => '',
            'table' => 'circuit_breaker',
        ],
    ],

    'configs' => [
        'default' => [
            'retries' => 3,
            'closed_threshold' => 3,
            'half_open_threshold' => 3,
            'retry_interval' => 1000,
            'open_timeout' => 60,
            'fallback_or_null' => false,
        ],
    ],
];
