<?php

declare(strict_types=1);

namespace Quvel\Core\Platform;

enum PlatformTag: string
{
    // Runtime/Container
    case WEB = 'web';
    case CAPACITOR = 'capacitor';
    case CORDOVA = 'cordova';
    case ELECTRON = 'electron';
    case TAURI = 'tauri';

    // Operating System
    case IOS = 'ios';
    case ANDROID = 'android';
    case MACOS = 'macos';
    case WINDOWS = 'windows';
    case LINUX = 'linux';

    // Form Factor
    case MOBILE = 'mobile';
    case TABLET = 'tablet';
    case DESKTOP = 'desktop';

    // Screen Sizes (Quasar breakpoints)
    case SCREEN_XS = 'screen:xs';
    case SCREEN_SM = 'screen:sm';
    case SCREEN_MD = 'screen:md';
    case SCREEN_LG = 'screen:lg';
    case SCREEN_XL = 'screen:xl';

    /**
     * Maps all specific platforms to one of the 3 main modes: web, mobile, desktop.
     */
    public function getMainMode(): string
    {
        return match ($this) {
            self::MOBILE,
            self::TABLET,
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

            self::WEB,
            self::SCREEN_XS,
            self::SCREEN_SM,
            self::SCREEN_MD,
            self::SCREEN_LG,
            self::SCREEN_XL => self::WEB->value,
        };
    }
}
