<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\CircuitBreakerConfig;

class ConfigBuilder
{
    public static function build(array $config): CircuitBreakerConfig
    {
        return new CircuitBreakerConfig(
            retries: $config["retries"] ?? 3,
            closedThreshold: $config["closed_threshold"] ?? 5,
            halfOpenThreshold: $config["half_open_threshold"] ?? 5,
            retryInterval: $config["retry_interval"] ?? 1000,
            openTimeout: $config["open_timeout"] ?? 60,
            fallbackOrNull: $config["fallback_or_null"] ?? false,
        );
    }
}
