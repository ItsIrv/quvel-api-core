<?php

declare(strict_types=1);

namespace Quvel\Core\Targeting;

use Illuminate\Support\Collection;
use Quvel\Core\Contracts\DeviceManager;
use Quvel\Core\Contracts\DeviceTargetingService as DeviceTargetingServiceContract;
use Quvel\Core\Models\UserDevice;
use RuntimeException;

class DeviceTargetingService implements DeviceTargetingServiceContract
{
    public function __construct(
        private readonly DeviceManager $deviceManager
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

        return $this->deviceManager->getUserDevices($userId)->filter(function (UserDevice $device) {
            return $device->hasValidPushToken();
        });
    }

}