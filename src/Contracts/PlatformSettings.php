<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

/**
 * Contract for platform settings resolution.
 */
interface PlatformSettings
{
    /**
     * Get settings for the current detected platforms.
     * Merges shared settings with all platform-specific overrides.
     *
     * @return array Merged settings for the current platforms
     */
    public function getCurrentPlatformSettings(): array;

    /**
     * Get settings for specific platforms.
     * Merges shared settings with all platform-specific overrides in order.
     *
     * @param array $platforms Array of platform tags
     * @return array Merged settings for the specified platforms
     */
    public function getSettingsForPlatforms(array $platforms): array;

    /**
     * Get shared settings applied to all platforms.
     *
     * @return array Shared settings
     */
    public function getSharedSettings(): array;
}
