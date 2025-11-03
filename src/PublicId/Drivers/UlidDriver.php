<?php

declare(strict_types=1);

namespace Quvel\Core\PublicId\Drivers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Str;
use Quvel\Core\Contracts\PublicIdGenerator;

/**
 * ULID driver for public ID generation.
 */
class UlidDriver implements PublicIdGenerator
{
    public function generate(): string
    {
        return (string) Str::ulid();
    }

    public function isValid(string $id): bool
    {
        return Str::isUlid($id);
    }

    public function schema(Blueprint $blueprint, string $column = 'public_id'): ColumnDefinition
    {
        return $blueprint->char($column, 26);
    }
}
