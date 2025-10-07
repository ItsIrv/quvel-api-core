<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Quvel\Core\Contracts\DeviceManager;

class DeactivateDeviceAction
{
    public function __construct(
        private readonly DeviceManager $deviceManager
    ) {}

    public function execute(string $deviceId, string $reason = 'Manual deactivation'): bool
    {
        return $this->deviceManager->deactivateDevice($deviceId, $reason);
    }
}