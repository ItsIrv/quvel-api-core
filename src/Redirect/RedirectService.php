<?php

declare(strict_types=1);

namespace Quvel\Core\Redirect;

use Quvel\Core\Contracts\RedirectService as RedirectServiceContract;
use Quvel\Core\Facades\Platform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Multi-platform redirect service for getting users back to their apps.
 *
 * ## Redirect Modes
 *
 * **universal_links**: Use regular HTTPS URLs
 * - Requires App Site Association (iOS) / Android App Links setup
 * - Same URL works in browser and opens app seamlessly
 * - Best UX but requires domain configuration
 * - Example: https://example.com/dashboard → opens app or web
 *
 * **custom_scheme**: Use custom scheme URLs
 * - Replaces https:// with custom scheme (myapp://)
 * - Phone asks "Open in app?" when clicked
 * - Works without domain setup, less seamless
 * - Example: myapp://dashboard → phone prompts to open app
 *
 * **landing_page**: Show intermediate page with countdown
 * - User lands on page with countdown timer
 * - Automatically tries to open app, shows manual button
 * - Most reliable, good for auth flows like Socialite
 * - Best accessibility and user control
 *
 * **web_only**: Always use web URLs
 * - Never tries to open app, stays in browser
 * - For when you want to keep users on web
 * - If deep links are set up externally, this will be ignored as that's device functionality.
 *
 * ## Use Cases
 * - Direct API responses to apps (`redirect()` method)
 * - Browser redirects like Socialite auth (`redirectToApp()` method)
 *
 * @see config/quvel.php for configuration options
 */
class RedirectService implements RedirectServiceContract
{
    /**
     * Smart redirect based on platform and configured redirect mode.
     *
     * For app platforms, uses the configured redirect mode.
     * For web platforms, always does a regular redirect.
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @return RedirectResponse|Response
     */
    public function redirect(string $path = '', array $queryParams = []): RedirectResponse|Response
    {
        if (Platform::isPlatform('web')) {
            return redirect()->away($this->buildWebUrl($path, $queryParams));
        }

        return $this->redirectToApp($path, $queryParams);
    }

    /**
     * Redirect with a message parameter.
     *
     * Convenience method that adds a 'message' query parameter.
     *
     * @param string $path Path relative to frontend base URL
     * @param string $message Message to include in query parameters
     * @param array $extraParams Additional query parameters
     * @return RedirectResponse|Response
     */
    public function redirectWithMessage(string $path, string $message, array $extraParams = []): RedirectResponse|Response
    {
        $queryParams = array_merge(['message' => $message], $extraParams);
        return $this->redirect($path, $queryParams);
    }

    /**
     * Redirect user back to their app (for browser contexts like Socialite).
     *
     * Uses the configured redirect mode:
     * - 'universal_links': Regular HTTPS redirect
     * - 'custom_scheme': Custom scheme URL
     * - 'landing_page': Intermediate page with countdown
     * - 'web_only': Regular HTTPS redirect
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @param string|null $redirectMode Override default redirect mode
     * @return RedirectResponse|Response
     */
    public function redirectToApp(string $path = '', array $queryParams = [], ?string $redirectMode = null): RedirectResponse|Response
    {
        $mode = $redirectMode ?? config('quvel.frontend.redirect_mode', 'universal_links');

        return match ($mode) {
            'custom_scheme' => redirect()->away($this->buildCustomSchemeUrl($path, $queryParams)),
            'landing_page' => $this->showLandingPage($path, $queryParams),
            default => redirect()->away($this->buildWebUrl($path, $queryParams)),
        };
    }

    /**
     * Get the frontend URL without redirecting.
     *
     * Returns web URL for web platforms, app URL for app platforms based on redirect mode.
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @return string Complete URL
     */
    public function getUrl(string $path = '', array $queryParams = []): string
    {
        if (Platform::isPlatform('web')) {
            return $this->buildWebUrl($path, $queryParams);
        }

        $mode = config('quvel.frontend.redirect_mode', 'universal_links');

        return match ($mode) {
            'custom_scheme' => $this->buildCustomSchemeUrl($path, $queryParams),
            default => $this->buildWebUrl($path, $queryParams),
        };
    }

    /**
     * Get URL with message parameter.
     *
     * @param string $path Path relative to frontend base URL
     * @param string $message Message to include in query parameters
     * @param array $extraParams Additional query parameters
     * @return string Complete URL with message parameter
     */
    public function getUrlWithMessage(string $path, string $message, array $extraParams = []): string
    {
        $queryParams = array_merge(['message' => $message], $extraParams);
        return $this->getUrl($path, $queryParams);
    }

    /**
     * Check if current request is from a specific platform.
     *
     * @param string $platform Platform to check ('web', 'mobile', 'desktop')
     * @return bool True if current platform matches
     */
    public function isPlatform(string $platform): bool
    {
        return Platform::isPlatform($platform);
    }

    /**
     * Get the detected platform type.
     *
     * @return string Platform type ('web', 'mobile', 'desktop')
     */
    public function getPlatform(): string
    {
        return Platform::getPlatform();
    }

    /**
     * Check if the current platform supports app redirects.
     *
     * @return bool True if platform supports app redirects
     */
    public function supportsAppRedirects(): bool
    {
        return Platform::supportsAppRedirects();
    }

    /**
     * Validate if a redirect URL is safe to use.
     *
     * Prevents open redirect attacks by checking URLs against:
     * - Configured allowed domains
     * - Frontend application domain (automatically allowed)
     *
     * @param string $url URL to validate
     * @return bool True if URL is safe for redirection
     */
    public function isValidRedirectUrl(string $url): bool
    {
        $allowedDomains = config('quvel.frontend.allowed_redirect_domains', []);
        $frontendUrl = parse_url(config('quvel.frontend.url'));

        if ($frontendUrl && isset($frontendUrl['host'])) {
            $allowedDomains[] = $frontendUrl['host'];
        }

        $parsedUrl = parse_url($url);

        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return false;
        }

        foreach ($allowedDomains as $domain) {
            if ($parsedUrl['host'] === $domain || str_ends_with($parsedUrl['host'], '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a web URL (HTTPS).
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @return string Complete HTTPS URL
     */
    private function buildWebUrl(string $path, array $queryParams): string
    {
        $base = rtrim(config('quvel.frontend.url', 'http://localhost:3000'), '/');
        $path = ltrim($path, '/');
        $url = $path ? "$base/$path" : $base;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Build a custom scheme URL.
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @return string Custom scheme URL
     */
    private function buildCustomSchemeUrl(string $path, array $queryParams): string
    {
        $webUrl = $this->buildWebUrl($path, $queryParams);
        $customScheme = config('quvel.frontend.custom_scheme');

        if (!$customScheme) {
            return $webUrl;
        }

        return preg_replace('#^https?://#', $customScheme . '://', $webUrl) ?? $webUrl;
    }

    /**
     * Show landing page with countdown to app.
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @return Response Landing page view
     */
    private function showLandingPage(string $path, array $queryParams): Response
    {
        $appUrl = $this->buildCustomSchemeUrl($path, $queryParams);
        $webUrl = $this->buildWebUrl($path, $queryParams);
        $timeout = config('quvel.frontend.landing_page_timeout', 5);
        $view = config('quvel.frontend.views.countdown_redirect', 'quvel::redirect.countdown');

        return response()->view($view, [
            'appUrl' => $appUrl,
            'webUrl' => $webUrl,
            'timeout' => $timeout,
            'platform' => Platform::getPlatform(),
        ]);
    }

}