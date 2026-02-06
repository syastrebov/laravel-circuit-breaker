<?php

declare(strict_types=1);

use CircuitBreaker\Enums\Provider;

return [

    'provider' => Provider::Memory->value,

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
