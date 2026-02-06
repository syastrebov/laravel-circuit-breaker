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

        $this->assertConfig('default', 3, 3, 3, 1000, 60, false);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testDefaultConfigByName(): void
    {
        $this->app['config']->set('circuit-breaker.configs', []);

        $this->assertConfig('default', 3, 3, 3, 1000, 60, false);
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

        $this->assertConfig('default', 2, 4, 6, 2000, 120, true);
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

        $this->assertConfig('custom', 5, 7, 9, 4000, 180, false);
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testCustomConfigIfEmpty(): void
    {
        $this->app['config']->set('circuit-breaker.configs.custom', []);

        $this->assertConfig('custom', 3, 3, 3, 1000, 60, false);
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

    private function assertConfig(
        string $configName,
        int $retries,
        int $closedThreshold,
        int $halfOpenThreshold,
        int $retryInterval,
        int $openTimeout,
        bool $fallbackOrNull
    ): void {
        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);

        $config = $factory->create($configName)->getConfig();

        $this->assertEquals($configName, $config->prefix);
        $this->assertEquals($retries, $config->retries);
        $this->assertEquals($closedThreshold, $config->closedThreshold);
        $this->assertEquals($halfOpenThreshold, $config->halfOpenThreshold);
        $this->assertEquals($retryInterval, $config->retryInterval);
        $this->assertEquals($openTimeout, $config->openTimeout);
        $this->assertEquals($fallbackOrNull, $config->fallbackOrNull);
    }
}
