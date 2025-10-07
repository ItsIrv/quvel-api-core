<?php

declare(strict_types=1);

namespace Quvel\Core\Platform;

enum PlatformType: string
{
    case WEB = 'web';
    case MOBILE = 'mobile';
    case ANDROID = 'android';
    case IOS = 'ios';
    case DESKTOP = 'desktop';
    case MACOS = 'macos';
    case WINDOWS = 'windows';
    case LINUX = 'linux';
    case ELECTRON = 'electron';
    case TAURI = 'tauri';
    case CAPACITOR = 'capacitor';
    case CORDOVA = 'cordova';

    public static function getWebPlatforms(): array
    {
        return [self::WEB->value];
    }

    public static function getMobilePlatforms(): array
    {
        return [
            self::MOBILE->value,
            self::ANDROID->value,
            self::IOS->value,
            self::CAPACITOR->value,
            self::CORDOVA->value,
        ];
    }

    public static function getDesktopPlatforms(): array
    {
        return [
            self::DESKTOP->value,
            self::MACOS->value,
            self::WINDOWS->value,
            self::LINUX->value,
            self::ELECTRON->value,
            self::TAURI->value,
        ];
    }

    public static function getApplePlatforms(): array
    {
        return [self::IOS->value, self::MACOS->value];
    }

    public static function getAndroidPlatforms(): array
    {
        return [self::ANDROID->value, self::MOBILE->value];
    }

    public function isWeb(): bool
    {
        return in_array($this->value, self::getWebPlatforms(), true);
    }

    public function isMobile(): bool
    {
        return in_array($this->value, self::getMobilePlatforms(), true);
    }

    public function isDesktop(): bool
    {
        return in_array($this->value, self::getDesktopPlatforms(), true);
    }

    public function isApple(): bool
    {
        return in_array($this->value, self::getApplePlatforms(), true);
    }

    public function isAndroid(): bool
    {
        return in_array($this->value, self::getAndroidPlatforms(), true);
    }

    /**
     * Get the main platform mode for this platform type.
     * Maps all specific platforms to one of the 3 main modes: web, mobile, desktop.
     */
    public function getMainMode(): string
    {
        return match ($this) {
            self::MOBILE,
            self::ANDROID,
            self::IOS,
            self::CAPACITOR,
            self::CORDOVA => self::MOBILE->value,

            self::DESKTOP,
            self::MACOS,
            self::WINDOWS,
            self::LINUX,
            self::ELECTRON,
            self::TAURI => self::DESKTOP->value,

            self::WEB => self::WEB->value,
        };
    }
}