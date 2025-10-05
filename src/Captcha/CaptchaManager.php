<?php

declare(strict_types=1);

namespace Quvel\Core\Captcha;

use Quvel\Core\Contracts\CaptchaDriverInterface;

/**
 * Simple captcha manager.
 */
class CaptchaManager
{
    private ?CaptchaDriverInterface $driver = null;

    private function getDriver(): CaptchaDriverInterface
    {
        if ($this->driver === null) {
            $driverClass = config('quvel.captcha.driver', GoogleRecaptchaDriver::class);
            $this->driver = app($driverClass);
        }

        return $this->driver;
    }

    public function verify(string $token, ?string $ip = null, ?string $action = null): CaptchaVerificationResult
    {
        if (!$this->isEnabled()) {
            return CaptchaVerificationResult::success();
        }

        return $this->getDriver()->verify($token, $ip, $action);
    }

    public function isEnabled(): bool
    {
        return config('quvel.captcha.enabled', true);
    }

    public function supportsScoring(): bool
    {
        return $this->getDriver()->supportsScoring();
    }

    public function getDefaultScoreThreshold(): ?float
    {
        return $this->getDriver()->getDefaultScoreThreshold();
    }
}