<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Quvel\Core\Models\UserDevice;

/**
 * @method static UserDevice registerDevice(array $deviceData)
 * @method static UserDevice|null findDevice(string $deviceId)
 * @method static Collection getUserDevices(?int $userId)
 * @method static bool updatePushToken(string $deviceId, string $pushToken, string $provider)
 * @method static bool deactivateDevice(string $deviceId, string $reason = 'Manual deactivation')
 * @method static void updateLastSeen(string $deviceId)
 * @method static int cleanupInactiveDevices()
 */
class DeviceManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quvel\Core\Contracts\DeviceManager::class;
    }
}