<?php

namespace CircuitBreaker\Laravel;

use CircuitBreaker\CircuitBreaker;
use CircuitBreaker\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;

readonly class CircuitBreakerFactory
{
    public function __construct(
        private ProviderInterface $provider,
        private array $configs,
        private LoggerInterface $logger
    ) {
    }

    public function create(string $configName = 'default'): CircuitBreaker
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
}
