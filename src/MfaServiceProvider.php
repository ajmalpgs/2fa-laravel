<?php

namespace TwoFA\LaravelMfa;

use TwoFA\LaravelMfa\Contracts\MfaProviderInterface;
use TwoFA\LaravelMfa\Contracts\MfaRequirementResolverInterface;
use TwoFA\LaravelMfa\Http\Middleware\EnsureMfaVerified;
use TwoFA\LaravelMfa\Http\Middleware\RequireMfa;
use TwoFA\LaravelMfa\Http\Middleware\SetMfaLocale;
use TwoFA\LaravelMfa\Services\MfaManager;
use TwoFA\LaravelMfa\Support\RateLimiters;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MfaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mfa.php', 'mfa');
        $this->app->singleton(MfaRequirementResolverInterface::class, config('mfa.requirement_resolver'));
        $this->app->bind(MfaProviderInterface::class, function () {
            return $this->app->make(config('mfa.providers.'.config('mfa.provider')));
        });
        $this->app->singleton('mfa', MfaManager::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('mfa.locale', SetMfaLocale::class);
        $router->aliasMiddleware('mfa.required', RequireMfa::class);
        $router->aliasMiddleware('mfa.verified', EnsureMfaVerified::class);
        RateLimiters::register();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mfa');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'mfa');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([__DIR__.'/../config/mfa.php' => config_path('mfa.php')], 'mfa-config');
        $this->publishes([__DIR__.'/../resources/views' => resource_path('views/vendor/mfa')], 'mfa-views');
        $this->publishes([__DIR__.'/../resources/lang' => resource_path('lang/vendor/mfa')], 'mfa-lang');
    }
}
