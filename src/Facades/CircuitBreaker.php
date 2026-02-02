<?php

namespace CircuitBreaker\Laravel\Facades;

use CircuitBreaker\Laravel\CircuitBreakerFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CircuitBreaker\CircuitBreaker create(string $configName = 'default')
 * @method static \CircuitBreaker\CircuitBreaker createCacheable(string $configName = 'default')
 */
class CircuitBreaker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CircuitBreakerFactory::class;
    }
}
