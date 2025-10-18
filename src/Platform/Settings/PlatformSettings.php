<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Quvel\Core\Contracts\PlatformSettings as PlatformSettingsContract;
use Quvel\Core\Facades\PlatformDetector;

/**
 * Platform-specific settings resolution.
 * Delegates to configured driver (config or database).
 */
class PlatformSettings implements PlatformSettingsContract
{
    public function __construct(
        private readonly PlatformSettingsContract $driver
    ) {
    }

    /**
     * Get settings for the current detected platforms.
     * Merges shared settings with all platform-specific overrides.
     *
     * @return array Merged settings for the current platforms
     */
    public function getCurrentPlatformSettings(): array
    {
        return $this->getSettingsForPlatforms(
            PlatformDetector::getPlatforms()
        );
    }

    /**
     * Get settings for specific platforms.
     * Merges shared settings with all platform-specific overrides in order.
     *
     * @param array $platforms Array of platform tags
     * @return array Merged settings for the specified platforms
     */
    public function getSettingsForPlatforms(array $platforms): array
    {
        return $this->driver->getSettingsForPlatforms($platforms);
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
