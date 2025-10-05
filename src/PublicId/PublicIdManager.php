<?php

declare(strict_types=1);

namespace Quvel\Core\PublicId;

use Quvel\Core\Contracts\PublicIdGenerator;
use Quvel\Core\PublicId\Drivers\UlidDriver;
use Quvel\Core\PublicId\Drivers\UuidDriver;

/**
 * Public ID manager with configurable drivers.
 */
class PublicIdManager implements PublicIdGenerator
{
    private ?PublicIdGenerator $driver = null;

    public function generate(): string
    {
        return $this->getDriver()->generate();
    }

    public function isValid(string $id): bool
    {
        return $this->getDriver()->isValid($id);
    }

    /**
     * Get the configured driver instance.
     */
    private function getDriver(): PublicIdGenerator
    {
        if ($this->driver === null) {
            $driverType = config('quvel.public_id.driver', 'ulid');

            $this->driver = match ($driverType) {
                'uuid' => app(UuidDriver::class),
                default => app(UlidDriver::class),
            };
        }

        return $this->driver;
    }
}