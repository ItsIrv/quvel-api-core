<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

/**
 * Contract for platform settings storage drivers.
 */
interface PlatformSettingsDriver
{
    /**
     * Get settings for a specific platform.
     * Merges shared settings with platform-specific overrides.
     *
     * @param string $platform Platform type (any PlatformType value)
     * @return array Merged settings for the specified platform
     */
    public function getSettingsForPlatform(string $platform): array;

    /**
     * Get shared settings applied to all platforms.
     *
     * @return array Shared settings
     */
    public function getSharedSettings(): array;
}
