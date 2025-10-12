<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Quvel\Core\Platform\PlatformType;

/**
 * Platform setting model.
 *
 * @property int $id
 * @property string $platform
 * @property array $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PlatformSetting extends Model
{
    protected $fillable = [
        'platform',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function getTable(): string
    {
        return config('quvel.platform_settings.table', 'platform_settings');
    }

    /**
     * Get settings for a platform with inheritance.
     * Inheritance order: shared → main mode → specific platform
     */
    public static function getForPlatform(string $platform): array
    {
        $shared = self::getShared();

        $platformType = PlatformType::tryFrom($platform);
        $mainMode = $platformType?->getMainMode() ?? $platform;

        $mainModeSettings = [];
        if ($mainMode !== $platform) {
            $mainModeSettings = self::where('platform', $mainMode)->first()?->settings ?? [];
        }

        $platformSettings = self::where('platform', $platform)->first()?->settings ?? [];

        return array_replace_recursive($shared, $mainModeSettings, $platformSettings);
    }

    /**
     * Get shared settings.
     */
    public static function getShared(): array
    {
        return self::where('platform', 'shared')->first()?->settings ?? [];
    }

    /**
     * Create or update platform settings.
     */
    public static function upsertForPlatform(string $platform, array $settings): self
    {
        return self::updateOrCreate(
            ['platform' => $platform],
            ['settings' => $settings]
        );
    }
}
