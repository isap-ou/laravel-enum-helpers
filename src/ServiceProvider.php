<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Artisan;
use IsapOu\EnumHelpers\Commands\ExportEnumsToJsCommand;
use IsapOu\EnumHelpers\Commands\MigrateEnumsCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'enum-helpers'
        );
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('enum-helpers.php'),
        ], 'enum-helpers-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateEnumsCommand::class,
                ExportEnumsToJsCommand::class,
            ]);
        }

        if (! $this->app->runningUnitTests() && $this->app['config']->get('enum-helpers.post_migrate', true)) {
            $this->app['events']->listen(MigrationsEnded::class, fn () => Artisan::call(MigrateEnumsCommand::class));
        }

        AboutCommand::add('Laravel enum Helpers', static fn () => ['Version' => '1.0.0']);
    }
}
