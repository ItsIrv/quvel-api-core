<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Quvel\Core\Models\UserDevice;
use Illuminate\Support\Collection;

interface PushSender
{
    /**
     * Send a push notification to a specific device.
     */
    public function sendToDevice(UserDevice $device, string $title, string $body, array $data = []): bool;

    /**
     * Send push notification to multiple devices.
     */
    public function sendToDevices(Collection $devices, string $title, string $body, array $data = []): array;

    /**
     * Get the appropriate driver for a device.
     */
    public function getDriverForDevice(UserDevice $device): ?PushDriver;
}