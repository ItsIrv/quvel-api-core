<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Platform detection facade.
 *
 * @method static string getPlatform() Get the detected platform type
 * @method static string getMainMode() Get the main platform mode
 * @method static bool isPlatform(string $platform) Check if current request is from a specific platform
 * @method static bool supportsAppRedirects() Check if platform supports app redirects
 *
 * @see PlatformDetector
 */
class PlatformDetector extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quvel\Core\Contracts\PlatformDetector::class;
    }
}