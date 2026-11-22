<?php

namespace CodePulse\Tests;

use CodePulse\Jobs\ProcessReviewJob;
use CodePulse\Models\Review;
use CodePulse\Services\GeminiClient;
use CodePulse\Services\KeyVaultService;
use Illuminate\Support\Facades\Http;

class ProcessReviewJobTest extends TestCase
{
    public function test_process_review_job_calculates_weighted_score_and_saves_findings(): void
    {
        $review = Review::create([
            'language' => 'PHP',
            'source_code' => '<?php echo "Hello World";',
            'status' => 'pending',
            'model_used' => 'gemini-3.8-flash',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'scores' => [
                                            'security' => 80,
                                            'performance' => 90,
                                            'maintainability' => 70,
                                        ],
                                        'findings' => [
                                            [
                                                'category' => 'performance',
                                                'severity' => 'medium',
                                                'title' => 'Slow Echo',
                                                'description' => 'Echo is called directly without buffer.',
                                                'line_start' => 1,
                                                'suggested_fix' => 'print "Hello World";',
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 150],
            ], 200),
        ]);

        $job = new ProcessReviewJob($review, 'AQ.TestKey12345');
        $job->handle(app(KeyVaultService::class), app(GeminiClient::class));

        $review->refresh();

        // 80*0.35 + 90*0.35 + 70*0.30 = 28 + 31.5 + 21 = 80.5 -> 81
        $this->assertEquals('completed', $review->status);
        $this->assertEquals(81, $review->overall_score);
        $this->assertEquals(80, $review->security_score);
        $this->assertEquals(90, $review->performance_score);
        $this->assertEquals(70, $review->maintainability_score);
        $this->assertCount(1, $review->findings);
        $this->assertEquals('Slow Echo', $review->findings->first()->title);
    }
}
