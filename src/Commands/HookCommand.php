<?php

namespace CodePulse\Commands;

use Illuminate\Console\Command;

class HookCommand extends Command
{
    protected $signature = 'codepulse:hook
                            {--install : Install the pre-commit Git hook}
                            {--remove : Remove the pre-commit Git hook}';

    protected $description = 'Manage CodePulse Git pre-commit hooks for automated code quality gates';

    public function handle(): int
    {
        $gitPath = base_path('.git');

        if (! is_dir($gitPath)) {
            $this->error('No .git directory found. Please ensure this is a Git repository.');
            return self::FAILURE;
        }

        $hooksDir = $gitPath . '/hooks';
        if (! is_dir($hooksDir)) {
            mkdir($hooksDir, 0755, true);
        }

        $hookPath = $hooksDir . '/pre-commit';

        if ($this->option('remove')) {
            return $this->removeHook($hookPath);
        }

        return $this->installHook($hookPath);
    }

    protected function installHook(string $hookPath): int
    {
        $hookScript = <<<'BASH'
#!/bin/sh
# CodePulse AI Pre-Commit Quality & Security Gate
echo "🔍 [CodePulse] Running pre-commit security and quality audit..."

php artisan codepulse:check --staged --fail-on=critical

STATUS=$?
if [ $STATUS -ne 0 ]; then
    echo "❌ [CodePulse] Commit aborted due to critical security or performance issues."
    echo "💡 Fix the reported issues above or commit with '--no-verify' if urgent."
    exit 1
fi

BASH;

        if (file_exists($hookPath)) {
            $current = file_get_contents($hookPath);
            if (str_contains($current, 'codepulse:check')) {
                $this->info('CodePulse pre-commit hook is already installed.');
                return self::SUCCESS;
            }

            // Append to existing pre-commit hook
            file_put_contents($hookPath, "\n" . $hookScript, FILE_APPEND);
        } else {
            file_put_contents($hookPath, $hookScript);
        }

        @chmod($hookPath, 0755);

        $this->info('Pre-commit hook installed successfully!');
        $this->line('  <fg=gray>Location:</> ' . $hookPath);
        $this->line('  <fg=green>CodePulse will now automatically scan staged changes before each commit.</>');

        return self::SUCCESS;
    }

    protected function removeHook(string $hookPath): int
    {
        if (! file_exists($hookPath)) {
            $this->info('No pre-commit hook found.');
            return self::SUCCESS;
        }

        $content = file_get_contents($hookPath);

        if (! str_contains($content, 'codepulse:check')) {
            $this->info('CodePulse hook is not present in the pre-commit script.');
            return self::SUCCESS;
        }

        // Clean out CodePulse block or remove file if only CodePulse
        $newContent = preg_replace('/# CodePulse AI Pre-Commit Quality & Security Gate.*?\nfi\n/s', '', $content);
        if ($newContent === null || $newContent === $content) {
            $newContent = preg_replace('/.*?codepulse:check.*?\n/s', '', $content);
        }

        $trimmed = trim(str_replace('#!/bin/sh', '', (string) $newContent));

        if (empty($trimmed)) {
            @unlink($hookPath);
        } else {
            file_put_contents($hookPath, trim($newContent) . "\n");
        }

        $this->info('CodePulse pre-commit hook removed successfully.');
        return self::SUCCESS;
    }
}
