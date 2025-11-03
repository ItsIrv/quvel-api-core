<?php

declare(strict_types=1);

namespace Quvel\Core\Captcha;

use Illuminate\Http\Request;
use Quvel\Core\Contracts\CaptchaDriverInterface;
use Quvel\Core\Contracts\CaptchaVerifier as CaptchaVerifierContract;
use Quvel\Core\Events\CaptchaVerifyFailed;
use Quvel\Core\Events\CaptchaVerifySuccess;

/**
 * Captcha verification service.
 * Coordinates driver verification and event dispatching.
 */
class CaptchaVerifier implements CaptchaVerifierContract
{
    private ?CaptchaDriverInterface $driver = null;

    public function verify(string $token, ?string $ip = null, ?string $action = null): CaptchaVerificationResult
    {
        if (!$this->isEnabled()) {
            return CaptchaVerificationResult::failure([
                'Captcha is disabled'
            ]);
        }

        $result = $this->getDriver()->verify($token, $ip, $action);

        $request = app(Request::class);
        $ipAddress = $ip ?? $request->ip();
        $userAgent = $request->userAgent();

        if ($result->isSuccessful()) {
            CaptchaVerifySuccess::dispatch(
                $token,
                $result->score ?? 1.0,
                $ipAddress,
                $userAgent
            );
        } else {
            $reason = implode(', ', $result->errorCodes) ?: 'Unknown error';

            CaptchaVerifyFailed::dispatch(
                $token,
                $reason,
                $ipAddress,
                $userAgent
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

    private function getDriver(): CaptchaDriverInterface
    {
        if ($this->driver === null) {
            $driverClass = config('quvel.captcha.driver', GoogleRecaptchaDriver::class);
            $this->driver = app($driverClass);
        }

        return $this->driver;
    }
}