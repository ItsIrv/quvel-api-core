<?php

declare(strict_types=1);

namespace Quvel\Core\Redirect;

use Illuminate\Http\Request;
use Quvel\Core\Contracts\RedirectService as RedirectServiceContract;
use Quvel\Core\Enums\HttpHeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Multi-platform redirect service for web, capacitor, cordova, electron, etc.
 */
class RedirectService implements RedirectServiceContract
{
    public function redirect(string $path = '', array $queryParams = []): RedirectResponse|Response
    {
        $url = $this->buildUrl($path, $queryParams);

        if ($this->shouldUseCustomScheme()) {
            return app('response')->view('redirect', [
                'url' => $url,
                'platform' => $this->getPlatform(),
                'scheme' => $this->getCustomScheme(),
            ]);
        }

        return app('redirect')->away($url);
    }

    public function redirectWithMessage(string $path, string $message, array $extraParams = []): RedirectResponse|Response
    {
        $queryParams = array_merge(['message' => $message], $extraParams);
        return $this->redirect($path, $queryParams);
    }

    public function getUrl(string $path = '', array $queryParams = []): string
    {
        return $this->buildUrl($path, $queryParams);
    }

    public function getUrlWithMessage(string $path, string $message, array $extraParams = []): string
    {
        $queryParams = array_merge(['message' => $message], $extraParams);
        return $this->getUrl($path, $queryParams);
    }

    public function isPlatform(string $platform): bool
    {
        return $this->getPlatform() === $platform;
    }

    public function getPlatform(): string
    {
        $request = app(Request::class);
        $platformHeader = $request->header(HttpHeader::PLATFORM->getValue());

        return match ($platformHeader) {
            'capacitor', 'cordova' => 'mobile',
            'electron', 'tauri' => 'desktop',
            default => 'web',
        };
    }

    private function buildUrl(string $path, array $queryParams): string
    {
        $base = rtrim($this->getBaseUrl(), '/');
        $path = ltrim($path, '/');

        $url = $path ? "$base/$path" : $base;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        if ($this->shouldUseCustomScheme()) {
            $url = preg_replace('#^https?://#', $this->getCustomScheme() . '://', $url) ?? $url;
        }

        return $url;
    }

    private function shouldUseCustomScheme(): bool
    {
        return $this->getCustomScheme() !== null && $this->getPlatform() !== 'web';
    }

    private function getBaseUrl(): string
    {
        return config('quvel.frontend.url', 'http://localhost:3000');
    }

    private function getCustomScheme(): ?string
    {
        return config('quvel.frontend.custom_scheme');
    }
}