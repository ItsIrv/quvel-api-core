<?php

declare(strict_types=1);

namespace Quvel\Core\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $device_id
 * @property string|null $device_name
 * @property string $platform
 * @property string|null $os_name
 * @property string|null $os_version
 * @property string|null $app_version
 * @property string|null $user_agent
 * @property string|null $push_token
 * @property string|null $push_provider
 * @property array|null $device_metadata
 * @property array|null $notification_preferences
 * @property bool $is_active
 * @property Carbon|null $last_seen_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder forUser(int $userId)
 * @method static Builder active()
 */
class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'platform',
        'os_name',
        'os_version',
        'app_version',
        'user_agent',
        'push_token',
        'push_provider',
        'device_metadata',
        'notification_preferences',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'device_metadata' => 'array',
        'notification_preferences' => 'array',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    public function scopeWithPushToken(Builder $query): Builder
    {
        return $query->whereNotNull('push_token');
    }

    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function hasValidPushToken(): bool
    {
        return !empty($this->push_token) && !empty($this->push_provider);
    }
}
