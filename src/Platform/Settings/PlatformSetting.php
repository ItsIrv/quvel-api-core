<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Platform setting model.
 *
 * @property int $id
 * @property string $platform
 * @property array $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
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
     * Get settings for multiple platforms with inheritance.
     * Inheritance order: shared → platform1 → platform2 → platform3...
     */
    public static function getForPlatforms(array $platforms): array
    {
        $allSettings = [self::getShared()];

        if (!empty($platforms)) {
            $platformRecords = self::whereIn('platform', $platforms)->get();

            foreach ($platforms as $platform) {
                $record = $platformRecords->firstWhere('platform', $platform);

                if ($record && !empty($record->settings)) {
                    $allSettings[] = $record->settings;
                }
            }
        }

        return array_replace_recursive(...$allSettings);
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
