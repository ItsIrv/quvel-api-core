<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings\Drivers;

use Quvel\Core\Contracts\PlatformSettings;
use Quvel\Core\Facades\PlatformDetector;

/**
 * Config-based platform settings driver.
 * Reads settings from config files (static, requires redeployment to update).
 */
class ConfigDriver implements PlatformSettings
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
        $allSettings = [$this->getSharedSettings()];

        foreach ($platforms as $platform) {
            $platformSettings = config('quvel.platform_settings.platforms.' . $platform, []);

            if (!empty($platformSettings)) {
                $allSettings[] = $platformSettings;
            }
        }

        return array_replace_recursive(...$allSettings);
    }

    /**
     * Get shared settings applied to all platforms.
     *
     * @return array Shared settings
     */
    public function getSharedSettings(): array
    {
        return config('quvel.platform_settings.shared', []);
    }

    public function getCurrentPlatformSettings(): array
    {
        return $this->getSettingsForPlatforms(
            PlatformDetector::getPlatforms()
        );
    }
}
