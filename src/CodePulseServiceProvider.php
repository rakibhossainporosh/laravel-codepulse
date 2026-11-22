<?php

namespace CodePulse;

use CodePulse\Commands\CheckCommand;
use CodePulse\Commands\HookCommand;
use CodePulse\Commands\InstallCommand;
use CodePulse\Services\GeminiClient;
use CodePulse\Services\KeyVaultService;
use CodePulse\Services\PromptBuilder;
use Illuminate\Support\ServiceProvider;

class CodePulseServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/codepulse.php', 'codepulse');

        $this->app->singleton(PromptBuilder::class, fn () => new PromptBuilder());
        $this->app->singleton(KeyVaultService::class, fn () => new KeyVaultService());
        $this->app->singleton(GeminiClient::class, fn ($app) => new GeminiClient($app->make(PromptBuilder::class)));

        $this->app->singleton('codepulse', function ($app) {
            return new CodePulse(
                $app->make(KeyVaultService::class),
                $app->make(GeminiClient::class)
            );
        });
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerViews();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                CheckCommand::class,
                HookCommand::class,
            ]);
        }
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/codepulse.php' => config_path('codepulse.php'),
            ], 'codepulse-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_codepulse_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_codepulse_tables.php'),
            ], 'codepulse-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/codepulse'),
            ], 'codepulse-views');
        }
    }

    protected function registerRoutes(): void
    {
        if (config('codepulse.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'codepulse');
    }
}
