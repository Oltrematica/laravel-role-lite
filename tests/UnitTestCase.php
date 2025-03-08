<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Oltrematica\RoleLite\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class UnitTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Oltrematica\\RoleLite\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        //        config()->set('oltrematica-RoleLite.prefix', 'custom-prefix');
        //        config()->set('oltrematica-RoleLite.protected', true);

        /*
        $migration = include __DIR__.'/../database/migrations/create_RoleLite_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders($app)
    {
        return [
            //            ServiceProvider::class,
        ];
    }
}
