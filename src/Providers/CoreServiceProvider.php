<?php

declare(strict_types=1);

namespace Quvel\Core\Providers;

use Illuminate\Contracts\Http\Kernel;
use Quvel\Core\Captcha\CaptchaManager;
use Quvel\Core\Database\BlueprintMacros;
use Quvel\Core\Contracts\CaptchaManager as CaptchaManagerContract;
use Quvel\Core\Contracts\InternalRequestValidator as InternalRequestValidatorContract;
use Quvel\Core\Contracts\LocaleManager as LocaleManagerContract;
use Quvel\Core\Contracts\PublicIdGenerator as PublicIdGeneratorContract;
use Quvel\Core\Contracts\RedirectService as RedirectServiceContract;
use Quvel\Core\Contracts\TraceManager as TraceManagerContract;
use Quvel\Core\Locale\LocaleManager;
use Quvel\Core\Logs\ContextualLogger;
use Quvel\Core\Platform\Detector;
use Quvel\Core\PublicId\PublicIdManager;
use Quvel\Core\Services\InternalRequestValidator;
use Quvel\Core\Redirect\RedirectService;
use Quvel\Core\Tracing\TraceManager;
use Illuminate\Support\ServiceProvider;

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

        $this->app->singleton(CaptchaManager::class);
        $this->app->singleton(CaptchaManagerContract::class, CaptchaManager::class);

        $this->app->singleton(InternalRequestValidator::class);
        $this->app->singleton(InternalRequestValidatorContract::class, InternalRequestValidator::class);

        $this->app->singleton(LocaleManager::class);
        $this->app->singleton(LocaleManagerContract::class, LocaleManager::class);

        $this->app->singleton(TraceManager::class);
        $this->app->singleton(TraceManagerContract::class, TraceManager::class);

        $this->app->singleton(RedirectService::class);
        $this->app->singleton(RedirectServiceContract::class, RedirectService::class);

        $this->app->singleton(PublicIdManager::class);
        $this->app->singleton(PublicIdGeneratorContract::class, PublicIdManager::class);

        $this->app->singleton(ContextualLogger::class);

        $this->app->scoped(Detector::class);
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
                __DIR__ . '/../lang' => lang_path('vendor/quvel'),
            ], 'quvel-lang');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'quvel');

        $this->registerMiddleware();
        $this->registerDatabaseMacros();
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