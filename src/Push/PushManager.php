<?php

declare(strict_types=1);

namespace Quvel\Core\Push;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Manager;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Push\Drivers\FcmDriver;
use Quvel\Core\Push\Drivers\ApnsDriver;
use Quvel\Core\Push\Drivers\WebPushDriver;

class PushManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('quvel.push.default_driver', 'fcm');
    }

    public function getAvailableDrivers(): array
    {
        return config('quvel.push.drivers', []);
    }

    public function isEnabled(): bool
    {
        return config('quvel.push.enabled', true);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createFcmDriver(): PushDriver
    {
        return $this->container->make(FcmDriver::class);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createApnsDriver(): PushDriver
    {
        return $this->container->make(ApnsDriver::class);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createWebPushDriver(): PushDriver
    {
        return $this->container->make(WebPushDriver::class);
    }
}