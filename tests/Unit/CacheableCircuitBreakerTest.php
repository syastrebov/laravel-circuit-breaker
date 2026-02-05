<?php

namespace Tests\Unit;

use CircuitBreaker\Enums\CircuitBreakerState;
use CircuitBreaker\Laravel\CircuitBreakerFactory;
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

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);

        $circuit = $factory->createCacheable();

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
