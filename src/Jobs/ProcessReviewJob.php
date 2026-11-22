<?php

namespace CodePulse\Jobs;

use CodePulse\Models\Review;
use CodePulse\Services\GeminiClient;
use CodePulse\Services\KeyVaultService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(
        public Review $review,
        public ?string $apiKey = null
    ) {}

    public function handle(KeyVaultService $keyVaultService, GeminiClient $geminiClient): void
    {
        $this->review->refresh();
        $this->review->update(['status' => 'processing']);

        $rawApiKey = $keyVaultService->resolveApiKey($this->apiKey);

        if (! $rawApiKey) {
            throw new \RuntimeException('No active Gemini API key found. Please define CODEPULSE_GEMINI_KEY in .env or configure it in Settings.');
        }

        $model = $this->review->model_used ?: config('codepulse.default_model', 'gemini-3.8-flash');
        $focusAreas = $this->review->focus_areas ?: config('codepulse.focus_areas', ['security', 'performance', 'bug', 'style']);

        $result = $geminiClient->generateReview(
            $rawApiKey,
            $this->review->source_code,
            $this->review->language,
            $focusAreas,
            $model,
            $this->review->file_path
        );

        $scores = $result['scores'];
        $findings = $result['findings'];
        $tokensUsed = $result['tokens_used'];
        $actualModel = $result['model_used'] ?? $model;

        // Weighted Overall Score Formula
        $perfScore = $scores['performance'];
        $secScore = $scores['security'];
        $maintScore = $scores['maintainability'];
        $overallScore = (int) round(($secScore * 0.35) + ($perfScore * 0.35) + ($maintScore * 0.30));

        $connection = config('codepulse.database_connection');

        DB::connection($connection)->transaction(function () use ($overallScore, $perfScore, $secScore, $maintScore, $tokensUsed, $findings, $actualModel) {
            $this->review->findings()->delete();

            foreach ($findings as $item) {
                $category = strtolower($item['category'] ?? 'bug');
                $severity = strtolower($item['severity'] ?? 'medium');

                $this->review->findings()->create([
                    'category' => in_array($category, ['security', 'performance', 'bug', 'style']) ? $category : 'bug',
                    'severity' => in_array($severity, ['critical', 'high', 'medium', 'low', 'info']) ? $severity : 'medium',
                    'title' => substr($item['title'] ?? 'Issue detected', 0, 255),
                    'description' => $item['description'] ?? '',
                    'line_start' => isset($item['line_start']) ? (int) $item['line_start'] : null,
                    'line_end' => isset($item['line_end']) ? (int) $item['line_end'] : null,
                    'suggested_fix' => $item['suggested_fix'] ?? null,
                ]);
            }

            $this->review->update([
                'status' => 'completed',
                'model_used' => $actualModel,
                'overall_score' => $overallScore,
                'performance_score' => $perfScore,
                'security_score' => $secScore,
                'maintainability_score' => $maintScore,
                'tokens_used' => $tokensUsed,
                'error_message' => null,
            ]);
        });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('CodePulse: ProcessReviewJob permanently failed', [
            'review_id' => $this->review->id,
            'exception' => $exception?->getMessage(),
        ]);

        $safeMessage = 'AI Code Review failed: ' . ($exception?->getMessage() ?? 'Unknown error occurred.');

        // Sanitize any key leakage
        $safeMessage = preg_replace('/(AQ\.[A-Za-z0-9_-]+|AIza[0-9A-Za-z-_]{35})/', '[REDACTED_KEY]', $safeMessage);

        $this->review->update([
            'status' => 'failed',
            'error_message' => $safeMessage,
        ]);
    }
}
