<?php

declare(strict_types=1);

namespace Quvel\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $deviceId,
        public readonly ?int $userId,
        public readonly string $platform,
        public readonly ?string $deviceName = null,
        public readonly ?string $pushToken = null
    ) {}
}