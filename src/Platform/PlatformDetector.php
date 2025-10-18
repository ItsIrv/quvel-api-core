<?php

declare(strict_types=1);

namespace Quvel\Core\Platform;

use Illuminate\Http\Request;
use Quvel\Core\Contracts\PlatformDetector as PlatformDetectorContract;
use Quvel\Core\Enums\HttpHeader;

/**
 * Platform detection for identifying app types from requests.
 * Supports multi-tag platform detection (e.g., "capacitor,ios,tablet,screen:md").
 */
class PlatformDetector implements PlatformDetectorContract
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * Get all detected platform tags from the request.
     *
     * @return array Array of platform tag strings
     */
    public function getPlatforms(): array
    {
        $headerValue = $this->request->header(HttpHeader::PLATFORM->getValue(), '');

        if (empty($headerValue)) {
            return [PlatformTag::WEB->value];
        }

        $tags = array_map('trim', explode(',', $headerValue, 5));

        $validTags = [];
        foreach ($tags as $tag) {
            if (PlatformTag::tryFrom($tag) !== null) {
                $validTags[] = $tag;
            }
        }

        return !empty($validTags) ? $validTags : [PlatformTag::WEB->value];
    }

    /**
     * Check if the current request has a specific platform tag.
     *
     * @param string $tag Platform tag to check
     * @return bool True if the tag is present
     */
    public function hasPlatform(string $tag): bool
    {
        return in_array($tag, $this->getPlatforms(), true);
    }

    /**
     * Get the main platform mode.
     * Maps all specific platforms to one of the 3 main modes.
     *
     * @return string Main mode ('web', 'mobile', 'desktop')
     */
    public function getMainMode(): string
    {
        $platforms = $this->getPlatforms();

        foreach ($platforms as $platformValue) {
            $tag = PlatformTag::from($platformValue);
            $mainMode = $tag->getMainMode();

            if ($mainMode !== PlatformTag::WEB->value) {
                return $mainMode;
            }
        }

        return PlatformTag::WEB->value;
    }
}
