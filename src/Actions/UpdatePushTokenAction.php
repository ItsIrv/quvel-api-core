<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Quvel\Core\Contracts\DeviceManager;

class UpdatePushTokenAction
{
    public function __construct(
        private readonly DeviceManager $deviceManager
    ) {}

    public function __invoke(string $deviceId, string $pushToken, string $provider): bool
    {
        return $this->deviceManager->updatePushToken($deviceId, $pushToken, $provider);
    }
}