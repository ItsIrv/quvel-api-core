<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Illuminate\Support\Collection;
use Quvel\Core\Contracts\Device;
use RuntimeException;

class GetUserDevicesAction
{
    public function __construct(
        private readonly Device $device
    ) {}

    public function __invoke(?int $userId): Collection
    {
        if (!$userId) {
            throw new RuntimeException('Authentication required');
        }

        return $this->device->getUserDevices($userId);
    }
}