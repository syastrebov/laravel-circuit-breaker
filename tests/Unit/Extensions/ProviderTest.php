<?php

namespace Tests\Unit\Extensions;

use CircuitBreaker\CircuitBreaker;
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
        $this->assertTrue($this->app->bound(CircuitBreaker::class));
    }

    #[DefineEnvironment('useMemoryProvider')]
    public function testMemoryProvider(): void
    {
        $circuit = $this->app->make(CircuitBreaker::class);

        $this->assertInstanceOf(CircuitBreaker::class, $circuit);
        $this->assertInstanceOf(MemoryProvider::class, $this->getProvider($circuit));
    }

    #[DefineEnvironment('useRedisProvider')]
    public function testRedisProvider(): void
    {
        $circuit = $this->app->make(CircuitBreaker::class);

        $this->assertInstanceOf(CircuitBreaker::class, $circuit);
        $this->assertInstanceOf(RedisProvider::class, $this->getProvider($circuit));
    }

    #[DefineEnvironment('useMemcachedProvider')]
    public function testMemcachedProvider(): void
    {
        $circuit = $this->app->make(CircuitBreaker::class);

        $this->assertInstanceOf(CircuitBreaker::class, $circuit);
        $this->assertInstanceOf(MemcachedProvider::class, $this->getProvider($circuit));
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDatabaseProvider(): void
    {
        $circuit = $this->app->make(CircuitBreaker::class);

        $this->assertInstanceOf(CircuitBreaker::class, $circuit);
        $this->assertInstanceOf(DatabaseProvider::class, $this->getProvider($circuit));
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDefaultTableName(): void
    {
        $circuit = $this->app->make(CircuitBreaker::class);
        $provider = $this->getProvider($circuit);

        $this->assertEquals('circuit_breaker', $this->getPrivateProperty($provider, 'table'));
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDefaultTableNameIfMissing(): void
    {
        $this->app['config']->set('circuit-breaker.connections.database.table', null);

        $circuit = $this->app->make(CircuitBreaker::class);
        $provider = $this->getProvider($circuit);

        $this->assertEquals('circuit_breaker', $this->getPrivateProperty($provider, 'table'));
    }

    public function testUnknownProvider(): void
    {
        $this->app['config']->set('circuit-breaker.provider', 'unknown');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Provider not supported');

        $this->app->make(CircuitBreaker::class);
    }
}
