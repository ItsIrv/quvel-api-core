<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings\Drivers;

use Quvel\Core\Contracts\PlatformSettingsDriver;
use Quvel\Core\Platform\PlatformType;

/**
 * Config-based platform settings driver.
 * Reads settings from config files (static, requires redeployment to update).
 */
class ConfigDriver implements PlatformSettingsDriver
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
        $shared = $this->getSharedSettings();

        $platformType = PlatformType::tryFrom($platform);
        $mainMode = $platformType?->getMainMode() ?? $platform;

        $mainModeSettings = [];
        if ($mainMode !== $platform) {
            $mainModeSettings = config("quvel.platform_settings.platforms.$mainMode", []);
        }

        $platformSpecific = config("quvel.platform_settings.platforms.$platform", []);

        return array_replace_recursive($shared, $mainModeSettings, $platformSpecific);
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
}
