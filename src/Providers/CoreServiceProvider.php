<?php

declare(strict_types=1);

namespace Quvel\Core\Providers;

use Exception;
use Quvel\Core\Http\Middleware\ConfigGate;
use Quvel\Core\Http\Middleware\LocaleMiddleware;
use Quvel\Core\Http\Middleware\RequireInternalRequest;
use Quvel\Core\Http\Middleware\TraceMiddleware;
use Quvel\Core\Http\Middleware\VerifyCaptcha;
use Quvel\Core\Captcha\CaptchaManager;
use Quvel\Core\Services\InternalRequestValidator;
use Quvel\Core\Services\RedirectService;
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

        $this->app->singleton(InternalRequestValidator::class);
        $this->app->singleton(RedirectService::class, function () {
            $service = new RedirectService();
            $service->setBaseUrl(config('quvel.frontend.url', 'http://localhost:3000'));
            $service->setCustomScheme(config('quvel.frontend.custom_scheme'));

            return $service;
        });

        $this->app->singleton(CaptchaManager::class);
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

        $router = $this->app['router'];

        $middlewareConfig = config('quvel.middleware.auto_register', []);

        if ($middlewareConfig['config_gate'] ?? true) {
            $router->aliasMiddleware('config-gate', ConfigGate::class);
        }

        if ($middlewareConfig['locale'] ?? true) {
            $router->aliasMiddleware('locale', LocaleMiddleware::class);
        }

        if ($middlewareConfig['trace'] ?? true) {
            $router->aliasMiddleware('trace', TraceMiddleware::class);
        }

        if ($middlewareConfig['captcha'] ?? true) {
            $router->aliasMiddleware('captcha', VerifyCaptcha::class);
        }

        if ($middlewareConfig['internal_only'] ?? true) {
            $router->aliasMiddleware('internal-only', RequireInternalRequest::class);
        }
    }
}