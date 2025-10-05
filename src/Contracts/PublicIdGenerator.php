<?php

declare(strict_types=1);

namespace Quvel\Core\Contracts;

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
}