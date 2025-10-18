<?php

declare(strict_types=1);

namespace Quvel\Core\Push\Drivers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Models\UserDevice;
use Quvel\Core\Platform\PlatformTag;

class FcmDriver implements PushDriver
{
    /**
     * @throws ConnectionException
     */
    public function send(UserDevice $device, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('quvel.push.fcm.server_key');

        if (!$serverKey || !$device->push_token) {
            return false;
        }

        $payload = [
            'to' => $device->push_token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);

        return $response->successful();
    }

    public function supports(string $platform): bool
    {
        return in_array($platform, [
            PlatformTag::MOBILE->value,
            PlatformTag::ANDROID->value,
            PlatformTag::WEB->value,
        ], true);
    }

    public function getName(): string
    {
        return 'fcm';
    }

    public function isConfigured(): bool
    {
        return !empty(config('quvel.push.fcm.server_key'));
    }
}