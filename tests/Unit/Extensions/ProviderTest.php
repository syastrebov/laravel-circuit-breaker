<?php

namespace Tests\Unit\Extensions;

use CircuitBreaker\Laravel\CircuitBreakerFactory;
use CircuitBreaker\Providers\DatabaseProvider;
use CircuitBreaker\Providers\MemcachedProvider;
use CircuitBreaker\Providers\MemoryProvider;
use CircuitBreaker\Providers\RedisProvider;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

final class ProviderTest extends TestCase
{
    #[DefineEnvironment('useMemoryProvider')]
    public function testBound()
    {
        $this->assertTrue($this->app->bound(CircuitBreakerFactory::class));
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testMemoryProvider(): void
    {
        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);
        $this->assertInstanceOf(MemoryProvider::class, $this->getProvider($factory->create('default')));
    }

    #[DefineEnvironment('useRedisProvider')]
    public function testRedisProvider(): void
    {
        $this->app['config']->set('circuit-breaker.provider', 'redis');

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);
        $this->assertInstanceOf(RedisProvider::class, $this->getProvider($factory->create('default')));
    }

    #[DefineEnvironment('useMemcachedProvider')]
    public function testMemcachedProvider(): void
    {
        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);
        $this->assertInstanceOf(MemcachedProvider::class, $this->getProvider($factory->create('default')));
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDatabaseProvider(): void
    {
        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);

        $provider = $this->getProvider($factory->create('default'));
        $this->assertInstanceOf(DatabaseProvider::class, $provider);
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDefaultTableName(): void
    {
        $factory = $this->app->get(CircuitBreakerFactory::class);
        $provider = $this->getProvider($factory->create('default'));

        $this->assertEquals('circuit_breaker', $this->getPrivateProperty($provider, 'table'));
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDefaultTableNameIfMissing(): void
    {
        $this->app['config']->set('circuit-breaker.connections.database.table', null);

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $provider = $this->getProvider($factory->create('default'));

        $this->assertEquals('circuit_breaker', $this->getPrivateProperty($provider, 'table'));
    }

    public function testUnknownProvider(): void
    {
        $this->app['config']->set('circuit-breaker.provider', 'unknown');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Provider not supported');

        $this->app->get(CircuitBreakerFactory::class);
    }
}
