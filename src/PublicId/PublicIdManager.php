<?php

declare(strict_types=1);

namespace Quvel\Core\PublicId;

use Illuminate\Support\Manager;
use Quvel\Core\PublicId\Drivers\UlidDriver;
use Quvel\Core\PublicId\Drivers\UuidDriver;

/**
 * Public ID manager with configurable drivers.
 */
class PublicIdManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('quvel.public_id.driver', 'ulid');
    }

    /**
     * Create the ULID driver.
     */
    protected function createUlidDriver(): UlidDriver
    {
        return $this->container->make(UlidDriver::class);
    }

    /**
     * Create the UUID driver.
     */
    protected function createUuidDriver(): UuidDriver
    {
        return $this->container->make(UuidDriver::class);
    }
}
