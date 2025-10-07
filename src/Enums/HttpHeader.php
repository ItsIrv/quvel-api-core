<?php

declare(strict_types=1);

namespace Quvel\Core\Enums;

/**
 * HTTP headers used throughout the framework.
 * Header values can be configured via the core config.
 */
enum HttpHeader: string
{
    /**
     * Standard Accept-Language header for locale negotiation.
     */
    case ACCEPT_LANGUAGE = 'Accept-Language';

    /**
     * Custom header for distributed tracing ID.
     */
    case TRACE_ID = 'X-Trace-ID';

    /**
     * Custom header for platform detection (web, capacitor, electron, etc.).
     */
    case PLATFORM = 'X-Platform';

    /**
     * Custom header for server-side rendering API key.
     * Used for internal request authentication.
     */
    case SSR_KEY = 'X-SSR-Key';

    /**
     * Custom header for device identification.
     * Used to identify specific device for targeted notifications.
     */
    case DEVICE_ID = 'X-Device-ID';

    /**
     * Custom header for push notification token.
     * Used to register and update device push tokens.
     */
    case PUSH_TOKEN = 'X-Push-Token';

    /**
     * Get the configured header value or use the default.
     */
    public function getValue(): string
    {
        $configKey = match ($this) {
            self::TRACE_ID => 'quvel.headers.trace_id',
            self::PLATFORM => 'quvel.headers.platform',
            self::SSR_KEY => 'quvel.headers.ssr_key',
            self::DEVICE_ID => 'quvel.headers.device_id',
            self::PUSH_TOKEN => 'quvel.headers.push_token',
            default => null,
        };

        if ($configKey && function_exists('config')) {
            $configValue = config($configKey);

            return $configValue ?? $this->value;
        }

        return $this->value;
    }
}