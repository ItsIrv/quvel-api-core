<?php

declare(strict_types=1);

namespace Quvel\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CaptchaVerifyFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $token,
        public readonly string $reason,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}