<?php

declare(strict_types=1);

namespace Quvel\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Quvel\Core\Contracts\DeviceManager;
use Quvel\Core\Enums\HttpHeader;

class DeviceDetectionMiddleware
{
    public function __construct(
        private readonly DeviceManager $deviceManager
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (!config('quvel.devices.enabled', true)) {
            return $next($request);
        }

        $deviceId = $request->header(HttpHeader::DEVICE_ID->getValue());
        $pushToken = $request->header(HttpHeader::PUSH_TOKEN->getValue());

        if ($deviceId) {
            Context::add('device_id', $deviceId);

            $request->attributes->set('device_id', $deviceId);

            $this->deviceManager->updateLastSeen($deviceId);

            $device = $this->deviceManager->findDevice($deviceId);
            if ($device) {
                Context::add('device', [
                    'id' => $device->device_id,
                    'platform' => $device->platform,
                    'user_id' => $device->user_id,
                ]);

                $request->attributes->set('device', $device);
            }
        }

        if ($pushToken) {
            Context::add('push_token', $pushToken);

            $request->attributes->set('push_token', $pushToken);
        }

        return $next($request);
    }
}