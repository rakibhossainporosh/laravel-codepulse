<?php

namespace CodePulse;

use CodePulse\Models\Review;
use CodePulse\Services\GeminiClient;
use CodePulse\Services\KeyVaultService;

class CodePulse
{
    public function __construct(
        protected KeyVaultService $keyVault,
        protected GeminiClient $geminiClient
    ) {}

    /**
     * Audit raw source code directly via PHP.
     */
    public function review(
        string $sourceCode,
        string $language = 'PHP',
        array $focusAreas = ['security', 'performance', 'bug', 'style'],
        ?string $model = null
    ): array {
        $apiKey = $this->keyVault->resolveApiKey();

        if (! $apiKey) {
            throw new \RuntimeException('Google Gemini API Key is missing. Configure CODEPULSE_GEMINI_KEY in .env.');
        }

        return $this->geminiClient->generateReview(
            $apiKey,
            $sourceCode,
            $language,
            $focusAreas,
            $model
        );
    }

    /**
     * Get review history query builder.
     */
    public function reviews()
    {
        return Review::with('findings')->latest('id');
    }
}
