<?php

declare(strict_types=1);

namespace Quvel\Core\Platform;

use Illuminate\Http\Request;
use Quvel\Core\Contracts\PlatformDetector;
use Quvel\Core\Enums\HttpHeader;

/**
 * Platform detection for identifying app types from requests.
 */
class Detector implements PlatformDetector
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Get the detected platform type.
     *
     * @return string Platform type ('web', 'mobile', 'desktop')
     */
    public function getPlatform(): string
    {
        $platform = PlatformType::tryFrom(
            $this->request->header(HttpHeader::PLATFORM->getValue())
        );

        return $platform?->getMainMode() ?? PlatformType::WEB->value;
    }

    /**
     * Check if current request is from a specific platform.
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
     * @return bool True if platform supports app redirects
     */
    public function supportsAppRedirects(): bool
    {
        return in_array(
            $this->getPlatform(),
            [
                PlatformType::MOBILE->value,
                PlatformType::DESKTOP->value
            ],
            true
        );
    }
}