<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\Contracts\ProviderInterface;
use CircuitBreaker\Enums\Provider;
use CircuitBreaker\Providers\DatabaseProvider;
use CircuitBreaker\Providers\MemcachedProvider;
use CircuitBreaker\Providers\MemoryProvider;
use CircuitBreaker\Providers\PredisProvider;
use CircuitBreaker\Providers\RedisProvider;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application as LaravelApplication;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Predis\Client;

final class CircuitBreakerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $raw = __DIR__ . '/../config/circuit-breaker.php';
        $path = realpath($raw);
        $source = is_string($path) ? $path : $raw;

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('circuit-breaker.php')]);
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'circuit-breaker-migrations');
        }

        $this->mergeConfigFrom($source, 'circuit-breaker');
    }

    #[\Override]
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
            $provider = Provider::tryFrom($app['config']->get('circuit-breaker.provider'));
            $connections = $app['config']->get('circuit-breaker.connections');

            return match ($provider) {
                Provider::Redis => $this->buildRedisProvider($connections['redis'] ?? []),
                Provider::Predis => $this->buildPredisProvider($connections['redis'] ?? []),
                Provider::Memcached => $this->buildMemcachedProvider(),
                Provider::Database => $this->buildDatabaseProvider($connections['database'] ?? []),
                Provider::Memory => $this->buildMemoryProvider(),
                default => throw new \Exception('Provider not supported'),
            };
        });
    }

    private function buildRedisProvider(?array $config = null): RedisProvider
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('Redis extension is not loaded.');
        }

        $client = Redis::connection($config['connection'] ?? null)->client();

        return new RedisProvider($client);
    }

    private function buildPredisProvider(?array $config = null): PredisProvider
    {
        if (!class_exists(Client::class)) {
            throw new \RuntimeException('Predis library is not installed.');
        }

        $client = Redis::connection($config['connection'] ?? null)->client();

        return new PredisProvider($client);
    }

    private function buildMemcachedProvider(): MemcachedProvider
    {
        if (!extension_loaded('memcached')) {
            throw new \RuntimeException('Memcached extension is not loaded.');
        }

        $store = Cache::store('memcached')->getStore();
        if ($store instanceof MemcachedStore) {
            return new MemcachedProvider($store->getMemcached());
        }

        throw new \Exception('Memcached provider not supported');
    }

    private function buildDatabaseProvider(?array $config = null): DatabaseProvider
    {
        if (!extension_loaded('pdo') || !\PDO::getAvailableDrivers()) {
            throw new \RuntimeException('PDO extension is not loaded.');
        }

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
