<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

/**
 * Contract for platform detection.
 */
interface PlatformDetector
{
    /**
     * Get the detected granular platform type.
     *
     * @return string Platform type (any PlatformType value: 'web', 'mobile', 'ios', 'android', 'capacitor', etc.)
     */
    public function getPlatform(): string;

    /**
     * Get the main platform mode.
     * Maps all specific platforms to one of the 3 main modes.
     *
     * @return string Main mode ('web', 'mobile', 'desktop')
     */
    public function getMainMode(): string;

    /**
     * Check if the current request is from a specific platform.
     *
     * @param string $platform Platform to check (any PlatformType value)
     * @return bool True if current platform matches
     */
    public function isPlatform(string $platform): bool;

    /**
     * Check if the current platform supports app redirects.
     *
     * @return bool True if a platform supports app redirects
     */
    public function supportsAppRedirects(): bool;
}