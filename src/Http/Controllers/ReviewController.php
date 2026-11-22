<?php

namespace CodePulse\Http\Controllers;

use CodePulse\Jobs\ProcessReviewJob;
use CodePulse\Models\Review;
use CodePulse\Services\KeyVaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;

class ReviewController
{
    public function store(Request $request, KeyVaultService $keyVaultService): JsonResponse
    {
        $validated = $request->validate([
            'source_code' => ['required', 'string', 'max:65000'],
            'language' => ['nullable', 'string', 'max:50'],
            'focus_areas' => ['nullable', 'array'],
            'model' => ['nullable', 'string', 'max:60'],
            'api_key' => ['nullable', 'string'],
        ]);

        $rawKey = $keyVaultService->resolveApiKey($validated['api_key'] ?? null);

        if (! $rawKey) {
            return response()->json([
                'message' => 'No Google Gemini API key configured. Provide an API key or configure CODEPULSE_GEMINI_KEY in .env.',
            ], 422);
        }

        $review = Review::create([
            'language' => $validated['language'] ?? 'PHP',
            'source_code' => $validated['source_code'],
            'focus_areas' => $validated['focus_areas'] ?? config('codepulse.focus_areas'),
            'model_used' => $validated['model'] ?? config('codepulse.default_model', 'gemini-3.8-flash'),
            'status' => 'pending',
        ]);

        ProcessReviewJob::dispatch($review, $rawKey);

        return response()->json([
            'message' => 'Review queued successfully',
            'review_id' => $review->id,
            'status' => $review->status,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $review = Review::with('findings')->findOrFail($id);

        return response()->json([
            'review' => $review,
        ]);
    }

    public function status(int $id): JsonResponse
    {
        $review = Review::select(['id', 'status', 'overall_score', 'error_message'])->findOrFail($id);

        return response()->json([
            'id' => $review->id,
            'status' => $review->status,
            'overall_score' => $review->overall_score,
            'error_message' => $review->error_message,
        ]);
    }

    public function retry(int $id, KeyVaultService $keyVaultService): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        $rawKey = $keyVaultService->resolveApiKey();
        ProcessReviewJob::dispatch($review, $rawKey);

        return response()->json([
            'message' => 'Review re-queued for processing',
            'review_id' => $review->id,
        ]);
    }
}
