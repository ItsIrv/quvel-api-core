<?php

declare(strict_types=1);

namespace Quvel\Core\Database;

use Illuminate\Database\Schema\Blueprint;
use Quvel\Core\Facades\PublicId;

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
            $col = PublicId::schema($this, $column);

            $col->unique($column);

            if ($index) {
                $col->index($column);
            }

            return $col;
        });
    }
}
