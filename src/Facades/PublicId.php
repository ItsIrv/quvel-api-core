<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Quvel\Core\Contracts\PublicIdGenerator;

/**
 * Public ID generation facade.
 *
 * @method static string generate() Generate a new public ID
 * @method static string getColumn() Get the default column name for public IDs
 * @method static string getDriver() Get the current driver name
 * @method static bool isValid(string $id) Validate a public ID format
 * @method static \Illuminate\Database\Schema\Blueprint schema(\Illuminate\Database\Schema\Blueprint $blueprint, string $column = 'public_id') Define the database schema for this ID type
 *
 * @see PublicIdGenerator
 */
class PublicId extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PublicIdGenerator::class;
    }
}
