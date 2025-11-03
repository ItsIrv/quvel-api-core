<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Quvel\Core\Captcha\CaptchaVerificationResult;

/**
 * Contract for captcha verification.
 */
interface CaptchaVerifier
{
    /**
     * Verify a captcha token.
     */
    public function verify(string $token, ?string $ip = null, ?string $action = null): CaptchaVerificationResult;

    /**
     * Check if captcha verification is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Check if the driver supports scoring.
     */
    public function supportsScoring(): bool;

    /**
     * Get the default score threshold.
     */
    public function getDefaultScoreThreshold(): ?float;
}
