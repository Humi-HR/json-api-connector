<?php

namespace Humi\JsonApiConnector\Tests\Traits;

/**
 * Trait MocksServices
 * @package App\Tests\Traits
 */
trait MocksServices
{
    /**
     * @param string $class
     * @param $service
     *
     * @return MocksServices
     */
    public function bindService(string $class, $service): self
    {
        app()->bind(
            $class,
            function ($app) use ($service) {
                return $service;
            }
        );

        return $this;
    }
}
