<?php

namespace CodePulse\Tests;

class HookCommandTest extends TestCase
{
    protected string $fakeGitDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeGitDir = base_path('.git');
        if (! is_dir($this->fakeGitDir)) {
            mkdir($this->fakeGitDir . '/hooks', 0755, true);
        }
    }

    public function test_hook_command_installs_pre_commit_script(): void
    {
        $hookFile = $this->fakeGitDir . '/hooks/pre-commit';
        @unlink($hookFile);

        $this->artisan('codepulse:hook', ['--install' => true])
            ->expectsOutputToContain('Pre-commit hook installed successfully!')
            ->assertSuccessful();

        $this->assertFileExists($hookFile);
        $content = file_get_contents($hookFile);
        $this->assertStringContainsString('codepulse:check --staged --fail-on=critical', $content);

        @unlink($hookFile);
    }

    public function test_hook_command_can_remove_installed_hook(): void
    {
        $hookFile = $this->fakeGitDir . '/hooks/pre-commit';
        file_put_contents($hookFile, "#!/bin/sh\n# CodePulse AI Pre-Commit Quality & Security Gate\nphp artisan codepulse:check\nexit 1\nfi\n");

        $this->artisan('codepulse:hook', ['--remove' => true])
            ->expectsOutputToContain('CodePulse pre-commit hook removed successfully')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($hookFile);
    }
}
