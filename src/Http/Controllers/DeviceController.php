<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Quvel\Core\Device\Actions\DeactivateDeviceAction;
use Quvel\Core\Device\Actions\GetUserDevicesAction;
use Quvel\Core\Device\Actions\RegisterDeviceAction;
use Quvel\Core\Enums\HttpHeader;
use Quvel\Core\Facades\PlatformDetector;
use Quvel\Core\Push\Actions\UpdatePushTokenAction;

class DeviceController
{
    public function register(Request $request, RegisterDeviceAction $registerDevice): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'os_name' => 'nullable|string|max:100',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
            'push_token' => 'nullable|string',
            'push_provider' => 'nullable|string|max:50',
            'device_metadata' => 'nullable|array',
            'notification_preferences' => 'nullable|array',
        ]);

        if (!isset($validated['platform'])) {
            $platforms = PlatformDetector::getPlatforms();
            $validated['platform'] = implode(',', $platforms);
        }

        $validated['user_agent'] = $request->userAgent();

        try {
            $device = $registerDevice($validated);

            return response()->json([
                'success' => true,
                'device' => [
                    'device_id' => $device->device_id,
                    'platform' => $device->platform,
                    'is_active' => $device->is_active,
                    'created_at' => $device->created_at,
                    'updated_at' => $device->updated_at,
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    public function updatePushToken(Request $request, UpdatePushTokenAction $updatePushToken): JsonResponse
    {
        $validated = $request->validate([
            'push_token' => 'required|string',
            'push_provider' => 'required|string|max:50',
        ]);

        $deviceId = $request->header(HttpHeader::DEVICE_ID->getValue());

        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID header is required',
            ], 400);
        }

        $success = $updatePushToken(
            $deviceId,
            $validated['push_token'],
            $validated['push_provider']
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Push token updated' : 'Device not found',
        ]);
    }

    public function deactivate(Request $request, DeactivateDeviceAction $deactivateDevice): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $deviceId = $request->header(HttpHeader::DEVICE_ID->getValue());

        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID header is required',
            ], 400);
        }

        $success = $deactivateDevice(
            $deviceId,
            $validated['reason'] ?? 'Manual deactivation'
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Device deactivated' : 'Device not found',
        ]);
    }

    public function getUserDevices(Request $request, GetUserDevicesAction $getUserDevices): JsonResponse
    {
        $user = $request->user();

        try {
            $devices = $getUserDevices($user?->id);

            return response()->json([
                'success' => true,
                'devices' => $devices->map(fn ($device): array => [
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'platform' => $device->platform,
                    'last_seen_at' => $device->last_seen_at,
                    'is_active' => $device->is_active,
                    'created_at' => $device->created_at,
                ]),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 401);
        }
    }
}
