<?php

namespace CircuitBreaker\Laravel;

use Illuminate\Support\Facades\Cache;

class Request
{
    public static function cacheable(string $name, callable $action): array
    {
        $cacheKey = "circuit.{$name}.response";

        return [
            $name,
            static function () use ($action, $cacheKey) {
                $response = $action();
                Cache::set($cacheKey, $response);

                return $response;
            },
            static function () use ($cacheKey) {
                return Cache::get($cacheKey);
            }
        ];
    }
}
