<?php

declare(strict_types=1);

namespace Quvel\Core\Locale;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Quvel\Core\Contracts\LocaleResolver as LocaleResolverContract;
use Quvel\Core\Enums\HttpHeader;

/**
 * Locale resolution service.
 * Detects, validates, and sets application locale from requests.
 */
class LocaleResolver implements LocaleResolverContract
{
    public function detectLocale(Request $request): ?string
    {
        $headerValue = $request->header(HttpHeader::ACCEPT_LANGUAGE->getValue());

        if (!$headerValue) {
            return config('quvel.locale.fallback_locale', 'en');
        }

        if (str_contains($headerValue, ',')) {
            return $this->parseAcceptLanguageHeader($headerValue);
        }

        return $headerValue;
    }

    public function isAllowedLocale(string $locale): bool
    {
        $allowedLocales = config('quvel.locale.allowed_locales', ['en']);

        if (in_array($locale, $allowedLocales, true)) {
            return true;
        }

        if (config('quvel.locale.normalize_locales', true)) {
            $normalized = $this->normalizeLocale($locale);
            $normalizedAllowed = array_map([$this, 'normalizeLocale'], $allowedLocales);

            return in_array($normalized, $normalizedAllowed, true);
        }

        return false;
    }

    public function setLocale(string $locale): void
    {
        App::setLocale($locale);
    }

    public function getLocale(): string
    {
        return App::getLocale();
    }

    public function normalizeLocale(string $locale): string
    {
        return strtolower(explode('-', $locale)[0]);
    }

    /**
     * Parse Accept-Language header with quality values.
     */
    private function parseAcceptLanguageHeader(string $header): ?string
    {
        $locales = [];

        foreach (explode(',', $header) as $locale) {
            $parts = explode(';q=', trim($locale));
            $lang = trim($parts[0]);
            $quality = isset($parts[1]) ? (float) $parts[1] : 1.0;

            if ($this->isAllowedLocale($lang)) {
                $locales[$lang] = $quality;
            }
        }

        if (empty($locales)) {
            return config('quvel.locale.fallback_locale', 'en');
        }

        arsort($locales);

        return array_key_first($locales);
    }
}
