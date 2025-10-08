<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Quvel\Core\Contracts\LocaleResolver as LocaleResolverContract;

/**
 * Enhanced locale middleware with configurable locale detection.
 */
class LocaleMiddleware
{
    public function __construct(
        private readonly LocaleResolverContract $localeManager
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $this->localeManager->detectLocale($request);

        if ($locale && $this->localeManager->isAllowedLocale($locale)) {
            $this->localeManager->setLocale($this->localeManager->normalizeLocale($locale));
        }

        return $next($request);
    }
}