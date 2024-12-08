<?php

namespace CashDash\Zaar\Tests;

use CashDash\Zaar\ZaarServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../workbench/database/migrations');

        $migration = include __DIR__.'/../database/migrations/add_shopify_user_id_to_users_table.php.stub';
        $migration->up();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CashDash\\Zaar\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ZaarServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        config()->set('app.key', 'base64:'.base64_encode(random_bytes(
            $app['config']['app.cipher'] == 'AES-128-CBC' ? 16 : 32
        )));

        $migration = include __DIR__.'/../database/migrations/create_shopify_sessions_table.php.stub';
        $migration->up();
    }
}
