<?php

declare(strict_types=1);

namespace Quvel\Core\Captcha;

/**
 * Result object for captcha verification operations.
 */
readonly class CaptchaVerificationResult
{
    public const string ERROR_MISSING_SECRET = 'missing-input-secret';
    public const string ERROR_INVALID_RESPONSE = 'invalid-input-response';
    public const string ERROR_NETWORK_ERROR = 'network-error';

    public function __construct(
        public bool $success,
        public ?float $score = null,
        public ?string $action = null,
        public ?string $challengeTimestamp = null,
        public ?string $hostname = null,
        public array $errorCodes = [],
    ) {
    }

    public static function success(
        ?float $score = null,
        ?string $action = null,
        ?string $challengeTimestamp = null,
        ?string $hostname = null
    ): self {
        return new self(
            success: true,
            score: $score,
            action: $action,
            challengeTimestamp: $challengeTimestamp,
            hostname: $hostname
        );
    }

    public static function failure(array $errorCodes = []): self
    {
        return new self(
            success: false,
            errorCodes: $errorCodes
        );
    }

    public function isSuccessful(): bool
    {
        return $this->success;
    }

    public function isFailed(): bool
    {
        return !$this->success;
    }

    public function hasScore(): bool
    {
        return $this->score !== null;
    }

    public function meetsScoreThreshold(float $threshold): bool
    {
        return $this->hasScore() && $this->score >= $threshold;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errorCodes);
    }
}
