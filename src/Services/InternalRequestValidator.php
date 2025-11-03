<?php

declare(strict_types=1);

namespace Quvel\Core\Services;

use Quvel\Core\Contracts\InternalRequestValidator as InternalRequestValidatorContract;
use Quvel\Core\Enums\HttpHeader;
use Quvel\Core\Events\InternalRequestFailed;
use Quvel\Core\Events\InternalRequestPassed;
use Illuminate\Http\Request;
use Closure;

/**
 * Service to validate if a request is internal/trusted.
 */
class InternalRequestValidator implements InternalRequestValidatorContract
{
    /**
     * Custom validation closure.
     */
    protected static ?Closure $customValidator = null;

    /**
     * Set a custom validation closure.
     */
    public static function setValidator(?Closure $validator): void
    {
        static::$customValidator = $validator;
    }

    /**
     * Check if the request is internal/trusted.
     */
    public function isInternalRequest(Request $request): bool
    {
        if (static::$customValidator !== null) {
            $isValid = (static::$customValidator)($request);
            $this->dispatchEvent($request, $isValid, $isValid ? null : 'Custom validator failed');

            return $isValid;
        }

        $isValidIp = $this->isValidIp($request);
        $isValidApiKey = $this->isValidApiKey($request);
        $isValid = $isValidIp && $isValidApiKey;

        $reason = null;

        if (!$isValid) {
            if (!$isValidIp && !$isValidApiKey) {
                $reason = 'Invalid IP and API key';
            } elseif (!$isValidIp) {
                $reason = 'Invalid IP address';
            } else {
                $reason = 'Invalid API key';
            }
        }

        $this->dispatchEvent($request, $isValid, $reason);

        return $isValid;
    }

    /**
     * Check if the request IP is trusted.
     */
    public function isValidIp(Request $request): bool
    {
        if (config('quvel.security.internal_requests.disable_ip_check', false)) {
            return true;
        }

        $ip = $request->ip();
        $trustedIps = config('quvel.security.internal_requests.trusted_ips', ['127.0.0.1', '::1']);

        return in_array($ip, $trustedIps, true);
    }

    /**
     * Check if the request has a valid internal API key.
     */
    public function isValidApiKey(Request $request): bool
    {
        if (config('quvel.security.internal_requests.disable_key_check', false)) {
            return true;
        }

        $expectedKey = config('quvel.security.internal_requests.api_key');

        if (!$expectedKey) {
            return false;
        }

        $providedKey = $request->header(HttpHeader::SSR_KEY->getValue());

        return hash_equals($expectedKey, $providedKey ?? '');
    }

    /**
     * Dispatch the appropriate event based on validation result.
     */
    private function dispatchEvent(Request $request, bool $isValid, ?string $reason): void
    {
        $token = $request->header(HttpHeader::SSR_KEY->getValue());
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        if ($isValid) {
            InternalRequestPassed::dispatch(
                $token ?? 'none',
                $ipAddress,
                $userAgent
            );
        } else {
            InternalRequestFailed::dispatch(
                $reason ?? 'Unknown validation failure',
                $token,
                $ipAddress,
                $userAgent
            );
        }
    }
}