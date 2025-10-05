<?php

use Quvel\Core\Logs\ContextualLogger;
use Quvel\Core\Logs\SanitizedContext;

if (!function_exists('clog')) {
    /**
     * Get a contextual logger instance.
     */
    function clog(?string $prefix = null, ?string $channel = null): ContextualLogger
    {
        $logger = app(ContextualLogger::class);

        if ($prefix) {
            return $logger->withPrefix($prefix);
        }

        if ($channel) {
            return $logger->channel($channel, $prefix);
        }

        return $logger;
    }
}

