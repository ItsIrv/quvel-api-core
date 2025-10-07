<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Quvel\Core\Models\UserDevice;

interface PushDriver
{
    /**
     * Send push notification to a device.
     */
    public function send(UserDevice $device, string $title, string $body, array $data = []): bool;

    /**
     * Check if this driver supports the given platform.
     */
    public function supports(string $platform): bool;

    /**
     * Get the driver name.
     */
    public function getName(): string;

    /**
     * Check if the driver is properly configured.
     */
    public function isConfigured(): bool;
}