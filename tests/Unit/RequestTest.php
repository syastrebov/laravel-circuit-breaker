<?php

namespace Tests\Unit;

use CircuitBreaker\Laravel\CircuitBreakerFactory;
use CircuitBreaker\Laravel\Request;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

class RequestTest extends TestCase
{
    #[DefineEnvironment('useMemoryProvider')]
    public function testBound()
    {
        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);

        $circuit = $factory->create();

        $response = $circuit->run(...Request::cacheable('test', static function () {
            return '{"data": "response"}';
        }));

        $this->assertEquals('{"data": "response"}', $response);

        $response = $circuit->run(...Request::cacheable('test', static function () {
            throw new \RuntimeException('unable to handle request');
        }));

        $this->assertEquals('{"data": "response"}', $response);
    }
}
