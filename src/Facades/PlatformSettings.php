<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Platform settings facade.
 *
 * @method static array getCurrentPlatformSettings() Get settings for the current detected platform
 * @method static array getSettingsForPlatform(string $platform) Get settings for a specific platform
 * @method static array getSharedSettings() Get shared settings applied to all platforms
 *
 * @see \Quvel\Core\Platform\Settings\PlatformSettings
 */
class PlatformSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quvel\Core\Contracts\PlatformSettings::class;
    }
}
