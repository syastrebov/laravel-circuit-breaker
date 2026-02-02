<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Contracts\CircuitBreakerInterface;
use Illuminate\Contracts\Cache\Repository;

readonly class CacheableCircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(
        private CircuitBreakerInterface $circuitBreaker,
        private Repository $cache
    ) {
    }

    public function getConfig(): CircuitBreakerConfig
    {
        return $this->circuitBreaker->getConfig();
    }

    public function run(string $name, callable $action, ?callable $fallback = null): mixed
    {
        $cacheKey = $this->buildCacheKey($name);

        return $this->circuitBreaker->run(
            $name,
            function () use ($cacheKey, $action) {
                $response = $action();

                try {
                    $this->cache->set($cacheKey, $response);
                } catch (\Throwable $e) {
                    // ignore
                }

                return $response;
            },
            function () use ($cacheKey, $fallback) {
                try {
                    return $this->cache->get($cacheKey);
                } catch (\Throwable $e) {
                    // ignore
                }

                if ($fallback) {
                    return $fallback();
                }

                return null;
            }
        );
    }

    protected function buildCacheKey(string $name): string
    {
        return "circuit.{$this->circuitBreaker->getConfig()->prefix}.{$name}.response";
    }
}
