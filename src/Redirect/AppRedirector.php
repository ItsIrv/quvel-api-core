<?php

declare(strict_types=1);

namespace Quvel\Core\Redirect;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Quvel\Core\Contracts\AppRedirector as AppRedirectorContract;
use Quvel\Core\Facades\PlatformDetector;

/**
 * Frontend redirection service for web and multi-platform apps.
 *
 * ## Methods
 *
 * **redirect() / getUrl()**: Standard URL generation and redirects
 * - Returns web URLs (HTTPS)
 * - Use for regular API responses and redirects
 * - Client applications handle routing appropriately
 *
 * **redirectToApp()**: Browser-to-app transition redirects
 * - Use when transitioning from device browser back to installed app
 * - Supports multiple redirect modes for different app configurations
 * - Provides flexibility for various deep linking scenarios
 *
 * ## Redirect Modes (for redirectToApp)
 *
 * **universal_links**: Use regular HTTPS URLs
 * - Requires App Site Association (iOS) / Android App Links setup
 * - Same URL works in browser and opens app seamlessly
 * - Best UX but requires domain configuration
 * - Example: https://example.com/dashboard → opens app or web
 *
 * **custom_scheme**: Use custom scheme URLs
 * - Replaces https:// with a custom scheme (myapp://)
 * - Phone asks, "Open in app?" when clicked
 * - Works without domain setup, less seamless
 * - Example: myapp://dashboard → phone prompts to open app
 *
 * **landing_page**: Show intermediate page with countdown
 * - User lands on page with countdown timer
 * - Automatically attempts app launch with manual fallback button
 * - Provides best accessibility and user control
 *
 * **web_only**: Always use web URLs
 * - Never tries to open app, stays in browser
 * - For when you want to keep users on the web
 * - If deep links are set up externally, this will be ignored as that's device functionality.
 *
 * @see config/quvel.php for configuration options
 */
class AppRedirector implements AppRedirectorContract
{
    /**
     * Redirect to a URL.
     *
     * Returns a standard web URL redirect. Client applications handle routing.
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     */
    public function redirect(string $path = '', array $queryParams = []): RedirectResponse|Response
    {
        return redirect()->away($this->buildWebUrl($path, $queryParams));
    }

    /**
     * Redirect with a message parameter.
     *
     * Convenience method that adds a 'message' query parameter.
     *
     * @param string $path Path relative to frontend base URL
     * @param string $message Message to include in query parameters
     * @param array $extraParams Additional query parameters
     */
    public function redirectWithMessage(
        string $path,
        string $message,
        array $extraParams = []
    ): RedirectResponse|Response {
        $queryParams = array_merge(['message' => $message], $extraParams);

        return $this->redirect($path, $queryParams);
    }

    /**
     * Redirect from browser context to installed app.
     *
     * Handles browser-to-app transitions using a configured redirect mode.
     * Supports universal links, custom schemes, and intermediate landing pages.
     *
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @param string|null $redirectMode Override default redirect mode
     */
    public function redirectToApp(
        string $path = '',
        array $queryParams = [],
        ?string $redirectMode = null
    ): RedirectResponse|Response {
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
     * @param string $path Path relative to frontend base URL
     * @param array $queryParams Query parameters to append
     * @return string Complete URL
     */
    public function getUrl(string $path = '', array $queryParams = []): string
    {
        return $this->buildWebUrl($path, $queryParams);
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
        $frontendUrl = parse_url((string) config('quvel.frontend.url'));

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
        $base = rtrim((string) config('quvel.frontend.url', 'http://localhost:3000'), '/');
        $path = ltrim($path, '/');
        $url = $path !== '' && $path !== '0' ? sprintf('%s/%s', $base, $path) : $base;

        if ($queryParams !== []) {
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
            'platforms' => PlatformDetector::getPlatforms(),
        ]);
    }
}
