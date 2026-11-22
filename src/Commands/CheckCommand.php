<?php

namespace CodePulse\Commands;

use CodePulse\Models\Review;
use CodePulse\Services\GeminiClient;
use CodePulse\Services\KeyVaultService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Terminal;

class CheckCommand extends Command
{
    protected $signature = 'codepulse:check
                            {path? : The file or directory path to audit}
                            {--staged : Audit only Git staged files before commit}
                            {--model= : Override the default Gemini model}
                            {--fail-on=critical : Exit code 1 if issues meet or exceed this severity (critical, high, medium)}';

    protected $description = 'Perform an AI code quality and security audit on files or Git changes';

    public function handle(KeyVaultService $keyVaultService, GeminiClient $geminiClient): int
    {
        $apiKey = $keyVaultService->resolveApiKey();

        if (! $apiKey) {
            $this->components->error('Google Gemini API Key is missing.');
            $this->line('  Please set <fg=yellow>CODEPULSE_GEMINI_KEY</> in your .env or run <fg=cyan>php artisan codepulse:install</>.');
            return self::FAILURE;
        }

        $files = $this->resolveFilesToAudit();

        if (empty($files)) {
            $this->components->info('No applicable files found to audit.');
            return self::SUCCESS;
        }

        $this->components->info(sprintf('Analyzing %d file(s) with Google Gemini AI...', count($files)));
        $this->newLine();

        $hasViolations = false;
        $failOn = strtolower($this->option('fail-on') ?: 'critical');
        $model = $this->option('model') ?: config('codepulse.default_model', 'gemini-3.8-flash');

        foreach ($files as $file) {
            $this->line("  <fg=gray>Auditing:</> <fg=white;options=bold>{$file}</>");

            if (! file_exists($file)) {
                $this->components->warn("File not found: {$file}");
                continue;
            }

            $sourceCode = file_get_contents($file);

            if (empty(trim($sourceCode))) {
                continue;
            }

            $language = $this->detectLanguage($file);
            $focusAreas = config('codepulse.focus_areas', ['security', 'performance', 'bug', 'style']);

            try {
                $result = $geminiClient->generateReview(
                    $apiKey,
                    $sourceCode,
                    $language,
                    $focusAreas,
                    $model,
                    $file
                );

                $scores = $result['scores'];
                $findings = $result['findings'];
                $actualModel = $result['model_used'];

                $sec = $scores['security'];
                $perf = $scores['performance'];
                $maint = $scores['maintainability'];
                $overall = (int) round(($sec * 0.35) + ($perf * 0.35) + ($maint * 0.30));

                // Print Scores
                $this->displayScores($overall, $sec, $perf, $maint, $actualModel);

                // Print Findings
                if (empty($findings)) {
                    $this->line('    <fg=green>✔ No issues found. Clean code!</>');
                } else {
                    $this->displayFindings($findings, $file);

                    foreach ($findings as $f) {
                        $sev = strtolower($f['severity'] ?? 'medium');
                        if ($this->severityMeetsThreshold($sev, $failOn)) {
                            $hasViolations = true;
                        }
                    }
                }

                $this->newLine();
            } catch (\Exception $e) {
                $this->components->error("Failed to audit {$file}: " . $e->getMessage());
                $this->newLine();
            }
        }

        if ($hasViolations) {
            $this->components->error("Audit failed: One or more files contain {$failOn} or higher severity issues.");
            return self::FAILURE;
        }

        $this->components->info('All audited files passed inspection successfully!');
        return self::SUCCESS;
    }

    protected function resolveFilesToAudit(): array
    {
        if ($this->option('staged')) {
            $process = Process::run('git diff --cached --name-only --diff-filter=ACMR');
            if ($process->failed()) {
                $this->components->warn('Git command failed. Checking all files instead.');
                return [];
            }

            $output = trim($process->output());
            if (empty($output)) {
                return [];
            }

            $staged = explode("\n", $output);
            return array_values(array_filter($staged, fn ($f) => $this->isAuditableFile(trim($f))));
        }

        $targetPath = $this->argument('path') ?: 'app';

        if (is_file($targetPath)) {
            return [$targetPath];
        }

        if (is_dir($targetPath)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($targetPath));
            $files = [];

            foreach ($iterator as $item) {
                if ($item->isFile() && $this->isAuditableFile($item->getPathname())) {
                    $files[] = $item->getPathname();
                }
            }

            return $files;
        }

        return [];
    }

    protected function isAuditableFile(string $filePath): bool
    {
        $ignored = config('codepulse.ignore_paths', ['vendor', 'node_modules', 'storage', 'tests']);
        foreach ($ignored as $ignore) {
            if (str_contains($filePath, $ignore)) {
                return false;
            }
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), ['php', 'js', 'ts', 'jsx', 'tsx', 'py']);
    }

    protected function detectLanguage(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'php' => 'PHP',
            'js', 'jsx' => 'JavaScript',
            'ts', 'tsx' => 'TypeScript',
            'py' => 'Python',
            default => 'PHP',
        };
    }

    protected function displayScores(int $overall, int $sec, int $perf, int $maint, string $model): void
    {
        $scoreColor = $overall >= 80 ? 'green' : ($overall >= 60 ? 'yellow' : 'red');

        $this->line(sprintf(
            '    <fg=gray>Model:</> %s | <fg=gray>Overall:</> <fg=%s;options=bold>%d/100</> (Sec: %d, Perf: %d, Maint: %d)',
            $model,
            $scoreColor,
            $overall,
            $sec,
            $perf,
            $maint
        ));
    }

    protected function displayFindings(array $findings, string $file): void
    {
        foreach ($findings as $idx => $f) {
            $severity = strtolower($f['severity'] ?? 'medium');
            $sevColor = match ($severity) {
                'critical' => 'red;options=bold',
                'high' => 'red',
                'medium' => 'yellow',
                'low' => 'blue',
                default => 'gray',
            };

            $lineInfo = isset($f['line_start']) ? " (L{$f['line_start']})" : '';
            $this->line(sprintf(
                '    <fg=%s>[%s]</> <fg=white;options=bold>%s</>%s',
                $sevColor,
                strtoupper($severity),
                $f['title'] ?? 'Issue',
                $lineInfo
            ));

            if (! empty($f['description'])) {
                $this->line("      <fg=gray>" . trim($f['description']) . "</>");
            }

            if (! empty($f['suggested_fix'])) {
                $this->line("      <fg=cyan>Fix:</> " . trim($f['suggested_fix']));
            }
        }
    }

    protected function severityMeetsThreshold(string $severity, string $threshold): bool
    {
        $levels = ['info' => 1, 'low' => 2, 'medium' => 3, 'high' => 4, 'critical' => 5];
        return ($levels[$severity] ?? 0) >= ($levels[$threshold] ?? 5);
    }
}
