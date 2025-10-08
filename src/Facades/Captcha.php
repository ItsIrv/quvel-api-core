<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Quvel\Core\Captcha\CaptchaVerificationResult;
use Quvel\Core\Contracts\CaptchaVerifier;

/**
 * Captcha facade.
 *
 * @method static CaptchaVerificationResult verify(string $token, ?string $ip = null, ?string $action = null)
 * @method static bool isEnabled()
 * @method static bool supportsScoring()
 * @method static float|null getDefaultScoreThreshold()
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaptchaVerifier::class;
    }
}