<?php

namespace CircuitBreaker\Laravel\Facades;

use CircuitBreaker\Laravel\CacheableCircuitBreaker;
use Illuminate\Support\Facades\Facade;

final class CircuitBreaker extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return \CircuitBreaker\CircuitBreaker::class;
    }

    public static function make(string $name = 'default'): ?\CircuitBreaker\CircuitBreaker
    {
        return self::$app?->make(\CircuitBreaker\CircuitBreaker::class, [$name]);
    }

    public static function makeCacheable(string $name = 'default'): ?CacheableCircuitBreaker
    {
        return self::$app?->make(CacheableCircuitBreaker::class, [$name]);
    }
}
