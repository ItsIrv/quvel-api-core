<?php

declare(strict_types=1);

namespace Quvel\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaptchaVerifySuccess
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $token,
        public readonly float $score,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}