<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Oltrematica\RoleLite\RoleLiteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Oltrematica\\RoleLite\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app)
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
