<?php

declare(strict_types=1);

namespace Quvel\Core\Push\Drivers;

use JsonException;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Models\UserDevice;
use Quvel\Core\Platform\PlatformTag;
use RuntimeException;

class ApnsDriver implements PushDriver
{
    public function getName(): string
    {
        return 'apns';
    }

    public function supports(string $platform): bool
    {
        return in_array($platform, [
            PlatformTag::IOS->value,
            PlatformTag::MACOS->value,
        ], true);
    }

    public function isConfigured(): bool
    {
        $config = config('quvel.push.apns');

        return $this->validateConfig($config);
    }

    /**
     * @throws JsonException
     */
    public function send(UserDevice $device, string $title, string $body, array $data = []): bool
    {
        if (!$device->hasValidPushToken() || !$device->push_token) {
            return false;
        }

        $config = config('quvel.push.apns');

        if (!$this->validateConfig($config)) {
            return false;
        }

        $payload = [
            'aps' => [
                'alert' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'sound' => 'default',
            ],
        ];

        if ($data !== []) {
            $payload = array_merge($payload, $data);
        }

        return $this->sendToApns($device->push_token, $payload, $config);
    }

    private function validateConfig(array $config): bool
    {
        $required = ['key_path', 'key_id', 'team_id', 'bundle_id'];

        if (array_any($required, fn ($key): bool => empty($config[$key]))) {
            return false;
        }

        return file_exists($config['key_path']);
    }

    /**
     * @throws JsonException
     */
    private function sendToApns(string $deviceToken, array $payload, array $config): bool
    {
        $environment = $config['environment'] ?? 'sandbox';
        $host = $environment === 'production'
            ? 'api.push.apple.com'
            : 'api.sandbox.push.apple.com';

        $jwt = $this->generateJwt($config);

        $headers = [
            'authorization: bearer ' . $jwt,
            'apns-topic: ' . $config['bundle_id'],
            'content-type: application/json',
        ];

        $url = sprintf('https://%s/3/device/%s', $host, $deviceToken);

        $ch = curl_init();

        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_TIMEOUT => 30,
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * @throws JsonException
     */
    private function generateJwt(array $config): string
    {
        $header = [
            'alg' => 'ES256',
            'kid' => $config['key_id'],
        ];

        $payload = [
            'iss' => $config['team_id'],
            'iat' => time(),
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = $this->signWithES256(
            $headerEncoded . '.' . $payloadEncoded,
            $config['key_path']
        );

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function signWithES256(string $data, string $keyPath): string
    {
        $keyContents = file_get_contents($keyPath);

        if ($keyContents === false) {
            throw new RuntimeException('Failed to read APNS key file');
        }

        $privateKey = openssl_pkey_get_private($keyContents);

        if ($privateKey === false) {
            throw new RuntimeException('Failed to load APNS private key');
        }

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $this->base64UrlEncode($signature);
    }
}
