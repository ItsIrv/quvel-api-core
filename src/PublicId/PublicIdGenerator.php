<?php

declare(strict_types=1);

namespace Quvel\Core\PublicId;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Quvel\Core\Contracts\PublicIdGenerator as PublicIdGeneratorContract;

/**
 * Default PublicIdGenerator service that delegates to the manager's default driver.
 */
class PublicIdGenerator implements PublicIdGeneratorContract
{
    public function __construct(
        private readonly PublicIdManager $manager
    ) {
    }

    public function generate(): string
    {
        return $this->manager->driver()->generate();
    }

    public function isValid(string $id): bool
    {
        return $this->manager->driver()->isValid($id);
    }

    public function schema(Blueprint $blueprint, string $column = 'public_id'): ColumnDefinition
    {
        return $this->manager->driver()->schema($blueprint, $column);
    }
}
