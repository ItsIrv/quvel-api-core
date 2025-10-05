<?php

declare(strict_types=1);

namespace Quvel\Core\Logs;

use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Closure;
use Throwable;

/**
 * Contextual logger with configurable enrichment and sanitization.
 */
class ContextualLogger implements LoggerInterface
{
    /**
     * Context enrichers.
     */
    protected static array $enrichers = [];

    /**
     * The log channel to use.
     */
    protected string $channel;

    /**
     * The log context prefix.
     */
    protected string $contextPrefix;

    public function __construct(
        protected LogManager $logger,
        ?string $channel = null,
        string $contextPrefix = ''
    ) {
        $this->channel = $channel ?? config('quvel.logging.default_channel', 'stack');
        $this->contextPrefix = $contextPrefix;
    }

    /**
     * Add a context enricher.
     */
    public static function addEnricher(string $name, Closure $enricher): void
    {
        static::$enrichers[$name] = $enricher;
    }

    /**
     * Remove a context enricher.
     */
    public static function removeEnricher(string $name): void
    {
        unset(static::$enrichers[$name]);
    }

    /**
     * Clear all enrichers.
     */
    public static function clearEnrichers(): void
    {
        static::$enrichers = [];
    }

    /**
     * Create a new logger instance with a different channel and optional prefix.
     */
    public function channel(string $channel, ?string $prefix = null): static
    {
        return new static($this->logger, $channel, $prefix ?? $this->contextPrefix);
    }

    /**
     * Create a new logger instance with a context prefix.
     */
    public function withPrefix(string $prefix): static
    {
        return new static($this->logger, $this->channel, $prefix);
    }

    /**
     * Log an emergency message.
     */
    public function emergency($message, array|SanitizedContext $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log an alert message.
     */
    public function alert($message, array|SanitizedContext $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log a critical message.
     */
    public function critical($message, array|SanitizedContext $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log an error message.
     */
    public function error($message, array|SanitizedContext $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a warning message.
     */
    public function warning($message, array|SanitizedContext $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log a notice message.
     */
    public function notice($message, array|SanitizedContext $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log an info message.
     */
    public function info($message, array|SanitizedContext $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a debug message.
     */
    public function debug($message, array|SanitizedContext $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log a message with an arbitrary level.
     */
    public function log(mixed $level, $message, array|SanitizedContext $context = []): void
    {
        $context = $this->enrichContext($context);

        $this->logger->channel($this->channel)->log($level, $message, $context);
    }

    /**
     * Enrich the log context with additional information.
     */
    protected function enrichContext(array|SanitizedContext $context): array
    {
        if ($context instanceof SanitizedContext) {
            $context = $context->toArray();
        }

        foreach (static::$enrichers as $enricher) {
            try {
                $context = $enricher($context, $this->contextPrefix) ?: $context;
            } catch (Throwable) {
                // Silently continue if enricher fails
            }
        }

        if ($this->contextPrefix !== '' && count($context) > 0) {
            $prefixed = [];

            foreach ($context as $key => $value) {
                $prefixed["$this->contextPrefix.$key"] = $value;
            }

            return $prefixed;
        }

        return $context;
    }
}