<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\Contracts\CircuitBreakerInterface;
use Illuminate\Contracts\Cache\Repository;

readonly class CacheableCircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(
        private CircuitBreakerInterface $circuitBreaker,
        private Repository $cache
    ) {
    }

    public function run(string $name, callable $action, ?callable $fallback = null): mixed
    {
        $cacheKey = "circuit.{$name}.response";

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
}
