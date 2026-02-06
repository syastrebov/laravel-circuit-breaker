<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Contracts\CircuitBreakerInterface;
use CircuitBreaker\Enums\CircuitBreakerState;
use Illuminate\Contracts\Cache\Repository;
use Psr\Log\LoggerInterface;

final readonly class CacheableCircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(
        private CircuitBreakerInterface $circuitBreaker,
        private Repository $cache,
        private ?LoggerInterface $logger = null
    ) {
    }

    #[\Override]
    public function getConfig(): CircuitBreakerConfig
    {
        return $this->circuitBreaker->getConfig();
    }

    #[\Override]
    public function getState(string $name): CircuitBreakerState
    {
        return $this->circuitBreaker->getState($name);
    }

    #[\Override]
    public function getStateTimestamp(string $name): int
    {
        return $this->circuitBreaker->getStateTimestamp($name);
    }

    #[\Override]
    public function getFailedAttempts(string $name): int
    {
        return $this->circuitBreaker->getFailedAttempts($name);
    }

    #[\Override]
    public function getHalfOpenAttempts(string $name): int
    {
        return $this->circuitBreaker->getHalfOpenAttempts($name);
    }

    #[\Override]
    public function run(string $name, callable $action, ?callable $fallback = null): mixed
    {
        $cacheKey = $this->buildCacheKey($name);

        return $this->circuitBreaker->run(
            $name,
            function () use ($cacheKey, $action): mixed {
                $response = $action();

                try {
                    $this->cache->set($cacheKey, $response);
                } catch (\Throwable $e) {
                    $this->logger?->error('CacheableCircuitBreaker: ' . $e->getMessage());
                }

                return $response;
            },
            function () use ($cacheKey, $fallback): mixed {
                try {
                    return $this->cache->get($cacheKey);
                } catch (\Throwable $e) {
                    $this->logger?->error('CacheableCircuitBreaker: ' . $e->getMessage());
                }

                if ($fallback !== null) {
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
