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
