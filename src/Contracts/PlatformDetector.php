<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

/**
 * Contract for platform detection.
 */
interface PlatformDetector
{
    /**
     * Get the detected platform type.
     *
     * @return string Platform type ('web', 'mobile', 'desktop')
     */
    public function getPlatform(): string;

    /**
     * Check if current request is from a specific platform.
     *
     * @param string $platform Platform to check ('web', 'mobile', 'desktop')
     * @return bool True if current platform matches
     */
    public function isPlatform(string $platform): bool;

    /**
     * Check if the current platform supports app redirects.
     *
     * @return bool True if platform supports app redirects
     */
    public function supportsAppRedirects(): bool;
}