<?php

namespace CodePulse\Tests;

use CodePulse\Services\GeminiClient;
use Illuminate\Support\Facades\Http;

class GeminiClientTest extends TestCase
{
    public function test_gemini_client_calls_api_with_x_goog_api_key_header(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'scores' => [
                                            'security' => 90,
                                            'performance' => 85,
                                            'maintainability' => 80,
                                        ],
                                        'findings' => [],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 200],
            ], 200),
        ]);

        $client = app(GeminiClient::class);
        $result = $client->generateReview('AQ.TestKey123', '<?php echo "test";', 'PHP', ['security']);

        $this->assertEquals(90, $result['scores']['security']);
        $this->assertEquals(200, $result['tokens_used']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('x-goog-api-key') &&
                $request->header('x-goog-api-key')[0] === 'AQ.TestKey123' &&
                ! str_contains($request->url(), 'key=');
        });
    }

    public function test_gemini_client_automatically_falls_back_on_503(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent' => Http::response([
                'error' => ['message' => 'Model overloaded'],
            ], 503),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'scores' => [
                                            'security' => 95,
                                            'performance' => 90,
                                            'maintainability' => 88,
                                        ],
                                        'findings' => [],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 120],
            ], 200),
        ]);

        $client = app(GeminiClient::class);
        $result = $client->generateReview('AQ.TestKey123', '<?php echo "test";', 'PHP', ['security'], 'gemini-3.8-flash');

        $this->assertEquals('gemini-3.5-flash-lite', $result['model_used']);
        $this->assertEquals(95, $result['scores']['security']);
    }
}
