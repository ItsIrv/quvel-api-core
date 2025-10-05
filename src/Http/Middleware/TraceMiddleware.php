<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Quvel\Core\Contracts\TraceManager as TraceManagerContract;
use Quvel\Core\Enums\HttpHeader;

/**
 * Middleware to capture and propagate trace IDs for distributed tracing.
 */
class TraceMiddleware
{
    public function __construct(
        private readonly TraceManagerContract $traceManager
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$this->traceManager->isEnabled()) {
            return $next($request);
        }

        $traceId = $this->traceManager->getOrGenerateTraceId($request);

        $this->traceManager->addToContext($traceId);

        $response = $next($request);

        $response->headers->set(HttpHeader::TRACE_ID->getValue(), $traceId);

        return $response;
    }
}