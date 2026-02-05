<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\CircuitBreakerConfig;
use CircuitBreaker\Contracts\CircuitBreakerInterface;
use CircuitBreaker\Contracts\ProviderInterface;
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
        return new CircuitBreaker($this->provider, $this->getConfig($configName), $this->logger);
    }

    public function createCacheable(string $configName = 'default'): CircuitBreakerInterface
    {
        return new CacheableCircuitBreaker($this->create($configName), $this->repository);
    }

    protected function getConfig(string $configName): CircuitBreakerConfig
    {
        if (isset($this->configs[$configName])) {
            return CircuitBreakerConfig::create([
                ...$this->configs[$configName],
                'prefix' => $configName,
            ]);
        } elseif ($configName === 'default') {
            return new CircuitBreakerConfig();
        }

        throw new \Exception("CircuitBreaker configuration not found [$configName]");
    }
}
