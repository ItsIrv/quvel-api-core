<?php

declare(strict_types=1);

namespace Quvel\Core\Push\Drivers;

use JsonException;
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Models\UserDevice;
use Quvel\Core\Platform\PlatformTag;
use Random\RandomException;
use RuntimeException;

class WebPushDriver implements PushDriver
{
    public function getName(): string
    {
        return 'web_push';
    }

    public function supports(string $platform): bool
    {
        return in_array($platform, [
            PlatformTag::WEB->value,
            PlatformTag::DESKTOP->value,
        ], true);
    }

    public function isConfigured(): bool
    {
        $config = config('quvel.push.web_push');

        return $this->validateConfig($config);
    }

    /**
     * @throws JsonException|RandomException
     */
    public function send(UserDevice $device, string $title, string $body, array $data = []): bool
    {
        if (!$device->hasValidPushToken() || !$device->push_token) {
            return false;
        }

        $config = config('quvel.push.web_push');

        if (!$this->validateConfig($config)) {
            return false;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => $data['icon'] ?? null,
            'badge' => $data['badge'] ?? null,
            'image' => $data['image'] ?? null,
            'tag' => $data['tag'] ?? null,
            'data' => $data['custom_data'] ?? [],
        ];

        return $this->sendWebPush($device->push_token, $payload, $config);
    }

    private function validateConfig(array $config): bool
    {
        return !empty($config['vapid_subject']) &&
               !empty($config['vapid_public_key']) &&
               !empty($config['vapid_private_key']);
    }

    /**
     * @throws JsonException
     * @throws RandomException
     */
    private function sendWebPush(string $endpoint, array $payload, array $config): bool
    {
        $parsedEndpoint = parse_url($endpoint);

        if ($parsedEndpoint === false || !isset($parsedEndpoint['scheme'], $parsedEndpoint['host'])) {
            return false;
        }

        $audience = $parsedEndpoint['scheme'] . '://' . $parsedEndpoint['host'];

        $vapidHeader = $this->generateVapidHeader($audience, $config);
        $encryptedPayload = $this->encryptPayload(json_encode($payload, JSON_THROW_ON_ERROR));

        $headers = [
            'Authorization: ' . $vapidHeader,
            'Content-Type: application/octet-stream',
            'Content-Encoding: aes128gcm',
            'TTL: 86400',
        ];

        $ch = curl_init();

        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $encryptedPayload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return in_array($httpCode, [200, 201, 204], true);
    }

    /**
     * @throws JsonException
     */
    private function generateVapidHeader(string $audience, array $config): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'ES256',
        ];

        $payload = [
            'aud' => $audience,
            'exp' => time() + 12 * 3600,
            'sub' => $config['vapid_subject'],
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = $this->signWithVapidKey(
            $headerEncoded . '.' . $payloadEncoded,
            $config['vapid_private_key']
        );

        $jwt = $headerEncoded . '.' . $payloadEncoded . '.' . $signature;

        return 'vapid t=' . $jwt . ', k=' . $config['vapid_public_key'];
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function signWithVapidKey(string $data, string $privateKey): string
    {
        $key = $this->convertVapidKeyToPem($privateKey);
        $privateKeyResource = openssl_pkey_get_private($key);

        if ($privateKeyResource === false) {
            throw new RuntimeException('Failed to load VAPID private key');
        }

        openssl_sign($data, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        return $this->base64UrlEncode($signature);
    }

    private function convertVapidKeyToPem(string $vapidKey): string
    {
        $keyData = base64_decode(strtr($vapidKey, '-_', '+/'));

        return "-----BEGIN EC PRIVATE KEY-----\n" .
               chunk_split(base64_encode($keyData), 64) .
               "-----END EC PRIVATE KEY-----\n";
    }

    /**
     * @throws RandomException
     */
    private function encryptPayload(string $payload): string
    {
        $salt = random_bytes(16);
        $key = random_bytes(16);
        $nonce = random_bytes(12);

        $encrypted = openssl_encrypt(
            $payload,
            'aes-128-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($encrypted === false) {
            throw new RuntimeException('Failed to encrypt payload');
        }

        return $salt . pack('N', 4096) . pack('C', strlen($key)) . $key . $encrypted . $tag;
    }
}
