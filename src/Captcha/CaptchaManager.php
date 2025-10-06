<?php

declare(strict_types=1);

namespace Quvel\Core\Captcha;

use Illuminate\Http\Request;
use Quvel\Core\Contracts\CaptchaDriverInterface;
use Quvel\Core\Contracts\CaptchaManager as CaptchaManagerContract;
use Quvel\Core\Events\CaptchaVerifyFailed;
use Quvel\Core\Events\CaptchaVerifySuccess;

/**
 * Simple captcha manager.
 */
class CaptchaManager implements CaptchaManagerContract
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

        $result = $this->getDriver()->verify($token, $ip, $action);

        $request = app(Request::class);
        $ipAddress = $ip ?? $request->ip();
        $userAgent = $request->userAgent();

        if ($result->isSuccessful()) {
            CaptchaVerifySuccess::dispatch(
                token: $token,
                score: $result->score ?? 1.0,
                ipAddress: $ipAddress,
                userAgent: $userAgent
            );
        } else {
            $reason = implode(', ', $result->errorCodes) ?: 'Unknown error';

            CaptchaVerifyFailed::dispatch(
                token: $token,
                reason: $reason,
                ipAddress: $ipAddress,
                userAgent: $userAgent
            );
        }

        return $result;
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