<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class RoleLiteServiceProvider extends LaravelServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-role-lite.php', 'oltrematica-role-lite');

        $this->publishes([
            __DIR__.'/../config/laravel-role-lite.php' => config_path('oltrematica-role-lite.php'),
        ], 'oltrematica-role-lite-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_roles_table.php' => $this->getMigrationFileName('_1_create_roles_table.php'),
            __DIR__.'/../database/migrations/create_role_model_table.php' => $this->getMigrationFileName('_2_create_role_model_table.php'),
        ], 'permission-migrations');

    }

    public function getMigrationFileName(string $name): string
    {
        return database_path('migrations/'.date('Y_m_d_His').'_'.str_replace('.stub', '', $name));
    }
    public function register()
    {
    }
}
