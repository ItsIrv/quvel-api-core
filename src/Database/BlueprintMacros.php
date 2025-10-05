<?php

declare(strict_types=1);

namespace Quvel\Core\Database;

use Illuminate\Database\Schema\Blueprint;

/**
 * Database blueprint macros for common column patterns.
 */
class BlueprintMacros
{
    /**
     * Register all macros.
     */
    public static function register(): void
    {
        static::registerPublicIdMacro();
    }

    /**
     * Register the publicId macro for optimized public ID columns.
     */
    protected static function registerPublicIdMacro(): void
    {
        Blueprint::macro('publicId', function (string $column = 'public_id', bool $index = true) {
            /** @var Blueprint $this */
            $driver = config('quvel.public_id.driver', 'ulid');

            $col = match ($driver) {
                'ulid' => $this->char($column, 26),
                'uuid' => $this->uuid($column),
                default => $this->char($column, 26),
            };

            $col->unique();

            if ($index) {
                $col->index();
            }

            return $col;
        });
    }
}