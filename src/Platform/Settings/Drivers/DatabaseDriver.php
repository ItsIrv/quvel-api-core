<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings\Drivers;

use Quvel\Core\Contracts\PlatformSettingsDriver;
use Quvel\Core\Platform\Settings\PlatformSetting;

/**
 * Database-based platform settings driver.
 * Reads settings from a database (dynamic, update without redeployment).
 */
class DatabaseDriver implements PlatformSettingsDriver
{
    /**
     * Get settings for a specific platform.
     * Merges shared settings with the main mode and platform-specific overrides.
     * Inheritance: shared -> main mode -> specific platform
     *
     * @param string $platform Platform type (any PlatformType value)
     * @return array Merged settings for the specified platform
     */
    public function getSettingsForPlatform(string $platform): array
    {
        return PlatformSetting::getForPlatform($platform);
    }

    /**
     * Get shared settings applied to all platforms.
     *
     * @return array Shared settings
     */
    public function getSharedSettings(): array
    {
        return PlatformSetting::getShared();
    }
}
