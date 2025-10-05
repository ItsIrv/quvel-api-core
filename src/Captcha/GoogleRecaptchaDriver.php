<?php

declare(strict_types=1);

namespace Quvel\Core\Captcha;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpClient;
use Quvel\Core\Contracts\CaptchaDriverInterface;

/**
 * Google reCAPTCHA v3 handler.
 */
class GoogleRecaptchaDriver implements CaptchaDriverInterface
{
    public function __construct(
        private readonly HttpClient $http
    ) {
    }

    public function verify(string $token, ?string $ip = null, ?string $action = null): CaptchaVerificationResult
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (!$secretKey) {
            return CaptchaVerificationResult::failure([CaptchaVerificationResult::ERROR_MISSING_SECRET]);
        }

        try {
            $response = $this->http
                ->timeout(config('quvel.captcha.timeout', 30))
                ->asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            $data = $response->json();

            if (!$data || !is_array($data)) {
                return CaptchaVerificationResult::failure([CaptchaVerificationResult::ERROR_NETWORK_ERROR]);
            }

            if (!($data['success'] ?? false)) {
                $errorCodes = $data['error-codes'] ?? [CaptchaVerificationResult::ERROR_INVALID_RESPONSE];
                return CaptchaVerificationResult::failure($errorCodes);
            }

            return CaptchaVerificationResult::success(
                score: $data['score'] ?? null,
                action: $data['action'] ?? $action,
                challengeTimestamp: $data['challenge_ts'] ?? null,
                hostname: $data['hostname'] ?? null
            );

        } catch (ConnectionException) {
            return CaptchaVerificationResult::failure([CaptchaVerificationResult::ERROR_NETWORK_ERROR]);
        }
    }

    public function supportsScoring(): bool
    {
        return true;
    }

    public function getDefaultScoreThreshold(): ?float
    {
        return 0.5;
    }
}