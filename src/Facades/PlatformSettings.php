<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Platform settings facade.
 *
 * @method static array getCurrentPlatformSettings() Get settings for the current detected platforms
 * @method static array getSettingsForPlatforms(array $platforms) Get settings for specific platforms
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
