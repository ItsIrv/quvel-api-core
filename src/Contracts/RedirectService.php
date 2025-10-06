<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Contract for multi-platform redirect service.
 */
interface RedirectService
{
    /**
     * Smart redirect based on platform and configured redirect mode.
     */
    public function redirect(string $path = '', array $queryParams = []): RedirectResponse|Response;

    /**
     * Redirect with a message parameter.
     */
    public function redirectWithMessage(string $path, string $message, array $extraParams = []): RedirectResponse|Response;

    /**
     * Redirect user back to their app (for browser contexts like Socialite).
     */
    public function redirectToApp(string $path = '', array $queryParams = [], ?string $redirectMode = null): RedirectResponse|Response;

    /**
     * Get the frontend URL without redirecting.
     */
    public function getUrl(string $path = '', array $queryParams = []): string;

    /**
     * Get URL with message parameter.
     */
    public function getUrlWithMessage(string $path, string $message, array $extraParams = []): string;

    /**
     * Check if current request is from a specific platform.
     */
    public function isPlatform(string $platform): bool;

    /**
     * Get the detected platform.
     */
    public function getPlatform(): string;

    /**
     * Check if the current platform supports app redirects.
     */
    public function supportsAppRedirects(): bool;

    /**
     * Validate if a redirect URL is safe.
     */
    public function isValidRedirectUrl(string $url): bool;
}