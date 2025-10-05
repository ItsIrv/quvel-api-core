<?php

declare(strict_types=1);

namespace Quvel\Core\PublicId\Drivers;

use Illuminate\Support\Str;
use Quvel\Core\Contracts\PublicIdGenerator;

/**
 * UUID driver for public ID generation.
 */
class UuidDriver implements PublicIdGenerator
{
    public function generate(): string
    {
        return (string) Str::uuid();
    }

    public function isValid(string $id): bool
    {
        return Str::isUuid($id);
    }
}