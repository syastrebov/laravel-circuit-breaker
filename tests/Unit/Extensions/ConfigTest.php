<?php

namespace Tests\Unit\Extensions;

use CircuitBreaker\Laravel\CircuitBreakerFactory;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

final class ConfigTest extends TestCase
{
    #[DefineEnvironment('useMemoryProvider')]
    public function testDefaultConfig(): void
    {
        $this->app['config']->set('circuit-breaker.configs', []);

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $config = $factory->create()->getConfig();

        $this->assertEquals('default', $config->prefix);
        $this->assertEquals(3, $config->retries);
        $this->assertEquals(3, $config->closedThreshold);
        $this->assertEquals(3, $config->halfOpenThreshold);
        $this->assertEquals(1000, $config->retryInterval);
        $this->assertEquals(60, $config->openTimeout);
        $this->assertEquals(false, $config->fallbackOrNull);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testDefaultConfigByName(): void
    {
        $this->app['config']->set('circuit-breaker.configs', []);

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $config = $factory->create('default')->getConfig();

        $this->assertEquals('default', $config->prefix);
        $this->assertEquals(3, $config->retries);
        $this->assertEquals(3, $config->closedThreshold);
        $this->assertEquals(3, $config->halfOpenThreshold);
        $this->assertEquals(1000, $config->retryInterval);
        $this->assertEquals(60, $config->openTimeout);
        $this->assertEquals(false, $config->fallbackOrNull);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCustomDefaultConfig(): void
    {
        $this->app['config']->set('circuit-breaker.configs.default', [
            'retries' => 2,
            'closed_threshold' => 4,
            'half_open_threshold' => 6,
            'retry_interval' => 2000,
            'open_timeout' => 120,
            'fallback_or_null' => true,
        ]);

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $config = $factory->create('default')->getConfig();

        $this->assertEquals('default', $config->prefix);
        $this->assertEquals(2, $config->retries);
        $this->assertEquals(4, $config->closedThreshold);
        $this->assertEquals(6, $config->halfOpenThreshold);
        $this->assertEquals(2000, $config->retryInterval);
        $this->assertEquals(120, $config->openTimeout);
        $this->assertEquals(true, $config->fallbackOrNull);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCustomConfig(): void
    {
        $this->app['config']->set('circuit-breaker.configs.custom', [
            'retries' => 5,
            'closed_threshold' => 7,
            'half_open_threshold' => 9,
            'retry_interval' => 4000,
            'open_timeout' => 180,
            'fallback_or_null' => false,
        ]);

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $config = $factory->create('custom')->getConfig();

        $this->assertEquals('custom', $config->prefix);
        $this->assertEquals(5, $config->retries);
        $this->assertEquals(7, $config->closedThreshold);
        $this->assertEquals(9, $config->halfOpenThreshold);
        $this->assertEquals(4000, $config->retryInterval);
        $this->assertEquals(180, $config->openTimeout);
        $this->assertEquals(false, $config->fallbackOrNull);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCustomConfigIfEmpty(): void
    {
        $this->app['config']->set('circuit-breaker.configs.custom', []);

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $config = $factory->create('custom')->getConfig();

        $this->assertEquals('custom', $config->prefix);
        $this->assertEquals(3, $config->retries);
        $this->assertEquals(3, $config->closedThreshold);
        $this->assertEquals(3, $config->halfOpenThreshold);
        $this->assertEquals(1000, $config->retryInterval);
        $this->assertEquals(60, $config->openTimeout);
        $this->assertEquals(false, $config->fallbackOrNull);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCustomConfigIfMissing(): void
    {
        $this->app['config']->set('circuit-breaker.configs.custom', null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CircuitBreaker configuration not found [custom]');

        $factory = $this->app->make(CircuitBreakerFactory::class);
        $factory->create('custom');
    }
}
