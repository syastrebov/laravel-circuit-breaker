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
        $circuit = CircuitBreaker::create();
        $this->assertInstanceOf(\CircuitBreaker\CircuitBreaker::class, $circuit);
        $this->assertEquals('default', $circuit->getConfig()->prefix);
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

        $circuit = CircuitBreaker::create('custom');
        $this->assertInstanceOf(\CircuitBreaker\CircuitBreaker::class, $circuit);
        $this->assertEquals('custom', $circuit->getConfig()->prefix);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCacheableFacadeWithoutName(): void
    {
        $cacheableCircuit = CircuitBreaker::createCacheable();
        $this->assertInstanceOf(CacheableCircuitBreaker::class, $cacheableCircuit);

        $circuit = $this->getPrivateProperty($cacheableCircuit, 'circuitBreaker');
        $this->assertInstanceOf(\CircuitBreaker\CircuitBreaker::class, $circuit);
        $this->assertEquals('default', $circuit->getConfig()->prefix);
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

        $cacheableCircuit = CircuitBreaker::createCacheable('custom');
        $this->assertInstanceOf(CacheableCircuitBreaker::class, $cacheableCircuit);

        $circuit = $this->getPrivateProperty($cacheableCircuit, 'circuitBreaker');
        $this->assertInstanceOf(\CircuitBreaker\CircuitBreaker::class, $circuit);
        $this->assertEquals('custom', $circuit->getConfig()->prefix);
    }
}
