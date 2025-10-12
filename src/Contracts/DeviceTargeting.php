<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Illuminate\Support\Collection;
use Quvel\Core\Models\UserDevice;

interface DeviceTargeting
{
    /**
     * Get target devices for notification based on scope.
     */
    public function getTargetDevices(
        ?UserDevice $requestingDevice,
        ?int $userId,
        ?string $scope = null
    ): Collection;

    /**
     * Get the default targeting scope.
     */
    public function getDefaultScope(): string;
}