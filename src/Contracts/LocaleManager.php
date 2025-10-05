<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Illuminate\Http\Request;

/**
 * Contract for locale management.
 */
interface LocaleManager
{
    /**
     * Detect locale from request.
     */
    public function detectLocale(Request $request): ?string;

    /**
     * Check if locale is allowed.
     */
    public function isAllowedLocale(string $locale): bool;

    /**
     * Set the application locale.
     */
    public function setLocale(string $locale): void;

    /**
     * Get the current application locale.
     */
    public function getLocale(): string;

    /**
     * Normalize locale (e.g., "en-US" -> "en").
     */
    public function normalizeLocale(string $locale): string;
}