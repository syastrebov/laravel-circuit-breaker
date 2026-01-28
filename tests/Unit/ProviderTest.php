<?php

namespace Tests\Unit;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Provider\DatabaseProvider;
use CircuitBreaker\Provider\ProviderInterface;
use CircuitBreaker\Provider\MemcachedProvider;
use CircuitBreaker\Provider\MemoryProvider;
use CircuitBreaker\Provider\RedisProvider;
use CircuitBreaker\Laravel\CircuitBreakerFactory;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

class ProviderTest extends TestCase
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

    public function testRedisProvider(): void
    {
        $this->app['config']->set('circuit-breaker.driver', 'redis');

        $factory = $this->app->get(CircuitBreakerFactory::class);
        $this->assertInstanceOf(CircuitBreakerFactory::class, $factory);
        $this->assertInstanceOf(RedisProvider::class, $this->getProvider($factory->create('default')));
    }

    public function testMemcachedProvider(): void
    {
        $this->app['config']->set('circuit-breaker.driver', 'memcached');

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
        $this->app['config']->set('circuit-breaker.driver', 'unknown');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Driver not supported');

        $this->app->get(CircuitBreakerFactory::class);
    }

    protected function getProvider(CircuitBreaker $circuitBreaker): ProviderInterface
    {
        return $this->getPrivateProperty($circuitBreaker, 'provider');
    }
}
