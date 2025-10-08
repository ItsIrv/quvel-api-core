<?php

declare(strict_types=1);

namespace Quvel\Core\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Quvel\Core\Captcha\CaptchaVerifier;
use Quvel\Core\Contracts\AppRedirector as AppRedirectorContract;
use Quvel\Core\Contracts\CaptchaVerifier as CaptchaVerifierContract;
use Quvel\Core\Contracts\Device as DeviceContract;
use Quvel\Core\Contracts\DeviceTargets as DeviceTargetsContract;
use Quvel\Core\Contracts\InternalRequestValidator as InternalRequestValidatorContract;
use Quvel\Core\Contracts\LocaleResolver as LocaleResolverContract;
use Quvel\Core\Contracts\PlatformDetector as PlatformDetectorContract;
use Quvel\Core\Contracts\PublicIdGenerator as PublicIdGeneratorContract;
use Quvel\Core\Contracts\PushManager as PushManagerContract;
use Quvel\Core\Contracts\TraceIdGenerator as TraceIdGeneratorContract;
use Quvel\Core\Database\BlueprintMacros;
use Quvel\Core\Device\Device;
use Quvel\Core\Device\DeviceTargets;
use Quvel\Core\Locale\LocaleResolver;
use Quvel\Core\Logs\ContextualLogger;
use Quvel\Core\Platform\PlatformDetector;
use Quvel\Core\PublicId\PublicIdManager;
use Quvel\Core\Push\PushManager;
use Quvel\Core\Redirect\AppRedirector;
use Quvel\Core\Services\InternalRequestValidator;
use Quvel\Core\Tracing\TraceIdGenerator;

/**
 * Core package service provider.
 */
class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/quvel.php',
            'quvel'
        );

        $this->app->singleton(CaptchaVerifier::class);
        $this->app->singleton(CaptchaVerifierContract::class, CaptchaVerifier::class);

        $this->app->singleton(InternalRequestValidator::class);
        $this->app->singleton(InternalRequestValidatorContract::class, InternalRequestValidator::class);

        $this->app->singleton(LocaleResolver::class);
        $this->app->singleton(LocaleResolverContract::class, LocaleResolver::class);

        $this->app->singleton(TraceIdGenerator::class);
        $this->app->singleton(TraceIdGeneratorContract::class, TraceIdGenerator::class);

        $this->app->singleton(AppRedirector::class);
        $this->app->singleton(AppRedirectorContract::class, AppRedirector::class);

        $this->app->singleton(PublicIdManager::class);
        $this->app->singleton(PublicIdGeneratorContract::class, PublicIdManager::class);

        $this->app->singleton(ContextualLogger::class);

        $this->app->scoped(PlatformDetector::class);
        $this->app->scoped(PlatformDetectorContract::class, PlatformDetector::class);

        $this->app->singleton(Device::class);
        $this->app->singleton(DeviceContract::class, Device::class);

        $this->app->singleton(PushManager::class);
        $this->app->singleton(PushManagerContract::class, PushManager::class);

        $this->app->singleton(DeviceTargets::class);
        $this->app->singleton(DeviceTargetsContract::class, DeviceTargets::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/quvel.php' => config_path('quvel.php'),
            ], 'quvel-config');

            $this->publishes([
                __DIR__ . '/../../lang' => lang_path('vendor/quvel'),
            ], 'quvel-lang');

            $this->publishes([
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/quvel'),
            ], 'quvel-views');

            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'quvel-migrations');

            $this->publishes([
                __DIR__ . '/../../routes/devices.php' => base_path('routes/devices.php'),
            ], 'quvel-routes');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'quvel');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'quvel');

        $this->registerMiddleware();
        $this->registerRoutes();
        $this->registerDatabaseMacros();
    }

    /**
     * Register device management routes if enabled.
     */
    protected function registerRoutes(): void
    {
        if (config('quvel.routes.devices.enabled', false)) {
            Route::middleware(config('quvel.routes.devices.middleware', []))
                ->prefix(config('quvel.routes.devices.prefix', 'api/devices'))
                ->name(config('quvel.routes.devices.name', 'devices.'))
                ->group(__DIR__ . '/../../routes/devices.php');
        }
    }

    /**
     * Register middleware aliases and add to middleware groups.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $middlewareConfig = config('quvel.middleware', []);

        // Register all middleware aliases
        $aliases = $middlewareConfig['aliases'] ?? [];
        foreach ($aliases as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }

        // Add middleware to groups automatically
        $this->addMiddlewareToGroups($middlewareConfig);
    }

    /**
     * Add middleware to web and api groups.
     */
    protected function addMiddlewareToGroups(array $config): void
    {
        $kernel = $this->app->make(Kernel::class);
        $aliases = $config['aliases'] ?? [];

        // Add to web group
        $webMiddleware = $config['groups']['web'] ?? [];
        foreach ($webMiddleware as $alias) {
            if (isset($aliases[$alias])) {
                $kernel->appendMiddlewareToGroup('web', $aliases[$alias]);
            }
        }

        // Add to api group
        $apiMiddleware = $config['groups']['api'] ?? [];
        foreach ($apiMiddleware as $alias) {
            if (isset($aliases[$alias])) {
                $kernel->appendMiddlewareToGroup('api', $aliases[$alias]);
            }
        }
    }

    /**
     * Register database blueprint macros.
     */
    protected function registerDatabaseMacros(): void
    {
        BlueprintMacros::register();
    }
}