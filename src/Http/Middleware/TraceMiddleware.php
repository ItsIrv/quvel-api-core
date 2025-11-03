<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Quvel\Core\Contracts\TraceIdGenerator as TraceIdGeneratorContract;
use Quvel\Core\Enums\HttpHeader;

/**
 * Middleware to capture and propagate trace IDs for distributed tracing.
 */
class TraceMiddleware
{
    public function __construct(
        private readonly TraceIdGeneratorContract $traceIdGenerator
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$this->traceIdGenerator->isEnabled()) {
            return $next($request);
        }

        $traceId = $this->traceIdGenerator->getOrGenerateTraceId($request);

        $this->traceIdGenerator->addToContext($traceId);

        $response = $next($request);

        $response->headers->set(HttpHeader::TRACE_ID->getValue(), $traceId);

        return $response;
    }
}
