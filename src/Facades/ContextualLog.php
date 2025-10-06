<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Quvel\Core\Logs\ContextualLogger;

/**
 * Contextual logging facade.
 *
 * @method static ContextualLogger channel(string $channel, ?string $prefix = null) Get logger for specific channel
 * @method static void emergency(string $message, array $context = []) Log emergency message
 * @method static void alert(string $message, array $context = []) Log alert message
 * @method static void critical(string $message, array $context = []) Log critical message
 * @method static void error(string $message, array $context = []) Log error message
 * @method static void warning(string $message, array $context = []) Log warning message
 * @method static void notice(string $message, array $context = []) Log notice message
 * @method static void info(string $message, array $context = []) Log info message
 * @method static void debug(string $message, array $context = []) Log debug message
 *
 * @see ContextualLogger
 */
class ContextualLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ContextualLogger::class;
    }
}