<?php

namespace CodePulse\Http\Controllers;

use CodePulse\Models\Finding;
use CodePulse\Models\Review;
use CodePulse\Services\KeyVaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request, KeyVaultService $keyVaultService): View|JsonResponse
    {
        $rawKey = $keyVaultService->resolveApiKey();

        $totalReviews = Review::count();
        $avgScore = (int) round(Review::whereNotNull('overall_score')->avg('overall_score') ?? 0);
        $criticalIssues = Finding::where('severity', 'critical')->count();

        $recentReviews = Review::with('findings')
            ->latest('id')
            ->limit(10)
            ->get();

        $data = [
            'kpis' => [
                'total_reviews' => $totalReviews,
                'avg_score' => $avgScore,
                'critical_issues' => $criticalIssues,
            ],
            'recentReviews' => $recentReviews,
            'apiKeyStatus' => [
                'has_key' => ! empty($rawKey),
                'key_hint' => $rawKey ? $keyVaultService->maskKey($rawKey) : null,
                'default_model' => config('codepulse.default_model', 'gemini-3.8-flash'),
            ],
        ];

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('codepulse::app', $data);
    }
}
