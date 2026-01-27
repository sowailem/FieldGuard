<?php

namespace Sowailem\FieldGuard;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Sowailem\FieldGuard\Commands\ClearCacheCommand;
use Sowailem\FieldGuard\Middleware\EnforceFieldSecurityMiddleware;

class FieldGuardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('fieldguard', function ($app) {
            return new FieldGuard();
        });

        $this->mergeConfigFrom(__DIR__.'/../config/fieldguard.php', 'fieldguard');
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if (config('fieldguard.api.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        if (config('fieldguard.automatic_enforcement', false)) {
            $this->app->make('fieldguard')->enableAutomaticEnforcement();
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fieldguard.php' => config_path('fieldguard.php'),
            ], 'fieldguard-config');

            $this->publishes([
                __DIR__.'/../routes/api.php' => base_path('routes/fieldguard.php'),
            ], 'fieldguard-routes');

            $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

            $this->commands([
                ClearCacheCommand::class,
            ]);
        }

        $this->app['router']->aliasMiddleware('fieldguard', EnforceFieldSecurityMiddleware::class);
    }
}
