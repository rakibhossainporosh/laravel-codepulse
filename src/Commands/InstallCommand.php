<?php

namespace CodePulse\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    protected $signature = 'codepulse:install {--force : Overwrite existing files}';

    protected $description = 'Install and configure CodePulse AI in your Laravel application';

    public function handle(): int
    {
        $this->displayBanner();

        $this->info('Publishing CodePulse configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'codepulse-config',
            '--force' => $this->option('force'),
        ]);

        $this->info('Publishing CodePulse migrations...');
        $this->publishMigration();

        if ($this->confirm('Would you like to run the database migrations now?', true)) {
            $this->call('migrate');
        }

        if (is_dir(base_path('.git')) && $this->confirm('Would you like to install the Git pre-commit hook to audit changes before each commit?', false)) {
            $this->call('codepulse:hook', ['--install' => true]);
        }

        $this->checkEnvironmentKey();

        $this->newLine();
        $this->components->info('CodePulse AI successfully installed!');
        $this->line('  <fg=gray>Dashboard:</> <fg=cyan>' . url(config('codepulse.path', 'codepulse')) . '</>');
        $this->line('  <fg=gray>CLI Check:</> <fg=yellow>php artisan codepulse:check --staged</>');
        $this->line('  <fg=gray>Git Hook:</>  <fg=yellow>php artisan codepulse:hook --install</>');
        $this->newLine();

        return self::SUCCESS;
    }

    protected function publishMigration(): void
    {
        $migrationFile = 'create_codepulse_tables.php';
        $existing = glob(database_path("migrations/*_{$migrationFile}"));

        if (! empty($existing) && ! $this->option('force')) {
            $this->line('  <fg=gray>Migration already exists. Skipping.</>');
            return;
        }

        $timestamp = date('Y_m_d_His');
        $targetPath = database_path("migrations/{$timestamp}_{$migrationFile}");
        $stubPath = __DIR__ . '/../../database/migrations/create_codepulse_tables.php.stub';

        copy($stubPath, $targetPath);
        $this->line("  <fg=green>Created migration:</> {$timestamp}_{$migrationFile}");
    }

    protected function checkEnvironmentKey(): void
    {
        $key = config('codepulse.api_key');

        if (! empty($key)) {
            $this->components->twoColumnDetail('Google Gemini API Key', '<fg=green;options=bold>CONFIGURED</>');
            return;
        }

        $this->newLine();
        $this->components->warn('No Google Gemini API Key found in your .env file.');
        $this->line('  To run AI code audits, get a free key from: <fg=cyan>https://aistudio.google.com/app/apikey</>');
        $this->line('  Then add it to your <fg=yellow>.env</> file:');
        $this->line('  <fg=yellow>CODEPULSE_GEMINI_KEY=AQ.your_api_key_here</>');
    }

    protected function displayBanner(): void
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold>   ______          __     ____        __             ___     ____</>');
        $this->line('  <fg=blue;options=bold>  / ____/____  ___/ /__  / __ \__  __/ /_______     /   |   /  _/</>');
        $this->line('  <fg=blue;options=bold> / /   / __ \/ __  / _ \/ /_/ / / / / / ___/ _ \   / /| |   / /  </>');
        $this->line('  <fg=blue;options=bold>/ /___/ /_/ / /_/ /  __/ ____/ /_/ / (__  )  __/  / ___ |_ / /   </>');
        $this->line('  <fg=blue;options=bold>\____/\____/\__,_/\___/_/    \__,_/_/____/\___/  /_/  |_/___/    </>');
        $this->line('  <fg=gray>AI-Powered Code & Security Review Engine for Laravel</>');
        $this->newLine();
    }
}
