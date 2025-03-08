<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Oltrematica\RoleLite\RoleLiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName
            ): string => 'Oltrematica\\RoleLite\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $schema = $this->app['db']->connection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_RoleLite_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders($app)
    {
        return [
            RoleLiteServiceProvider::class,
        ];
    }
}
