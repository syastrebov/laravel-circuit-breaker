<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Contracts\CircuitBreakerInterface;
use CircuitBreaker\Providers\ProviderInterface;
use Illuminate\Cache\Repository;
use Psr\Log\LoggerInterface;

readonly class CircuitBreakerFactory
{
    public function __construct(
        private ProviderInterface $provider,
        private array $configs,
        private Repository $repository,
        private LoggerInterface $logger
    ) {
    }

    public function create(string $configName = 'default'): CircuitBreakerInterface
    {
        if (!isset($this->configs[$configName])) {
            throw new \Exception("CircuitBreaker configuration not found [$configName]");
        }

        return new CircuitBreaker(
            $this->provider,
            ConfigBuilder::build($this->configs[$configName]),
            $this->logger
        );
    }

    public function createCacheable(string $configName = 'default'): CircuitBreakerInterface
    {
        return new CacheableCircuitBreaker($this->create($configName), $this->repository);
    }
}
