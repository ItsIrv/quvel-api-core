<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Quvel\Core\Contracts\Device;
use Quvel\Core\Models\UserDevice;
use RuntimeException;

class RegisterDeviceAction
{
    public function __construct(
        private readonly Device $device
    ) {}

    public function __invoke(array $deviceData): UserDevice
    {
        if (!config('quvel.devices.allow_anonymous', false) && !auth()->check()) {
            throw new RuntimeException('Authentication required for device registration');
        }

        if (auth()->check()) {
            $deviceData['user_id'] = auth()->id();
        }

        return $this->device->registerDevice($deviceData);
    }
}