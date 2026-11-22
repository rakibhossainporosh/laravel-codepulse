<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodePulse AI — Code & Security Review Engine</title>

    <!-- Tailwind CSS CDN for instant zero-setup drop-in execution -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

    <style>
        .bg-subtle-grid {
            background-size: 24px 24px;
            background-image: radial-gradient(rgba(148, 163, 184, 0.15) 1px, transparent 1px);
        }
        .dark .bg-subtle-grid {
            background-image: radial-gradient(rgba(148, 163, 184, 0.07) 1px, transparent 1px);
        }
    </style>
</head>
<body class="h-full bg-slate-50/80 text-slate-900 dark:bg-[#0b0f19] dark:text-slate-100 font-sans antialiased bg-subtle-grid transition-colors duration-200">

<div id="codepulse-app" class="min-h-screen flex flex-col">
    <!-- Top Navigation Header -->
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-slate-800/80 dark:bg-[#0e1320]/90 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-md shadow-indigo-500/25">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-base font-black tracking-tight text-slate-900 dark:text-white">CodePulse</span>
                        <span class="rounded bg-gradient-to-r from-indigo-500 to-violet-500 px-1.5 py-0.2 text-[10px] font-extrabold text-white">AI</span>
                    </div>
                    <span class="block text-[10px] font-medium uppercase tracking-wider text-slate-400">Laravel Package Engine</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- BYOK Status Pill -->
                <div class="flex items-center gap-2 rounded-xl border border-slate-200/80 bg-slate-100/60 px-3 py-1.5 text-xs dark:border-slate-800 dark:bg-slate-900/60">
                    <span class="relative flex h-2 w-2">
                        @if($apiKeyStatus['has_key'])
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        @endif
                    </span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">
                        {{ $apiKeyStatus['has_key'] ? 'Gemini Active' : 'Key Missing' }}
                    </span>
                    @if($apiKeyStatus['key_hint'])
                        <span class="font-mono text-[10px] text-slate-400">({{ $apiKeyStatus['key_hint'] }})</span>
                    @endif
                </div>

                <!-- Theme Toggle Button -->
                <button type="button" onclick="toggleTheme()" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    <svg id="theme-icon-sun" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg id="theme-icon-moon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <!-- Hero Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    Code Quality & Security Dashboard
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    AI-powered AST static analysis, vulnerability scanner, and algorithmic efficiency scoring.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="document.getElementById('audit-section').scrollIntoView({behavior: 'smooth'})" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-violet-600 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-500/25 hover:from-indigo-500 hover:to-violet-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Run New Audit</span>
                </button>
            </div>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <!-- Total Reviews -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-xs dark:border-slate-800/80 dark:bg-[#0e1320]/90">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Audits</div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $kpis['total_reviews'] }}</span>
                    <span class="text-xs text-slate-400 font-semibold">inspections</span>
                </div>
            </div>

            <!-- Average Score -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-xs dark:border-slate-800/80 dark:bg-[#0e1320]/90">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Average Health Score</div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $kpis['avg_score'] }}</span>
                    <span class="text-xs text-slate-400 font-semibold">/ 100</span>
                    <span class="ml-auto inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $kpis['avg_score'] >= 80 ? 'bg-emerald-500/15 text-emerald-600' : 'bg-amber-500/15 text-amber-600' }}">
                        {{ $kpis['avg_score'] >= 80 ? 'Good' : 'Needs Fix' }}
                    </span>
                </div>
            </div>

            <!-- Critical Vulnerabilities -->
            <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-xs dark:border-slate-800/80 dark:bg-[#0e1320]/90">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Critical Issues</div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $kpis['critical_issues'] }}</span>
                    <span class="text-xs text-slate-400 font-semibold">vulnerabilities</span>
                    @if($kpis['critical_issues'] === 0)
                        <span class="ml-auto rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-600">All Clear</span>
                    @else
                        <span class="ml-auto rounded-full bg-rose-500/10 px-2 py-0.5 text-[10px] font-bold text-rose-600">Action Required</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Interactive Audit Editor & Workspace -->
        <div id="audit-section" class="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-xs dark:border-slate-800/80 dark:bg-[#0e1320]/90 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">New Review Session</h2>
                    <p class="text-xs text-slate-400">Paste your code snippet or controller logic to trigger an AI review</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="loadSample('php')" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        Load Sample PHP
                    </button>
                </div>
            </div>

            <textarea id="code-input" rows="8" placeholder="<?php echo "<?php\n\n// Paste your PHP / JavaScript code here for instant AI audit...\n"; ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 p-4 font-mono text-xs text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"></textarea>

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-3">
                    <select id="model-select" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                        <option value="gemini-3.8-flash">gemini-3.8-flash (Recommended)</option>
                        <option value="gemini-3.5-flash-lite">gemini-3.5-flash-lite (Fast)</option>
                        <option value="gemini-4-carbon">gemini-4-carbon (Deep Reasoning)</option>
                    </select>
                </div>

                <button id="submit-btn" type="button" onclick="submitReview()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500">
                    <span>Analyze Code</span>
                </button>
            </div>

            <!-- Dynamic Result Area -->
            <div id="result-box" class="hidden mt-4 p-4 rounded-xl border border-indigo-200 bg-indigo-50/50 dark:border-indigo-900/50 dark:bg-indigo-950/20 text-xs">
                <div id="result-content" class="font-mono"></div>
            </div>
        </div>

        <!-- Recent Reviews Table -->
        <div class="rounded-2xl border border-slate-200/80 bg-white/90 overflow-hidden shadow-xs dark:border-slate-800/80 dark:bg-[#0e1320]/90">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80">
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Recent Audit History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 dark:bg-slate-950/50">
                        <tr>
                            <th class="px-6 py-3">Audit</th>
                            <th class="px-6 py-3">Language</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Overall Score</th>
                            <th class="px-6 py-3">Issues</th>
                            <th class="px-6 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @forelse($recentReviews as $item)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">Audit #{{ $item->id }}</td>
                                <td class="px-6 py-4 font-mono">{{ $item->language }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600' : ($item->status === 'failed' ? 'bg-rose-500/10 text-rose-600' : 'bg-amber-500/10 text-amber-600') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    {{ $item->overall_score ? $item->overall_score . '/100' : '—' }}
                                </td>
                                <td class="px-6 py-4">{{ $item->findings->count() }} issues</td>
                                <td class="px-6 py-4 text-slate-400">{{ $item->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    No audits submitted yet. Paste code above or run <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">php artisan codepulse:check</code>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('codepulse_theme', isDark ? 'dark' : 'light');
        document.getElementById('theme-icon-sun').classList.toggle('hidden', !isDark);
        document.getElementById('theme-icon-moon').classList.toggle('hidden', isDark);
    }

    if (localStorage.getItem('codepulse_theme') === 'dark' || (!('codepulse_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        document.getElementById('theme-icon-sun')?.classList.remove('hidden');
        document.getElementById('theme-icon-moon')?.classList.add('hidden');
    }

    function loadSample(lang) {
        document.getElementById('code-input').value = `<?php
namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\DB;

class OrderController extends Controller
{
    public function search(Request $request)
    {
        // Issue: Direct SQL Injection
        $q = $request->input('query');
        return DB::select("SELECT * FROM orders WHERE name LIKE '%" . $q . "%'");
    }
}`;
    }

    async function submitReview() {
        const code = document.getElementById('code-input').value.trim();
        if (!code) {
            alert('Please paste or write some code first.');
            return;
        }

        const model = document.getElementById('model-select').value;
        const btn = document.getElementById('submit-btn');
        const resultBox = document.getElementById('result-box');
        const resultContent = document.getElementById('result-content');

        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">↻</span> Auditing with Gemini...';
        resultBox.classList.remove('hidden');
        resultContent.innerHTML = 'Connecting to Google Gemini API...';

        try {
            const res = await fetch('{{ route("codepulse.reviews.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    source_code: code,
                    language: 'PHP',
                    model: model,
                })
            });

            const data = await res.json();
            if (!res.ok) {
                resultContent.innerHTML = `<span class="text-rose-500">Error: ${data.message}</span>`;
            } else {
                resultContent.innerHTML = `<span class="text-emerald-500">Audit session #${data.review_id} queued! Reloading page to show findings...</span>`;
                setTimeout(() => window.location.reload(), 2000);
            }
        } catch (e) {
            resultContent.innerHTML = `<span class="text-rose-500">Network error: ${e.message}</span>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Analyze Code';
        }
    }
</script>
</body>
</html>
