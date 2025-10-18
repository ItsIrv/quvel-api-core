<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings\Drivers;

use Quvel\Core\Contracts\PlatformSettings;
use Quvel\Core\Facades\PlatformDetector;
use Quvel\Core\Platform\Settings\PlatformSetting;

/**
 * Database-based platform settings driver.
 * Reads settings from a database (dynamic, update without redeployment).
 */
class DatabaseDriver implements PlatformSettings
{
    /**
     * Get settings for specific platforms.
     * Merges shared settings with all platform-specific overrides in order.
     * Inheritance: shared -> platform1 -> platform2 -> platform3...
     *
     * @param array $platforms Array of platform tags
     * @return array Merged settings for the specified platforms
     */
    public function getSettingsForPlatforms(array $platforms): array
    {
        return PlatformSetting::getForPlatforms($platforms);
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

    public function getCurrentPlatformSettings(): array
    {
        return PlatformSetting::getForPlatforms(
            PlatformDetector::getPlatforms()
        );
    }
}
