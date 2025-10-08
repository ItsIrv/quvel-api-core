<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Middleware;

use Quvel\Core\Captcha\CaptchaVerifier;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to verify captcha tokens.
 * Protects endpoints from bots and automated attacks.
 */
class VerifyCaptcha
{
    public function __construct(
        private readonly CaptchaVerifier $captchaManager
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $inputField = 'captcha_token'): mixed
    {
        if (!$this->captchaManager->isEnabled()) {
            return $next($request);
        }

        $token = $request->input($inputField);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => __('quvel::messages.captcha.token_required'),
            ], 422);
        }

        $result = $this->captchaManager->verify((string) $token, $request->ip());

        if ($result->isFailed()) {
            return response()->json([
                'success' => false,
                'message' => __('quvel::messages.captcha.verification_failed'),
                'errors' => $result->errorCodes,
            ], 422);
        }

        if ($result->hasScore()) {
            $threshold = config('quvel.captcha.score_threshold', 0.5);

            if (!$result->meetsScoreThreshold($threshold)) {
                return response()->json([
                    'success' => false,
                    'message' => __('quvel::messages.captcha.score_too_low'),
                    'score' => $result->score,
                ], 422);
            }
        }

        return $next($request);
    }
}