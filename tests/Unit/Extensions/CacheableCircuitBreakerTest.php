<?php

namespace Tests\Unit\Extensions;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Laravel\CacheableCircuitBreaker;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

final class CacheableCircuitBreakerTest extends TestCase
{
    #[DefineEnvironment('useMemoryProvider')]
    public function testBound()
    {
        $this->app['config']->set('circuit-breaker.configs.default', [
            'retries' => 1,
            'closed_threshold' => 4,
        ]);

        $name = __CLASS__ . __METHOD__;

        $circuit = $this->app->make(CacheableCircuitBreaker::class);
        $this->assertInstanceOf(CacheableCircuitBreaker::class, $circuit);

        $response = $circuit->run($name, static function () {
            return '{"data": "response"}';
        });

        $this->assertEquals('{"data": "response"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(0, $circuit->getFailedAttempts($name));

        $response = $circuit->run($name, static function () {
            throw new \RuntimeException('unable to handle request');
        });

        $this->assertEquals('{"data": "response"}', $response);
        $this->assertEquals(CircuitBreakerState::CLOSED, $circuit->getState($name));
        $this->assertEquals(1, $circuit->getFailedAttempts($name));
    }
}
