<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Platform detection facade.
 *
 * @method static array getPlatforms() Get all detected platform tags
 * @method static bool hasPlatform(string $tag) Check if the current request has a specific platform tag
 * @method static string getMainMode() Get the main platform mode
 *
 * @see \Quvel\Core\Platform\PlatformDetector
 */
class PlatformDetector extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quvel\Core\Contracts\PlatformDetector::class;
    }
}
