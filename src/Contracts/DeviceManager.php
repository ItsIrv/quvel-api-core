<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Quvel\Core\Models\UserDevice;
use Illuminate\Support\Collection;

interface DeviceManager
{
    /**
     * Register a new device or update existing one.
     */
    public function registerDevice(array $deviceData): UserDevice;

    /**
     * Find device by device ID.
     */
    public function findDevice(string $deviceId): ?UserDevice;

    /**
     * Get all active devices for a user.
     */
    public function getUserDevices(?int $userId): Collection;

    /**
     * Update device push token.
     */
    public function updatePushToken(string $deviceId, string $pushToken, string $provider): bool;

    /**
     * Deactivate a device.
     */
    public function deactivateDevice(string $deviceId, string $reason = 'Manual deactivation'): bool;

    /**
     * Update device last seen timestamp.
     */
    public function updateLastSeen(string $deviceId): void;

    /**
     * Clean up inactive devices.
     */
    public function cleanupInactiveDevices(): int;
}