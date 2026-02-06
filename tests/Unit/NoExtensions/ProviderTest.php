<?php

namespace Tests\Unit\NoExtensions;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Providers\DatabaseProvider;
use CircuitBreaker\Providers\PredisProvider;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    #[DefineEnvironment('useRedisProvider')]
    public function testRedisProvider(): void
    {
        $this->app['config']->set('circuit-breaker.provider', 'redis');
        $this->app['config']->set('database.redis.client', 'redis');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Redis extension is not loaded.');

        $this->app->make(CircuitBreaker::class);
    }

    #[DefineEnvironment('usePredisProvider')]
    public function testPredisProvider(): void
    {
        $this->app['config']->set('circuit-breaker.provider', 'predis');
        $this->app['config']->set('database.redis.client', 'predis');

        $circuit = $this->app->make(CircuitBreaker::class);
        $this->assertInstanceOf(CircuitBreaker::class, $circuit);
        $this->assertInstanceOf(PredisProvider::class, $this->getProvider($circuit));
    }

    #[DefineEnvironment('useMemcachedProvider')]
    public function testMemcachedProvider(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Memcached extension is not loaded.');

        $this->app->make(CircuitBreaker::class);
    }

    #[DefineEnvironment('useDatabaseProvider')]
    public function testDatabaseProvider(): void
    {
        $circuit = $this->app->make(CircuitBreaker::class);
        $this->assertInstanceOf(CircuitBreaker::class, $circuit);

        $provider = $this->getProvider($circuit);
        $this->assertInstanceOf(DatabaseProvider::class, $provider);

        $pdo = $this->getPrivateProperty($provider, 'pdo');

        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals('sqlite', $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
    }
}
