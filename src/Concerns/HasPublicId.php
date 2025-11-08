<?php

declare(strict_types=1);

namespace Quvel\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Quvel\Core\Facades\PublicId;

/**
 * Trait for models that need public IDs (UUID/ULID) alongside auto-increment IDs.
 */
trait HasPublicId
{
    /**
     * Boot the trait.
     */
    protected static function bootHasPublicId(): void
    {
        static::creating(static function (Model $model): void {
            if (empty($model->{$model->getPublicIdColumn()})) {
                $model->{$model->getPublicIdColumn()} = $model->generatePublicId();
            }
        });
    }

    /**
     * Get the column name for the public ID.
     */
    public function getPublicIdColumn(): string
    {
        return $this->publicIdColumn ?? 'public_id';
    }

    /**
     * Generate a new public ID.
     */
    public function generatePublicId(): string
    {
        return PublicId::generate();
    }

    /**
     * Find a model by its public ID.
     */
    public static function findByPublicId(string $publicId): ?static
    {
        $instance = new static();

        return static::where($instance->getPublicIdColumn(), $publicId)->first();
    }

    /**
     * Find a model by its public ID or fail.
     */
    public static function findByPublicIdOrFail(string $publicId): static
    {
        $instance = new static();

        return static::where($instance->getPublicIdColumn(), $publicId)->firstOrFail();
    }

    /**
     * Get the route key name for Laravel route model binding.
     */
    public function getRouteKeyName(): string
    {
        return $this->getPublicIdColumn();
    }

    /**
     * Scope query to find by public ID.
     */
    public function scopeWherePublicId($query, string $publicId)
    {
        return $query->where($this->getPublicIdColumn(), $publicId);
    }
}
