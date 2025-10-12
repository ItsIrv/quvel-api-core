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
         * Custom header for an SSR API key.
         * Set to null to use the default 'X-SSR-Key'
         */
        'ssr_key' => env('HEADER_SSR_KEY'),

        /**
         * Custom header for device identification.
         * Set to null to use the default 'X-Device-ID'
         */
        'device_id' => env('HEADER_DEVICE_ID'),

        /**
         * Custom header for the push notification token.
         * Set to null to use the default 'X-Push-Token'
         */
        'push_token' => env('HEADER_PUSH_TOKEN'),

        /**
         * Custom header for Accept-Language.
         */
        'accept_language' => env('HEADER_ACCEPT_LANGUAGE'),
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
         * Custom URL scheme for mobile/desktop apps (e.g., 'myapp')
         * Used when redirect_mode is 'custom_scheme'
         */
        'custom_scheme' => env('FRONTEND_CUSTOM_SCHEME'),

        /**
         * Internal API URL for server-side requests from SSR
         */
        'internal_api_url' => env('FRONTEND_INTERNAL_API_URL'),

        /**
         * Redirect mode for getting users back to apps
         * - 'universal_links': Use HTTPS URLs (requires App Site Association setup)
         * - 'custom_scheme': Use custom scheme URLs (myapp://)
         * - 'landing_page': Show intermediate page with countdown/button
         * - 'web_only': Always use web URLs
         */
        'redirect_mode' => env('FRONTEND_REDIRECT_MODE', 'universal_links'),

        /**
         * Timeout in seconds for landing page countdown
         * Only used when redirect_mode is 'landing_page'
         */
        'landing_page_timeout' => env('FRONTEND_LANDING_PAGE_TIMEOUT', 5),

        /**
         * View configuration for redirect pages
         */
        'views' => [
            /**
             * Countdown redirect view for redirect_mode 'landing_page'
             */
            'countdown_redirect' => env('FRONTEND_COUNTDOWN_VIEW', 'quvel::redirect.countdown'),
        ],

        /**
         * Theme configuration
         */
        'theme' => [
            /**
             * Primary brand color for buttons and accents
             */
            'primary_color' => env('FRONTEND_THEME_PRIMARY', '#3b82f6'),
        ],

        /**
         * Allowed domains for secure redirects
         */
        'allowed_redirect_domains' => explode(',', env('FRONTEND_ALLOWED_REDIRECT_DOMAINS', '')),
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
     * Device Management Configuration
     */
    'devices' => [
        /**
         * Enable device registration and management
         */
        'enabled' => env('DEVICES_ENABLED', true),

        /**
         * Allow anonymous device tracking (without user authentication)
         */
        'allow_anonymous' => env('DEVICES_ALLOW_ANONYMOUS', false),

        /**
         * Clean up inactive devices after this many days
         */
        'cleanup_inactive_after_days' => env('DEVICES_CLEANUP_DAYS', 90),

        /**
         * Maximum devices allowed per user
         */
        'max_devices_per_user' => env('DEVICES_MAX_PER_USER', 10),
    ],

    /**
     * Push Notification Configuration
     */
    'push' => [
        /**
         * Enable the push notification system
         */
        'enabled' => env('PUSH_ENABLED', true),

        /**
         * Push notification drivers to enable
         */
        'drivers' => explode(',', env('PUSH_DRIVERS', 'fcm,apns,web')),

        /**
         * Firebase Cloud Messaging configuration
         */
        'fcm' => [
            'server_key' => env('FCM_SERVER_KEY'),
            'project_id' => env('FCM_PROJECT_ID'),
        ],

        /**
         * Apple Push Notification Service configuration
         */
        'apns' => [
            'key_path' => env('APNS_KEY_PATH'),
            'key_id' => env('APNS_KEY_ID'),
            'team_id' => env('APNS_TEAM_ID'),
            'bundle_id' => env('APNS_BUNDLE_ID'),
            'environment' => env('APNS_ENVIRONMENT', 'sandbox'),
        ],

        /**
         * Web Push configuration
         */
        'web_push' => [
            'vapid_subject' => env('VAPID_SUBJECT'),
            'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
            'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
        ],

        /**
         * Batch size for bulk notifications
         */
        'batch_size' => env('PUSH_BATCH_SIZE', 1000),
    ],

    /**
     * Device Targeting Configuration
     */
    'targeting' => [
        /**
         * Default notification scope
         * - 'requesting_device': Only notify the device that made the request
         * - 'all_user_devices': Notify all devices for the user
         */
        'default_scope' => env('TARGETING_DEFAULT_SCOPE', 'requesting_device'),
    ],

    /**
     * API Routes Configuration
     */
    'routes' => [
        /**
         * Device management routes
         * Publish routes file to customize: php artisan vendor:publish --tag=quvel-routes
         */
        'devices' => [
            'enabled' => env('QUVEL_DEVICE_ROUTES_ENABLED', false),
            'prefix' => 'devices',
            'name' => 'devices.',
            'middleware' => ['api', 'auth:sanctum'],
        ],

        /**
         * Platform settings routes.
         * Provides server-driven configuration for clients
         */
        'platform_settings' => [
            'enabled' => env('QUVEL_PLATFORM_SETTINGS_ROUTES_ENABLED', false),
            'prefix' => 'platform-settings',
            'name' => 'platform-settings.',
            'middleware' => [],
        ],
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
            'platform-detection' => \Quvel\Core\Http\Middleware\PlatformDetectionMiddleware::class,
            'device-detection' => \Quvel\Core\Http\Middleware\DeviceDetectionMiddleware::class,
        ],

        /**
         * Middleware groups - automatically added to Laravel's middleware groups
         */
        'groups' => [
            'web' => [
                'platform-detection',
                'device-detection',
                'locale',
                'trace',
            ],
            'api' => [
                'platform-detection',
                'device-detection',
                'locale',
                'trace',
            ],
        ],
    ],

    /**
     * Platform Settings Configuration
     * Server-driven configuration for clients to fetch on boot.
     * Supports version requirements, feature flags, rotating keys, etc.
     */
    'platform_settings' => [
        /**
         * Driver for storing platform settings
         * - 'config': Read from config files (static, requires redeployment)
         * - 'database': Read from a database (dynamic, update without redeployment)
         */
        'driver' => env('PLATFORM_SETTINGS_DRIVER', 'config'),

        /**
         * Database table name (used when the driver is 'database')
         */
        'table' => 'platform_settings',

        /**
         * Shared settings applied to all platforms
         * These are merged with platform-specific settings
         * (Only used when the driver is 'config')
         */
        'shared' => [
            'api_version' => '1.0.0',
            'maintenance_mode' => false,
            'features' => [
                // Example feature flags
                // 'new_ui' => true,
                // 'beta_features' => false,
            ],
        ],

        /**
         * Platform-specific settings
         * Keyed by PlatformType
         * Platform-specific settings override shared settings
         */
        'platforms' => [
            \Quvel\Core\Platform\PlatformType::WEB->value => [
                // Example: Web-specific settings
            ],
            \Quvel\Core\Platform\PlatformType::MOBILE->value => [
                // Example: Generic mobile settings
            ],
            \Quvel\Core\Platform\PlatformType::DESKTOP->value => [
                // Example: Desktop-specific settings
            ],

            // Add more platform-specific settings here
        ],
    ],
];