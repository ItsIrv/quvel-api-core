<?php

declare(strict_types=1);

namespace Quvel\Core\Tracing;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Quvel\Core\Contracts\TraceManager as TraceManagerContract;
use Quvel\Core\Enums\HttpHeader;

/**
 * Trace manager for distributed tracing with UUID generation.
 */
class TraceManager implements TraceManagerContract
{
    /**
     * Custom trace ID generator.
     */
    protected static ?Closure $customGenerator = null;

    /**
     * Custom trace ID validator.
     */
    protected static ?Closure $customValidator = null;

    /**
     * Set a custom trace ID generator.
     */
    public static function setGenerator(Closure $generator): void
    {
        static::$customGenerator = $generator;
    }

    /**
     * Set a custom trace ID validator.
     */
    public static function setValidator(Closure $validator): void
    {
        static::$customValidator = $validator;
    }

    public function getOrGenerateTraceId(Request $request): string
    {
        $headerTraceId = $request->header(HttpHeader::TRACE_ID->getValue());

        if ($headerTraceId && $this->shouldAcceptTraceHeader($request, $headerTraceId)) {
            return (string) $headerTraceId;
        }

        return $this->generateTraceId();
    }

    public function generateTraceId(): string
    {
        if (static::$customGenerator !== null) {
            return (static::$customGenerator)();
        }

        return (string) Str::uuid();
    }


    public function addToContext(string $traceId): void
    {
        Context::add('trace_id', $traceId);
    }

    public function isEnabled(): bool
    {
        return config('quvel.tracing.enabled', true);
    }

    /**
     * Determine if we should accept the trace header from request.
     */
    public function shouldAcceptTraceHeader(Request $request, string $traceId): bool
    {
        if (!config('quvel.tracing.accept_external_trace_ids', true)) {
            return false;
        }

        if (empty($traceId)) {
            return false;
        }

        if (static::$customValidator !== null) {
            return (static::$customValidator)($traceId);
        }

        return Str::isUuid($traceId);
    }
}