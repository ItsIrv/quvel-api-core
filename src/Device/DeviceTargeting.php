<?php

declare(strict_types=1);

namespace Quvel\Core\Device;

use Illuminate\Support\Collection;
use Quvel\Core\Contracts\Device;
use Quvel\Core\Contracts\DeviceTargeting as DeviceTargetingServiceContract;
use Quvel\Core\Models\UserDevice;
use RuntimeException;

class DeviceTargeting implements DeviceTargetingServiceContract
{
    public function __construct(
        private readonly Device $device
    ) {}

    public function getTargetDevices(
        ?UserDevice $requestingDevice,
        ?int $userId,
        ?string $scope = null
    ): Collection {
        $scope = $scope ?? $this->getDefaultScope();

        return match ($scope) {
            'requesting_device' => $this->getRequestingDevice($requestingDevice),
            'all_user_devices' => $this->getAllUserDevices($userId, $requestingDevice),
            default => throw new RuntimeException('Device targeting scope "' . $scope . '" is not supported.'),
        };
    }

    public function getDefaultScope(): string
    {
        return config('quvel.targeting.default_scope', 'requesting_device');
    }

    private function getRequestingDevice(?UserDevice $requestingDevice): Collection
    {
        if (!$requestingDevice || !$requestingDevice->hasValidPushToken()) {
            return collect();
        }

        return collect([$requestingDevice]);
    }

    private function getAllUserDevices(?int $userId, ?UserDevice $requestingDevice): Collection
    {
        if (!$userId) {
            return $this->getRequestingDevice($requestingDevice);
        }

        return $this->device->getUserDevices($userId)->filter(function (UserDevice $device) {
            return $device->hasValidPushToken();
        });
    }

}