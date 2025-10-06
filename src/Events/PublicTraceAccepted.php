<?php

declare(strict_types=1);

namespace Quvel\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublicTraceAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $traceId,
        public readonly string $endpoint,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}