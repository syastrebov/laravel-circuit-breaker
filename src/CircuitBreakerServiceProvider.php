<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Providers\DatabaseProvider;
use CircuitBreaker\Providers\MemcachedProvider;
use CircuitBreaker\Providers\MemoryProvider;
use CircuitBreaker\Providers\RedisProvider;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application as LaravelApplication;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class CircuitBreakerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $source = realpath($raw = __DIR__ . '/../config/circuit-breaker.php') ?: $raw;
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('circuit-breaker.php')]);
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'circuit-breaker-migrations');
        }

        $this->mergeConfigFrom($source, 'circuit-breaker');
    }

    public function register(): void
    {
        $this->registerProvider();
        $this->registerFactory();
    }

    private function registerFactory(): void
    {
        $this->app->singleton(CircuitBreakerFactory::class, function (Container $app) {
            $logger = $app->make(LogManager::class);
            if ($channel = $app['config']->get('circuit-breaker.logger.channel')) {
                $logger = $logger->channel($channel);
            }

            return new CircuitBreakerFactory(
                $app->get(ProviderInterface::class),
                $app['config']->get('circuit-breaker.configs'),
                $app->get(Repository::class),
                $logger
            );
        });
    }

    private function registerProvider(): void
    {
        $this->app->singleton(ProviderInterface::class, function (Container $app) {
            $type = $app['config']->get('circuit-breaker.driver');
            $connections = $app['config']->get('circuit-breaker.connections');

            return match ($type) {
                'redis' => $this->buildRedisProvider($connections['redis'] ?? []),
                'memcached' => $this->buildMemcachedProvider($connections['memcached'] ?? []),
                'database' => $this->buildDatabaseProvider($connections['database'] ?? []),
                'memory' => $this->buildMemoryProvider(),
                default => throw new \Exception('Driver not supported'),
            };
        });
    }

    private function buildRedisProvider(?array $config = null): RedisProvider
    {
        return new RedisProvider(Redis::connection($config['connection'] ?? null)->client());
    }

    private function buildMemcachedProvider(?array $config = null): MemcachedProvider
    {
        return new MemcachedProvider(Cache::store('memcached')->getStore()->getMemcached());
    }

    private function buildDatabaseProvider(?array $config = null): DatabaseProvider
    {
        return new DatabaseProvider(
            DB::connection($config['connection'] ?? null)->getPdo(),
            $config['table'] ?? 'circuit_breaker'
        );
    }

    private function buildMemoryProvider(): MemoryProvider
    {
        return new MemoryProvider();
    }
}
