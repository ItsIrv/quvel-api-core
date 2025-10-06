<?php

use Quvel\Core\Facades\ContextualLog;
use Quvel\Core\Logs\ContextualLogger;
use Quvel\Core\Logs\SanitizedContext;

if (!function_exists('clog')) {
    /**
     * Get a contextual logger instance.
     */
    function clog(?string $prefix = null, ?string $channel = null): ContextualLogger
    {
        if ($channel) {
            return ContextualLog::channel($channel, $prefix);
        }

        $logger = app(ContextualLogger::class);

        if ($prefix) {
            return $logger->withPrefix($prefix);
        }

        return $logger;
    }
}

