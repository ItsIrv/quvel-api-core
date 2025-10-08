<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Quvel\Core\Contracts\Device;

class DeactivateDeviceAction
{
    public function __construct(
        private readonly Device $device
    ) {}

    public function __invoke(string $deviceId, string $reason = 'Manual deactivation'): bool
    {
        return $this->device->deactivateDevice($deviceId, $reason);
    }
}