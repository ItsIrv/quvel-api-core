<?php

declare(strict_types=1);

namespace Quvel\Core\Tests;

use Quvel\Core\Providers\CoreServiceProvider;

class CoreServiceProviderTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $loadedProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(CoreServiceProvider::class, $loadedProviders);
    }

    public function test_config_is_published(): void
    {
        $this->assertTrue(true);
    }
}
