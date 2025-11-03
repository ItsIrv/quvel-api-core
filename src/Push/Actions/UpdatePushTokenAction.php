<?php

declare(strict_types=1);

namespace Quvel\Core\Push\Actions;

use Quvel\Core\Contracts\Device;

class UpdatePushTokenAction
{
    public function __construct(
        private readonly Device $device
    ) {
    }

    public function __invoke(string $deviceId, string $pushToken, string $provider): bool
    {
        return $this->device->updatePushToken($deviceId, $pushToken, $provider);
    }
}
