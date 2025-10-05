<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Illuminate\Http\Request;

/**
 * Contract for internal request validation.
 */
interface InternalRequestValidator
{
    /**
     * Check if the request is internal/trusted.
     */
    public function isInternalRequest(Request $request): bool;

    /**
     * Check if the request IP is trusted.
     */
    public function isValidIp(Request $request): bool;

    /**
     * Check if the request has a valid internal API key.
     */
    public function isValidApiKey(Request $request): bool;
}