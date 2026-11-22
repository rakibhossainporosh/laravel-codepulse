<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CodePulse AI — Code Quality & Security Engine for Laravel</title>

    <!-- Tailwind CSS CDN for instant zero-dependency drop-in execution -->
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
        .line-highlight {
            background-color: rgba(244, 63, 94, 0.15);
            border-left: 3px solid #f43f5e;
        }
    </style>
</head>
<body class="h-full bg-slate-50/80 text-slate-900 dark:bg-[#0b0f19] dark:text-slate-100 font-sans antialiased bg-subtle-grid transition-colors duration-200">

<div id="codepulse-app" class="min-h-screen flex flex-col">
    <!-- Top Navigation Header -->
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-slate-800/80 dark:bg-[#0e1320]/90 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 text-white shadow-md shadow-indigo-500/25">
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">New Review Session</h2>
                    <p class="text-xs text-slate-400">Paste code or drop a source file to run an instant security & performance audit</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-semibold">Load Sample:</span>
                    <button type="button" onclick="loadSample('php')" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        PHP (SQLi / N+1)
                    </button>
                    <button type="button" onclick="loadSample('js')" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        JavaScript
                    </button>
                </div>
            </div>

            <div id="dropzone" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)" class="relative rounded-xl border border-slate-200 dark:border-slate-800 transition-colors">
                <textarea id="code-input" oninput="updateCharCount()" rows="10" placeholder="// Paste your PHP, JavaScript, or Python code here or drag-and-drop a file..." class="w-full rounded-xl bg-slate-50 p-4 font-mono text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 dark:bg-slate-950 dark:text-slate-100 border-0 resize-y"></textarea>
                <div id="drop-overlay" class="hidden absolute inset-0 bg-indigo-500/10 backdrop-blur-xs border-2 border-dashed border-indigo-500 rounded-xl flex items-center justify-center font-bold text-indigo-600 text-sm">
                    Drop file to load code into editor
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                <div class="flex flex-wrap items-center gap-3">
                    <select id="language-select" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                        <option value="PHP">PHP</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="TypeScript">TypeScript</option>
                        <option value="Python">Python</option>
                        <option value="Go">Go</option>
                        <option value="Rust">Rust</option>
                        <option value="SQL">SQL</option>
                    </select>

                    <select id="model-select" class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                        <option value="gemini-3.8-flash">gemini-3.8-flash (Recommended)</option>
                        <option value="gemini-3.5-flash-lite">gemini-3.5-flash-lite (Fast)</option>
                        <option value="gemini-4-carbon">gemini-4-carbon (Deep Reasoning)</option>
                    </select>

                    <span id="char-count" class="font-mono text-[11px] text-slate-400">0 / 65,000 chars</span>
                </div>

                <button id="submit-btn" type="button" onclick="submitReview()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <span>Analyze Code</span>
                </button>
            </div>

            <!-- Dynamic Result Area -->
            <div id="result-box" class="hidden mt-4 p-4 rounded-xl border border-indigo-200 bg-indigo-50/50 dark:border-indigo-900/50 dark:bg-indigo-950/20 text-xs">
                <div id="result-content" class="font-mono flex items-center gap-2"></div>
            </div>
        </div>

        <!-- Recent Reviews Table -->
        <div class="rounded-2xl border border-slate-200/80 bg-white/90 overflow-hidden shadow-xs dark:border-slate-800/80 dark:bg-[#0e1320]/90">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Recent Audit History</h3>
                    <p class="text-[11px] text-slate-400">Click any row or "Inspect" to view detailed finding cards and line-by-line fixes</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 dark:bg-slate-950/50">
                        <tr>
                            <th class="px-6 py-3">Audit</th>
                            <th class="px-6 py-3">Language</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Overall Score</th>
                            <th class="px-6 py-3">Findings</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans">
                        @forelse($recentReviews as $item)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 cursor-pointer" onclick="inspectAudit({{ $item->id }})">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    Audit #{{ $item->id }}
                                    @if($item->file_path)
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $item->file_path }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-mono">{{ $item->language }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600' : ($item->status === 'failed' ? 'bg-rose-500/10 text-rose-600' : 'bg-indigo-500/10 text-indigo-600 animate-pulse') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    @if($item->overall_score !== null)
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs {{ $item->overall_score >= 80 ? 'bg-emerald-500/10 text-emerald-600' : ($item->overall_score >= 60 ? 'bg-amber-500/10 text-amber-600' : 'bg-rose-500/10 text-rose-600') }}">
                                            {{ $item->overall_score }} / 100
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $item->findings->count() }} issues</td>
                                <td class="px-6 py-4 text-slate-400">{{ $item->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" onclick="event.stopPropagation(); inspectAudit({{ $item->id }})" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        Inspect →
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                    No audits submitted yet. Paste code above or run <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">php artisan codepulse:check</code>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Review Detail & Inspector Slide-over Modal -->
    <div id="inspector-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6">
        <div class="relative w-full max-w-6xl max-h-[92vh] flex flex-col rounded-2xl bg-white dark:bg-[#0e1320] border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 px-6 py-4 bg-slate-50/50 dark:bg-[#121829]/50">
                <div class="flex items-center gap-3">
                    <h2 id="modal-title" class="text-base font-black text-slate-900 dark:text-white">Audit Details</h2>
                    <span id="modal-lang" class="rounded-md bg-slate-100 dark:bg-slate-800 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-600 dark:text-slate-300"></span>
                    <span id="modal-model" class="rounded-md bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 font-mono text-[10px] font-bold text-indigo-600 dark:text-indigo-400"></span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="copyMarkdownReport()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2.5 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50">
                        <span id="copy-report-text">Copy .md Report</span>
                    </button>
                    <button type="button" onclick="closeInspector()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- 4 Scores Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/40">
                        <div class="text-[10px] font-bold uppercase text-slate-400">Overall Quality</div>
                        <div id="score-overall" class="text-2xl font-black mt-1"></div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/40">
                        <div class="text-[10px] font-bold uppercase text-slate-400">Security</div>
                        <div id="score-security" class="text-2xl font-black mt-1 text-rose-500"></div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/40">
                        <div class="text-[10px] font-bold uppercase text-slate-400">Performance</div>
                        <div id="score-performance" class="text-2xl font-black mt-1 text-amber-500"></div>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-slate-50/50 dark:bg-slate-900/40">
                        <div class="text-[10px] font-bold uppercase text-slate-400">Maintainability</div>
                        <div id="score-maintainability" class="text-2xl font-black mt-1 text-blue-500"></div>
                    </div>
                </div>

                <!-- Main Split: Code Viewer (Left) & Findings (Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left: Code Viewer -->
                    <div class="lg:col-span-6 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                            <span>Audited Source Code</span>
                            <span class="font-mono text-[10px] text-slate-400" id="code-line-count"></span>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-950 p-4 overflow-x-auto max-h-[500px] overflow-y-auto font-mono text-xs text-slate-200">
                            <pre id="modal-code" class="leading-relaxed"></pre>
                        </div>
                    </div>

                    <!-- Right: Findings List -->
                    <div class="lg:col-span-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500" id="findings-count-badge">Findings</span>
                            <div class="flex gap-1" id="category-tabs">
                                <!-- Populated dynamically -->
                            </div>
                        </div>

                        <div id="modal-findings" class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentReview = null;

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

    function updateCharCount() {
        const len = document.getElementById('code-input').value.length;
        document.getElementById('char-count').innerText = `${len.toLocaleString()} / 65,000 chars`;
    }

    function handleDragOver(e) {
        e.preventDefault();
        document.getElementById('drop-overlay').classList.remove('hidden');
    }

    function handleDragLeave(e) {
        document.getElementById('drop-overlay').classList.add('hidden');
    }

    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('drop-overlay').classList.add('hidden');
        const file = e.dataTransfer.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            document.getElementById('code-input').value = event.target.result;
            updateCharCount();

            // Detect language from extension
            const ext = file.name.split('.').pop().toLowerCase();
            const langMap = { php: 'PHP', js: 'JavaScript', ts: 'TypeScript', py: 'Python', go: 'Go', rs: 'Rust', sql: 'SQL' };
            if (langMap[ext]) {
                document.getElementById('language-select').value = langMap[ext];
            }
        };
        reader.readAsText(file);
    }

    function loadSample(lang) {
        if (lang === 'php') {
            document.getElementById('code-input').value = "<" + "?php\n\n" +
                "namespace App\\Http\\Controllers;\n\n" +
                "use Illuminate\\Http\\Request;\n" +
                "use Illuminate\\Support\\Facades\\DB;\n\n" +
                "class OrderController extends Controller\n" +
                "{\n" +
                "    public function search(Request $request)\n" +
                "    {\n" +
                "        // Issue: Direct SQL Injection Vulnerability\n" +
                "        $q = $request->input('query');\n" +
                "        $orders = DB::select(\"SELECT * FROM orders WHERE name LIKE '%\" . $q . \"%'\");\n\n" +
                "        // Issue: N+1 query loop\n" +
                "        $results = [];\n" +
                "        foreach ($orders as $order) {\n" +
                "            $items = DB::select(\"SELECT * FROM order_items WHERE order_id = \" . $order->id);\n" +
                "            $results[] = ['order' => $order, 'items' => $items];\n" +
                "        }\n\n" +
                "        return response()->json($results);\n" +
                "    }\n" +
                "}";
            document.getElementById('language-select').value = 'PHP';
        } else {
            document.getElementById('code-input').value = `// Express Node.js sample
const express = require('express');
const router = express.Router();

router.get('/users', async (req, res) => {
    const { role } = req.query;

    // Issue: ReDoS vulnerability
    const pattern = new RegExp(role);

    // Issue: Unbounded memory leak
    const cache = [];
    for (let i = 0; i < 100000; i++) {
        cache.push({ id: i, data: Buffer.alloc(512) });
    }

    res.json({ users: cache.length });
});`;
            document.getElementById('language-select').value = 'JavaScript';
        }
        updateCharCount();
    }

    async function submitReview() {
        const code = document.getElementById('code-input').value.trim();
        if (!code) {
            alert('Please paste or write some code first.');
            return;
        }

        const model = document.getElementById('model-select').value;
        const lang = document.getElementById('language-select').value;
        const btn = document.getElementById('submit-btn');
        const resultBox = document.getElementById('result-box');
        const resultContent = document.getElementById('result-content');

        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">↻</span> Auditing with Gemini...';
        resultBox.classList.remove('hidden');
        resultContent.innerHTML = '<span class="text-indigo-600 font-bold">Dispatching to Google Gemini AI Engine...</span>';

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
                    language: lang,
                    model: model,
                })
            });

            const data = await res.json();
            if (!res.ok) {
                resultContent.innerHTML = `<span class="text-rose-500 font-bold">Error: ${data.message}</span>`;
                btn.disabled = false;
                btn.innerHTML = 'Analyze Code';
            } else {
                resultContent.innerHTML = `<span class="text-emerald-500 font-bold">Audit #${data.review_id} queued! Polling for completion...</span>`;
                pollReviewStatus(data.review_id);
            }
        } catch (e) {
            resultContent.innerHTML = `<span class="text-rose-500">Network error: ${e.message}</span>`;
            btn.disabled = false;
            btn.innerHTML = 'Analyze Code';
        }
    }

    async function pollReviewStatus(reviewId) {
        const resultContent = document.getElementById('result-content');
        const interval = setInterval(async () => {
            try {
                const res = await fetch(`{{ url(config('codepulse.path', 'codepulse')) }}/reviews/${reviewId}/status`);
                if (res.ok) {
                    const statusData = await res.json();
                    if (statusData.status === 'completed') {
                        clearInterval(interval);
                        resultContent.innerHTML = `<span class="text-emerald-500 font-bold">Audit completed with Score: ${statusData.overall_score}/100! Opening results...</span>`;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else if (statusData.status === 'failed') {
                        clearInterval(interval);
                        resultContent.innerHTML = `<span class="text-rose-500 font-bold">Audit failed: ${statusData.error_message || 'Unknown error'}</span>`;
                        document.getElementById('submit-btn').disabled = false;
                        document.getElementById('submit-btn').innerHTML = 'Analyze Code';
                    }
                }
            } catch (err) {}
        }, 2000);
    }

    async function inspectAudit(id) {
        try {
            const res = await fetch(`{{ url(config('codepulse.path', 'codepulse')) }}/reviews/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.review) {
                currentReview = data.review;
                renderInspector(data.review);
                document.getElementById('inspector-modal').classList.remove('hidden');
            }
        } catch (e) {
            alert('Failed to load audit details: ' + e.message);
        }
    }

    function closeInspector() {
        document.getElementById('inspector-modal').classList.add('hidden');
    }

    function renderInspector(review) {
        document.getElementById('modal-title').innerText = `Audit #${review.id}`;
        document.getElementById('modal-lang').innerText = review.language;
        document.getElementById('modal-model').innerText = review.model_used || 'gemini-3.8-flash';

        // Scores
        document.getElementById('score-overall').innerText = review.overall_score !== null ? `${review.overall_score}/100` : '—';
        document.getElementById('score-security').innerText = review.security_score !== null ? `${review.security_score}/100` : '—';
        document.getElementById('score-performance').innerText = review.performance_score !== null ? `${review.performance_score}/100` : '—';
        document.getElementById('score-maintainability').innerText = review.maintainability_score !== null ? `${review.maintainability_score}/100` : '—';

        // Code Viewer with line numbers
        const lines = (review.source_code || '').split('\n');
        document.getElementById('code-line-count').innerText = `${lines.length} lines`;

        let codeHtml = '';
        lines.forEach((line, index) => {
            const lineNum = index + 1;
            const safeLine = line.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            codeHtml += `<div id="code-line-${lineNum}" class="flex"><span class="w-8 shrink-0 text-slate-500 select-none text-right pr-3">${lineNum}</span><span class="flex-1">${safeLine || ' '}</span></div>`;
        });
        document.getElementById('modal-code').innerHTML = codeHtml;

        // Findings
        renderFindingsList(review.findings);
    }

    function renderFindingsList(findings) {
        document.getElementById('findings-count-badge').innerText = `${findings.length} Finding(s)`;
        const container = document.getElementById('modal-findings');

        if (!findings || findings.length === 0) {
            container.innerHTML = `<div class="p-6 text-center text-slate-400 font-bold">✨ No issues detected. Production-ready clean code!</div>`;
            return;
        }

        let html = '';
        findings.forEach((f, idx) => {
            const sevColor = {
                critical: 'bg-rose-500/15 text-rose-600 border-rose-500/30',
                high: 'bg-orange-500/15 text-orange-600 border-orange-500/30',
                medium: 'bg-amber-500/15 text-amber-600 border-amber-500/30',
                low: 'bg-blue-500/15 text-blue-600 border-blue-500/30',
            }[f.severity.toLowerCase()] || 'bg-slate-500/15 text-slate-400 border-slate-500/30';

            const lineRef = f.line_start ? `Line ${f.line_start}${f.line_end && f.line_end !== f.line_start ? '-' + f.line_end : ''}` : '';

            html += `
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-4 bg-white dark:bg-slate-900/60 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase border ${sevColor}">${f.severity}</span>
                        <span class="text-xs font-bold text-slate-900 dark:text-white">${f.title}</span>
                    </div>
                    ${lineRef ? `<button onclick="highlightCodeLine(${f.line_start})" class="font-mono text-[10px] text-indigo-500 hover:underline">${lineRef}</button>` : ''}
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">${f.description || ''}</p>
                ${f.suggested_fix ? `
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400">Suggested Fix</span>
                        <button onclick="copyFixText(${idx}, this)" class="text-[10px] font-bold text-indigo-500 hover:text-indigo-600">Copy Fix</button>
                    </div>
                    <pre class="rounded-lg bg-slate-950 p-2.5 font-mono text-[11px] text-emerald-400 overflow-x-auto">${f.suggested_fix.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
                </div>` : ''}
            </div>`;
        });

        container.innerHTML = html;
    }

    function highlightCodeLine(lineNum) {
        document.querySelectorAll('.line-highlight').forEach(el => el.classList.remove('line-highlight'));
        const el = document.getElementById(`code-line-${lineNum}`);
        if (el) {
            el.classList.add('line-highlight');
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function copyFixText(index, btn) {
        if (!currentReview || !currentReview.findings[index]) return;
        const fix = currentReview.findings[index].suggested_fix;
        navigator.clipboard.writeText(fix);
        btn.innerText = 'Copied!';
        setTimeout(() => btn.innerText = 'Copy Fix', 2000);
    }

    function copyMarkdownReport() {
        if (!currentReview) return;
        let md = `# CodePulse AI Audit Report #${currentReview.id}\n\n`;
        md += `- **Date**: ${new Date(currentReview.created_at).toLocaleString()}\n`;
        md += `- **Language**: ${currentReview.language}\n`;
        md += `- **Overall Score**: ${currentReview.overall_score ?? 'N/A'}/100\n`;
        md += `- **Security**: ${currentReview.security_score ?? 'N/A'}/100\n`;
        md += `- **Performance**: ${currentReview.performance_score ?? 'N/A'}/100\n`;
        md += `- **Maintainability**: ${currentReview.maintainability_score ?? 'N/A'}/100\n\n`;
        md += `## Findings (${currentReview.findings.length})\n\n`;

        currentReview.findings.forEach((f, i) => {
            md += `### ${i + 1}. [${f.severity.toUpperCase()}] ${f.title}\n`;
            if (f.line_start) md += `- **Line**: ${f.line_start}\n`;
            md += `- **Description**: ${f.description}\n\n`;
            if (f.suggested_fix) {
                md += `\`\`\`${currentReview.language.toLowerCase()}\n${f.suggested_fix}\n\`\`\`\n\n`;
            }
        });

        navigator.clipboard.writeText(md);
        const btn = document.getElementById('copy-report-text');
        btn.innerText = 'Copied MD!';
        setTimeout(() => btn.innerText = 'Copy .md Report', 2000);
    }

    // Close on Escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeInspector();
    });
</script>
</body>
</html>
