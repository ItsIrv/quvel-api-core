<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

/**
 * Contract for public ID generation.
 */
interface PublicIdGenerator
{
    /**
     * Generate a new public ID.
     */
    public function generate(): string;

    /**
     * Validate a public ID format.
     */
    public function isValid(string $id): bool;

    /**
     * Define the database schema for this ID type.
     */
    public function schema(Blueprint $blueprint, string $column = 'public_id'): ColumnDefinition;
}
