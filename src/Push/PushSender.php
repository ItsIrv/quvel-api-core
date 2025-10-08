<?php

declare(strict_types=1);

namespace Quvel\Core\Push;

use Exception;
use Illuminate\Support\Collection;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Contracts\PushSender as PushSenderContract;
use Quvel\Core\Events\PushNotificationSent;
use Quvel\Core\Events\PushNotificationFailed;
use Quvel\Core\Models\UserDevice;

class PushSender implements PushSenderContract
{
    public function __construct(
        private readonly PushManager $manager
    ) {}

    public function sendToDevice(UserDevice $device, string $title, string $body, array $data = []): bool
    {
        if (!$this->manager->isEnabled() || !$device->hasValidPushToken()) {
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
        $availableDrivers = $this->manager->getAvailableDrivers();

        if ($device->push_provider && in_array($device->push_provider, $availableDrivers, true)) {
            $driver = $this->manager->driver($device->push_provider);

            if ($driver->supports($device->platform)) {
                return $driver;
            }
        }

        foreach ($availableDrivers as $driverName) {
            $driver = $this->manager->driver($driverName);

            if ($driver->supports($device->platform)) {
                return $driver;
            }
        }

        return null;
    }
}
