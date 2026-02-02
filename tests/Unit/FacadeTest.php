<?php

namespace Tests\Unit;

use CircuitBreaker\Laravel\CacheableCircuitBreaker;
use CircuitBreaker\Laravel\Facades\CircuitBreaker;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

class FacadeTest extends TestCase
{
    #[DefineEnvironment('useMemoryProvider')]
    public function testFacadeWithoutName(): void
    {
        $this->assertInstanceOf(
            \CircuitBreaker\CircuitBreaker::class,
            CircuitBreaker::create()
        );
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testFacadeWithName(): void
    {
        $this->app['config']->set('circuit-breaker.configs.custom', [
            'retries' => 5,
            'closed_threshold' => 7,
            'half_open_threshold' => 9,
            'retry_interval' => 4000,
            'open_timeout' => 180,
            'fallback_or_null' => false,
        ]);

        $this->assertInstanceOf(
            \CircuitBreaker\CircuitBreaker::class,
            CircuitBreaker::create('custom')
        );
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCacheableFacadeWithoutName(): void
    {
        $this->assertInstanceOf(
            CacheableCircuitBreaker::class,
            CircuitBreaker::createCacheable()
        );
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCacheableFacadeWithName(): void
    {
        $this->app['config']->set('circuit-breaker.configs.custom', [
            'retries' => 5,
            'closed_threshold' => 7,
            'half_open_threshold' => 9,
            'retry_interval' => 4000,
            'open_timeout' => 180,
            'fallback_or_null' => false,
        ]);

        $this->assertInstanceOf(
            CacheableCircuitBreaker::class,
            CircuitBreaker::createCacheable('custom')
        );
    }
}
