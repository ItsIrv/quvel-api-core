<?php

declare(strict_types=1);

namespace Quvel\Core\Platform;

use Illuminate\Http\Request;
use Quvel\Core\Contracts\PlatformDetector as PlatformDetectorContract;;
use Quvel\Core\Enums\HttpHeader;

/**
 * Platform detection for identifying app types from requests.
 */
class PlatformDetector implements PlatformDetectorContract
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Get the detected granular platform type.
     *
     * @return string Platform type (any PlatformType value)
     */
    public function getPlatform(): string
    {
        $platform = PlatformType::tryFrom(
            $this->request->header(HttpHeader::PLATFORM->getValue(), '')
        );

        return $platform?->value ?? PlatformType::WEB->value;
    }

    /**
     * Get the main platform mode.
     * Maps all specific platforms to one of the 3 main modes.
     *
     * @return string Main mode ('web', 'mobile', 'desktop')
     */
    public function getMainMode(): string
    {
        $platform = PlatformType::tryFrom(
            $this->request->header(HttpHeader::PLATFORM->getValue(), '')
        );

        return $platform?->getMainMode() ?? PlatformType::WEB->value;
    }

    /**
     * Check if the current request is from a specific platform.
     *
     * @param string $platform Platform to check ('web', 'mobile', 'desktop')
     * @return bool True if current platform matches
     */
    public function isPlatform(string $platform): bool
    {
        return $this->getPlatform() === $platform;
    }

    /**
     * Check if the current platform supports app redirects.
     *
     * @return bool True if the platform supports app redirects
     */
    public function supportsAppRedirects(): bool
    {
        return in_array(
            $this->getMainMode(),
            [
                PlatformType::MOBILE->value,
                PlatformType::DESKTOP->value
            ],
            true
        );
    }
}