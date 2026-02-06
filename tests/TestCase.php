<?php

namespace Tests;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Contracts\ProviderInterface;
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
        $app['config']->set('circuit-breaker.provider', 'memory');
    }

    protected function useDatabaseProvider($app): void
    {
        $app['config']->set('circuit-breaker.provider', 'database');
    }

    protected function useMemcachedProvider($app): void
    {
        $app['config']->set('circuit-breaker.provider', 'memcached');
    }

    protected function useRedisProvider($app): void
    {
        $app['config']->set('circuit-breaker.provider', 'redis');
    }

    protected function usePredisProvider($app): void
    {
        $app['config']->set('circuit-breaker.provider', 'predis');
    }

    protected function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectedClass = new \ReflectionClass($object);
        $reflection = $reflectedClass->getProperty($property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }

    protected function getProvider(CircuitBreaker $circuitBreaker): ProviderInterface
    {
        return $this->getPrivateProperty($circuitBreaker, 'provider');
    }
}
