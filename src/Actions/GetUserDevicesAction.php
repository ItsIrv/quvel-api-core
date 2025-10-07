<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Illuminate\Support\Collection;
use Quvel\Core\Contracts\DeviceManager;
use RuntimeException;

class GetUserDevicesAction
{
    public function __construct(
        private readonly DeviceManager $deviceManager
    ) {}

    public function execute(?int $userId): Collection
    {
        if (!$userId) {
            throw new RuntimeException('Authentication required');
        }

        return $this->deviceManager->getUserDevices($userId);
    }
}