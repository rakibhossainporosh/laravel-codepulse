<?php

namespace CodePulse\Tests;

use CodePulse\CodePulseServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            CodePulseServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:6Cu/ozUs8DpKDdoercUcCDZLkKgAyA4VbUghF/U5o5w=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('codepulse.api_key', 'AQ.TestApiKey1234567890');
        $app['config']->set('codepulse.default_model', 'gemini-3.8-flash');
        $app['config']->set('codepulse.fallback_model', 'gemini-3.5-flash-lite');
    }

    protected function defineDatabaseMigrations(): void
    {
        $migration = include __DIR__ . '/../database/migrations/create_codepulse_tables.php.stub';
        $migration->up();
    }
}
