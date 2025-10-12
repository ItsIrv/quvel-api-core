<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Quvel\Core\Models\UserDevice;

/**
 * @method static Collection getTargetDevices(?UserDevice $requestingDevice, ?int $userId, ?string $scope = null)
 * @method static string getDefaultScope()
 */
class DeviceTargeting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quvel\Core\Contracts\DeviceTargeting::class;
    }
}
