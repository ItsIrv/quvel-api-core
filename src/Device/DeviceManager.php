<?php

declare(strict_types=1);

namespace Quvel\Core\Device;

use Quvel\Core\Contracts\DeviceManager as DeviceManagerContract;
use Quvel\Core\Events\DeviceRegistered;
use Quvel\Core\Events\DeviceRemoved;
use Quvel\Core\Models\UserDevice;
use Illuminate\Support\Collection;
use RuntimeException;

class DeviceManager implements DeviceManagerContract
{
    public function registerDevice(array $deviceData): UserDevice
    {
        if (isset($deviceData['user_id'])) {
            $maxDevices = config('quvel.devices.max_devices_per_user', 10);
            $userDeviceCount = UserDevice::forUser($deviceData['user_id'])->active()->count();

            if ($userDeviceCount >= $maxDevices) {
                throw new RuntimeException("Maximum number of devices ($maxDevices) reached for this user");
            }
        }

        $device = UserDevice::updateOrCreate(
            ['device_id' => $deviceData['device_id']],
            array_merge($deviceData, [
                'is_active' => true,
                'last_seen_at' => now(),
            ])
        );

        if ($device->wasRecentlyCreated) {
            DeviceRegistered::dispatch(
                deviceId: $device->device_id,
                userId: $device->user_id,
                platform: $device->platform,
                deviceName: $device->device_name,
                pushToken: $device->push_token
            );
        }

        return $device;
    }

    public function findDevice(string $deviceId): ?UserDevice
    {
        return UserDevice::where('device_id', $deviceId)->first();
    }

    public function getUserDevices(?int $userId): Collection
    {
        if (!$userId) {
            return collect();
        }

        return UserDevice::forUser($userId)
            ->active()
            ->get();
    }

    public function updatePushToken(string $deviceId, string $pushToken, string $provider): bool
    {
        return UserDevice::where('device_id', $deviceId)
            ->update([
                'push_token' => $pushToken,
                'push_provider' => $provider,
                'last_seen_at' => now(),
            ]) > 0;
    }

    public function deactivateDevice(string $deviceId, string $reason = 'Manual deactivation'): bool
    {
        $device = $this->findDevice($deviceId);

        if (!$device) {
            return false;
        }

        $device->deactivate();

        DeviceRemoved::dispatch(
            deviceId: $device->device_id,
            userId: $device->user_id,
            reason: $reason
        );

        return true;
    }

    public function updateLastSeen(string $deviceId): void
    {
        UserDevice::where('device_id', $deviceId)
            ->update(['last_seen_at' => now()]);
    }

    public function cleanupInactiveDevices(): int
    {
        $days = config('quvel.devices.cleanup_inactive_after_days', 90);
        $devices = UserDevice::where('last_seen_at', '<', now()->subDays($days))
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($devices as $device) {
            if ($this->deactivateDevice($device->device_id, 'Automatic cleanup - inactive')) {
                $count++;
            }
        }

        return $count;
    }
}