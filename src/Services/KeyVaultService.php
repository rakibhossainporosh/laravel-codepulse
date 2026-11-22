<?php

namespace CodePulse\Services;

use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeyVaultService
{
    private const PING_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Resolve the active API key (from config/env or encrypted storage).
     */
    public function resolveApiKey(?string $overrideKey = null): ?string
    {
        if ($overrideKey && ! empty(trim($overrideKey))) {
            return trim($overrideKey);
        }

        $configKey = config('codepulse.api_key');
        if (! empty($configKey)) {
            return trim($configKey);
        }

        return null;
    }

    /**
     * Test connection to Google Gemini API using the provided key.
     * Supports both new AQ. keys and legacy AIza keys via x-goog-api-key header.
     *
     * @return array{success: boolean, message: string}
     */
    public function testConnection(string $rawKey): array
    {
        $cleanKey = trim($rawKey);

        if (! str_starts_with($cleanKey, 'AQ.') && ! str_starts_with($cleanKey, 'AIza')) {
            return [
                'success' => false,
                'message' => 'Invalid key format. Gemini API key must start with "AQ." or "AIza".',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-goog-api-key' => $cleanKey,
                ])
                ->get(self::PING_URL);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Gemini API connection verified successfully.',
                ];
            }

            $status = $response->status();
            $errorMsg = $response->json('error.message') ?? ('HTTP ' . $status);

            return [
                'success' => false,
                'message' => "Google AI Studio rejected key (Status {$status}): {$errorMsg}",
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection timed out or failed to reach Google AI Studio: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate safe masked hint for display (e.g. AQ.A...RN4w).
     */
    public function maskKey(?string $rawKey): string
    {
        if (! $rawKey || strlen($rawKey) < 8) {
            return '••••••••';
        }

        $start = substr($rawKey, 0, 4);
        $end = substr($rawKey, -4);

        return "{$start}...{$end}";
    }
}
