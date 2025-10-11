<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

/**
 * Contract for platform-specific settings resolution.
 */
interface PlatformSettings
{
    /**
     * Get settings for the current detected platform.
     * Merges shared settings with platform-specific overrides.
     *
     * @return array Merged settings for the current platform
     */
    public function getCurrentPlatformSettings(): array;

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
