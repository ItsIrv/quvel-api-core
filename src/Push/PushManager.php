<?php

declare(strict_types=1);

namespace Quvel\Core\Push;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Manager;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Contracts\PushManager as PushManagerContract;
use Quvel\Core\Events\PushNotificationSent;
use Quvel\Core\Events\PushNotificationFailed;
use Quvel\Core\Models\UserDevice;
use Illuminate\Support\Collection;
use Quvel\Core\Push\Drivers\FcmDriver;
use Quvel\Core\Push\Drivers\ApnsDriver;
use Quvel\Core\Push\Drivers\WebPushDriver;

class PushManager extends Manager implements PushManagerContract
{
    public function getDefaultDriver(): string
    {
        return 'fcm';
    }

    public function sendToDevice(UserDevice $device, string $title, string $body, array $data = []): bool
    {
        if (!$this->isEnabled() || !$device->hasValidPushToken()) {
            return false;
        }

        $driver = $this->getDriverForDevice($device);

        if (!$driver) {
            return false;
        }

        try {
            $success = $driver->send($device, $title, $body, $data);

            if ($success) {
                PushNotificationSent::dispatch(
                    deviceIds: [$device->device_id],
                    title: $title,
                    body: $body,
                    provider: $driver->getName()
                );
            } else {
                PushNotificationFailed::dispatch(
                    deviceIds: [$device->device_id],
                    title: $title,
                    body: $body,
                    provider: $driver->getName(),
                    error: 'Send operation returned false'
                );
            }

            return $success;
        } catch (Exception $e) {
            PushNotificationFailed::dispatch(
                deviceIds: [$device->device_id],
                title: $title,
                body: $body,
                provider: $driver->getName(),
                error: $e->getMessage()
            );

            return false;
        }
    }

    public function sendToDevices(Collection $devices, string $title, string $body, array $data = []): array
    {
        $batchSize = config('quvel.push.batch_size', 1000);
        $results = [];

        $devices->chunk($batchSize)->each(function ($batch) use ($title, $body, $data, &$results) {
            foreach ($batch as $device) {
                $results[$device->device_id] = $this->sendToDevice($device, $title, $body, $data);
            }
        });

        return $results;
    }

    public function getDriverForDevice(UserDevice $device): ?PushDriver
    {
        if ($device->push_provider && $this->hasDriver($device->push_provider)) {
            $driver = $this->driver($device->push_provider);

            if ($driver->supports($device->platform)) {
                return $driver;
            }
        }

        foreach ($this->getAvailableDrivers() as $driverName) {
            $driver = $this->driver($driverName);

            if ($driver->supports($device->platform)) {
                return $driver;
            }
        }

        return null;
    }

    public function getAvailableDrivers(): array
    {
        return config('quvel.push.drivers', []);
    }

    public function isEnabled(): bool
    {
        return config('quvel.push.enabled', true);
    }

    private function hasDriver(string $name): bool
    {
        return in_array($name, $this->getAvailableDrivers(), true);
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