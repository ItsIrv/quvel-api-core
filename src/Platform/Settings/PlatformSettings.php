<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Quvel\Core\Contracts\PlatformSettings as PlatformSettingsContract;
use Quvel\Core\Facades\PlatformDetector;

/**
 * Platform-specific settings resolution.
 * Merges shared configuration with platform-specific overrides.
 */
class PlatformSettings implements PlatformSettingsContract
{
    /**
     * Get settings for the current detected platform.
     * Merges shared settings with platform-specific overrides.
     *
     * @return array Merged settings for the current platform
     */
    public function getCurrentPlatformSettings(): array
    {
        return $this->getSettingsForPlatform(
            PlatformDetector::getPlatform()
        );
    }

    /**
     * Get settings for a specific platform.
     * Merges shared settings with platform-specific overrides.
     *
     * @param string $platform Platform type (any PlatformType value)
     * @return array Merged settings for the specified platform
     */
    public function getSettingsForPlatform(string $platform): array
    {
        $shared = $this->getSharedSettings();
        $platformSpecific = config("quvel.platform_settings.platforms.$platform", []);

        return array_replace_recursive($shared, $platformSpecific);
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
