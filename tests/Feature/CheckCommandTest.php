<?php

namespace CodePulse\Tests;

use Illuminate\Support\Facades\Http;

class CheckCommandTest extends TestCase
{
    public function test_check_command_audits_file_and_displays_findings(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'cp_test_') . '.php';
        file_put_contents($tempFile, '<?php $id = $_GET["id"]; DB::select("SELECT * FROM users WHERE id = " . $id);');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'scores' => [
                                            'security' => 45,
                                            'performance' => 80,
                                            'maintainability' => 70,
                                        ],
                                        'findings' => [
                                            [
                                                'category' => 'security',
                                                'severity' => 'critical',
                                                'title' => 'SQL Injection Vulnerability',
                                                'description' => 'Direct concatenation of query parameter into raw SQL query.',
                                                'line_start' => 1,
                                                'suggested_fix' => 'DB::select("SELECT * FROM users WHERE id = ?", [$id]);',
                                            ],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
                'usageMetadata' => ['totalTokenCount' => 300],
            ], 200),
        ]);

        $this->artisan('codepulse:check', ['path' => $tempFile])
            ->expectsOutputToContain('SQL Injection Vulnerability')
            ->assertFailed();

        @unlink($tempFile);
    }
}
