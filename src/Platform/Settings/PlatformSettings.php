<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Quvel\Core\Contracts\PlatformSettings as PlatformSettingsContract;
use Quvel\Core\Contracts\PlatformSettingsDriver;
use Quvel\Core\Facades\PlatformDetector;

/**
 * Platform-specific settings resolution.
 * Delegates to configured driver (config or database).
 */
class PlatformSettings implements PlatformSettingsContract
{
    public function __construct(
        private readonly PlatformSettingsDriver $driver
    ) {
    }

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
        return $this->driver->getSettingsForPlatform($platform);
    }

    /**
     * Get shared settings applied to all platforms.
     *
     * @return array Shared settings
     */
    public function getSharedSettings(): array
    {
        return $this->driver->getSharedSettings();
    }
}
