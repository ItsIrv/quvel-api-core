<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Quvel\Core\Actions\RegisterDeviceAction;
use Quvel\Core\Actions\UpdatePushTokenAction;
use Quvel\Core\Actions\DeactivateDeviceAction;
use Quvel\Core\Actions\GetUserDevicesAction;
use Quvel\Core\Facades\Platform;
use Quvel\Core\Enums\HttpHeader;

class DeviceController
{
    public function __construct() {}

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
            $validated['platform'] = Platform::getPlatform();
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
            'reason' => 'nullable|string|max:255'
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
                'devices' => $devices->map(function ($device) {
                    return [
                        'device_id' => $device->device_id,
                        'device_name' => $device->device_name,
                        'platform' => $device->platform,
                        'last_seen_at' => $device->last_seen_at,
                        'is_active' => $device->is_active,
                        'created_at' => $device->created_at,
                    ];
                }),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}