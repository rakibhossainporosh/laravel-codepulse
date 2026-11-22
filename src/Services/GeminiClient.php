<?php

namespace CodePulse\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiClient
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(
        protected PromptBuilder $promptBuilder
    ) {}

    /**
     * Call Gemini generateContent API with structured schema and return parsed result.
     * Automatically falls back to fallback model on 429 or 503 before failing.
     *
     * @return array{scores: array, findings: array, tokens_used: int, model_used: string}
     *
     * @throws RuntimeException
     */
    public function generateReview(
        string $rawApiKey,
        string $sourceCode,
        string $language,
        array $focusAreas,
        ?string $model = null,
        ?string $filePath = null
    ): array {
        $primaryModel = $model ?: config('codepulse.default_model', 'gemini-3.8-flash');
        $fallbackModel = config('codepulse.fallback_model', 'gemini-3.5-flash-lite');

        try {
            $result = $this->executeGenerateContent($rawApiKey, $sourceCode, $language, $focusAreas, $primaryModel, $filePath);
            $result['model_used'] = $primaryModel;

            return $result;
        } catch (RuntimeException $e) {
            $status = $e->getCode();

            // Automatic fallback to fallback_model on 429 (quota) or 503 (overloaded)
            if ($primaryModel !== $fallbackModel && in_array($status, [429, 503])) {
                Log::warning("CodePulse: Gemini model [{$primaryModel}] returned HTTP {$status}. Falling back to [{$fallbackModel}].", [
                    'original_model' => $primaryModel,
                    'fallback_model' => $fallbackModel,
                    'error' => $e->getMessage(),
                ]);

                $fallbackResult = $this->executeGenerateContent(
                    $rawApiKey,
                    $sourceCode,
                    $language,
                    $focusAreas,
                    $fallbackModel,
                    $filePath
                );

                $fallbackResult['model_used'] = $fallbackModel;

                return $fallbackResult;
            }

            throw $e;
        }
    }

    /**
     * Execute HTTP POST to Gemini generateContent endpoint.
     *
     * @throws RuntimeException
     */
    protected function executeGenerateContent(
        string $rawApiKey,
        string $sourceCode,
        string $language,
        array $focusAreas,
        string $model,
        ?string $filePath = null
    ): array {
        $endpoint = self::BASE_URL . '/' . $model . ':generateContent';

        $systemInstruction = $this->promptBuilder->buildSystemInstruction($language, $focusAreas);
        $userPrompt = $this->promptBuilder->buildUserPrompt($sourceCode, $language, $focusAreas, $filePath);
        $responseSchema = $this->promptBuilder->getResponseSchema();

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt],
                    ],
                ],
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
                'temperature' => 0.2,
            ],
        ];

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $rawApiKey,
                ])
                ->post($endpoint, $payload);
        } catch (Exception $e) {
            Log::error('CodePulse: Gemini request failed due to connection error', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException("Connection to Google Gemini timed out or failed ({$model}): " . $e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            $status = $response->status();
            $errorMessage = $response->json('error.message')
                ?? $response->json('error')
                ?? ('HTTP ' . $status . ' ' . $response->body());

            Log::warning('CodePulse: Gemini API returned non-success response', [
                'status' => $status,
                'model' => $model,
                'error_message' => $errorMessage,
            ]);

            if (in_array($status, [429, 500, 502, 503, 504])) {
                throw new RuntimeException("Gemini API rate limited or unavailable (Status {$status}): {$errorMessage}", $status);
            }

            throw new RuntimeException("Gemini API error ({$status}): {$errorMessage}", $status);
        }

        $responseData = $response->json();
        $tokensUsed = (int) ($responseData['usageMetadata']['totalTokenCount'] ?? 0);

        $rawContent = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! $rawContent) {
            throw new RuntimeException('Gemini returned an empty candidate response structure.');
        }

        $parsed = json_decode($rawContent, true);

        if (! is_array($parsed) || ! isset($parsed['scores']) || ! isset($parsed['findings'])) {
            throw new RuntimeException('Gemini response could not be parsed into the required schema.');
        }

        return [
            'scores' => [
                'security' => (int) ($parsed['scores']['security'] ?? 70),
                'performance' => (int) ($parsed['scores']['performance'] ?? 70),
                'maintainability' => (int) ($parsed['scores']['maintainability'] ?? 70),
            ],
            'findings' => is_array($parsed['findings']) ? $parsed['findings'] : [],
            'tokens_used' => $tokensUsed,
        ];
    }
}
