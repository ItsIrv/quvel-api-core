<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

/**
 * Contract for platform detection.
 */
interface PlatformDetector
{
    /**
     * Get all detected platform tags from the request.
     *
     * @return array Array of platform tag strings (e.g., ['capacitor', 'ios', 'tablet', 'screen:md'])
     */
    public function getPlatforms(): array;

    /**
     * Check if the current request has a specific platform tag.
     *
     * @param string $tag Platform tag to check
     * @return bool True if the tag is present
     */
    public function hasPlatform(string $tag): bool;

    /**
     * Get the main platform mode.
     * Maps all specific platforms to one of the 3 main modes.
     *
     * @return string Main mode ('web', 'mobile', 'desktop')
     */
    public function getMainMode(): string;
}
