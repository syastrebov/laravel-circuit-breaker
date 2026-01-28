<?php

namespace Tests;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Driver\DriverInterface;
use CircuitBreaker\Laravel\CircuitBreakerServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CircuitBreakerServiceProvider::class,
        ];
    }

    protected function useMemoryProvider($app): void
    {
        $app['config']->set('circuit-breaker.driver', 'memory');
    }

    protected function useDatabaseProvider($app): void
    {
        $app['config']->set('circuit-breaker.driver', 'database');
    }

    protected function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }
}
