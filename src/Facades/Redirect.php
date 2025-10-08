<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Quvel\Core\Contracts\AppRedirector;

/**
 * Multi-platform redirect facade.
 *
 * @method static RedirectResponse|Response redirect(string $path = '', array $queryParams = []) Smart redirect based on platform and configured redirect mode
 * @method static RedirectResponse|Response redirectWithMessage(string $path, string $message, array $extraParams = []) Redirect with a message parameter
 * @method static RedirectResponse|Response redirectToApp(string $path = '', array $queryParams = [], ?string $redirectMode = null) Redirect user back to their app (for browser contexts like Socialite)
 * @method static string getUrl(string $path = '', array $queryParams = []) Get the frontend URL without redirecting
 * @method static string getUrlWithMessage(string $path, string $message, array $extraParams = []) Get URL with message parameter
 * @method static bool isPlatform(string $platform) Check if current request is from a specific platform
 * @method static string getPlatform() Get the detected platform type
 * @method static bool supportsAppRedirects() Check if the current platform supports app redirects
 * @method static bool isValidRedirectUrl(string $url) Validate if a redirect URL is safe
 *
 * @see AppRedirector
 */
class Redirect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AppRedirector::class;
    }
}