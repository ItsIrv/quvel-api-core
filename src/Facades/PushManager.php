<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Models\UserDevice;

/**
 * @method static bool sendToDevice(UserDevice $device, string $title, string $body, array $data = [])
 * @method static array sendToDevices(Collection $devices, string $title, string $body, array $data = [])
 * @method static PushDriver|null getDriverForDevice(UserDevice $device)
 * @method static array getAvailableDrivers()
 * @method static bool isEnabled()
 */
class PushManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quvel\Core\Contracts\PushManager::class;
    }
}