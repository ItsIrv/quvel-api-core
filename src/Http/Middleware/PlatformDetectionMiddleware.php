<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Quvel\Core\Facades\Platform;

/**
 * Platform detection middleware that adds platform info to request context.
 */
class PlatformDetectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, bool $includeUserAgent = false): mixed
    {
        $platform = Platform::getPlatform();

        Context::add('platform', $platform);

        $request->attributes->set('platform', $platform);

        if ($includeUserAgent) {
            $userAgent = $request->userAgent();

            Context::add('user_agent', $userAgent);

            $request->attributes->set('user_agent', $userAgent);
        }

        return $next($request);
    }
}