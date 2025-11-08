<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Illuminate\Support\Manager;
use Quvel\Core\Platform\Settings\Drivers\ConfigDriver;
use Quvel\Core\Platform\Settings\Drivers\DatabaseDriver;

/**
 * Platform settings manager.
 * Manages multiple storage drivers (config, database).
 */
class PlatformSettingsManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('quvel.platform_settings.driver', 'config');
    }

    /**
     * Create the config driver.
     */
    public function createConfigDriver(): ConfigDriver
    {
        return new ConfigDriver();
    }

    /**
     * Create the database driver.
     */
    public function createDatabaseDriver(): DatabaseDriver
    {
        return new DatabaseDriver();
    }
}
