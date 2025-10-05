<?php

return [
    /**
     * Captcha Configuration
     */
    'captcha' => [
        /**
         * Whether captcha verification is enabled globally
         */
        'enabled' => env('CAPTCHA_ENABLED', true),

        /**
         * Captcha driver class to use.
         */
        'driver' => env('CAPTCHA_DRIVER', \Quvel\Core\Captcha\GoogleRecaptchaDriver::class),

        /**
         * reCAPTCHA v3 score threshold (0.0-1.0)
         */
        'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),

        /**
         * HTTP timeout in seconds for captcha verification
         */
        'timeout' => env('CAPTCHA_TIMEOUT', 30),
    ],

    /**
     * HTTP Headers Configuration
     */
    'headers' => [
        /**
         * Custom header for distributed tracing.
         * Set to null to use the default 'X-Trace-ID'
         */
        'trace_id' => env('HEADER_TRACE_ID'),

        /**
         * Custom header for platform detection.
         * Set to null to use the default 'X-Platform'
         */
        'platform' => env('HEADER_PLATFORM'),

        /**
         * Custom header for SSR API key.
         * Set to null to use the default 'X-SSR-Key'
         */
        'ssr_key' => env('HEADER_SSR_KEY'),
    ],

    /**
     * Security Configuration
     */
    'security' => [
        /**
         * Internal request validation settings
         */
        'internal_requests' => [
            /**
             * List of trusted IP addresses for internal requests
             */
            'trusted_ips' => explode(',', env('SECURITY_TRUSTED_IPS', '127.0.0.1,::1')),

            /**
             * API key required for internal requests
             */
            'api_key' => env('SECURITY_API_KEY'),

            /**
             * Bypass IP validation (not recommended for production)
             */
            'disable_ip_check' => env('SECURITY_DISABLE_IP_CHECK', false),

            /**
             * Bypass API key validation (not recommended for production)
             */
            'disable_key_check' => env('SECURITY_DISABLE_KEY_CHECK', false),
        ],
    ],

    /**
     * Logging Helpers Configuration
     */
    'logging' => [
        /**
         * Global sanitization rules for SanitizedContext
         */
        'sanitization_rules' => [
            'password' => 'remove',
            'password_confirmation' => 'remove',
            'token' => 'hash',
            'api_key' => 'hash',
            'secret' => 'remove',
            'email' => 'domain_only',
            'credit_card' => 'mask',
            'ssn' => 'mask',
            'social_security_number' => 'mask',
        ],

        /**
         * Enable global sanitization rules by default
         */
        'use_global_sanitization' => env('LOG_USE_GLOBAL_SANITIZATION', true),
    ],

    /**
     * Locale Configuration
     */
    'locale' => [
        /**
         * Allowed application locales (comma-separated list)
         */
        'allowed_locales' => explode(',', env('LOCALE_ALLOWED', 'en')),

        /**
         * Default locale when detection fails
         */
        'fallback_locale' => env('LOCALE_FALLBACK', 'en'),

        /**
         * Convert region-specific locales to base language (en-US -> en)
         */
        'normalize_locales' => env('LOCALE_NORMALIZE', true),
    ],

    /**
     * Frontend Integration Configuration
     */
    'frontend' => [
        /**
         * Base URL for frontend application
         */
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),

        /**
         * Custom URL scheme for mobile/desktop deep links (e.g., 'myapp')
         * Use null to disable deep linking
         */
        'custom_scheme' => env('FRONTEND_CUSTOM_SCHEME'),

        /**
         * Internal API URL for server-side requests from SSR
         */
        'internal_api_url' => env('FRONTEND_INTERNAL_API_URL'),
    ],

    /**
     * Distributed Tracing Configuration
     */
    'tracing' => [
        /**
         * Enable distributed tracing with UUID generation
         */
        'enabled' => env('TRACING_ENABLED', true),

        /**
         * Accept trace IDs from incoming requests
         */
        'accept_external_trace_ids' => env('TRACING_ACCEPT_EXTERNAL', true),
    ],

    /**
     * Public ID Configuration
     */
    'public_id' => [
        /**
         * Driver for public ID generation (ulid, uuid)
         */
        'driver' => env('PUBLIC_ID_DRIVER', 'ulid'),

        /**
         * Default column name for public IDs
         */
        'column' => env('PUBLIC_ID_COLUMN', 'public_id'),
    ],

    /**
     * Middleware Configuration
     */
    'middleware' => [
        /**
         * Middleware aliases that will be registered
         */
        'aliases' => [
            'config-gate' => \Quvel\Core\Http\Middleware\ConfigGate::class,
            'locale' => \Quvel\Core\Http\Middleware\LocaleMiddleware::class,
            'trace' => \Quvel\Core\Http\Middleware\TraceMiddleware::class,
            'captcha' => \Quvel\Core\Http\Middleware\VerifyCaptcha::class,
            'internal-only' => \Quvel\Core\Http\Middleware\RequireInternalRequest::class,
        ],

        /**
         * Middleware groups - automatically added to Laravel's middleware groups
         */
        'groups' => [
            'web' => [
                'locale',
                'trace',
            ],
            'api' => [
                'locale',
                'trace',
            ],
        ],

    ],
];