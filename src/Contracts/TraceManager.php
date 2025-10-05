<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Illuminate\Http\Request;

/**
 * Contract for trace ID management.
 */
interface TraceManager
{
    /**
     * Get trace ID from request or generate new one.
     */
    public function getOrGenerateTraceId(Request $request): string;

    /**
     * Generate a new trace ID.
     */
    public function generateTraceId(): string;

    /**
     * Determine if we should accept the trace header from request.
     */
    public function shouldAcceptTraceHeader(Request $request, string $traceId): bool;

    /**
     * Add trace ID to context.
     */
    public function addToContext(string $traceId): void;

    /**
     * Check if tracing is enabled.
     */
    public function isEnabled(): bool;
}