<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CodePulse Master Switch
    |--------------------------------------------------------------------------
    |
    | Enable or disable the CodePulse package. When disabled, dashboard routes
    | and artisan check commands will not execute.
    |
    */
    'enabled' => env('CODEPULSE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Web Dashboard Path
    |--------------------------------------------------------------------------
    |
    | The URI path where the CodePulse review dashboard will be accessible.
    | By default, this is 'codepulse' (e.g. https://your-app.test/codepulse).
    |
    */
    'path' => env('CODEPULSE_PATH', 'codepulse'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the CodePulse dashboard routes. By default, it uses
    | the 'web' group. In production, access is guarded by Gate::define('viewCodePulse').
    |
    */
    'middleware' => [
        'web',
        \CodePulse\Http\Middleware\Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini BYOK API Key
    |--------------------------------------------------------------------------
    |
    | You can supply your Google Gemini API key via the .env file or dynamically
    | enter and encrypt it through the CodePulse dashboard settings.
    | Supports keys starting with 'AQ.' or legacy 'AIza'.
    |
    */
    'api_key' => env('CODEPULSE_GEMINI_KEY'),

    /*
    |--------------------------------------------------------------------------
    | AI Models
    |--------------------------------------------------------------------------
    |
    | Selected Google Gemini model for code & security analysis.
    | If the primary model returns HTTP 429 or 503, CodePulse automatically
    | falls back to the fallback_model before reporting failure.
    |
    */
    'default_model' => env('CODEPULSE_DEFAULT_MODEL', 'gemini-3.8-flash'),
    'fallback_model' => env('CODEPULSE_FALLBACK_MODEL', 'gemini-3.5-flash-lite'),

    /*
    |--------------------------------------------------------------------------
    | Ignored Paths for CLI & Git Checks
    |--------------------------------------------------------------------------
    |
    | Folders and files that will be skipped when running `php artisan codepulse:check`.
    |
    */
    'ignore_paths' => [
        'vendor',
        'node_modules',
        'storage',
        'bootstrap/cache',
        'public',
        'tests',
        '.git',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Audit Focus Areas
    |--------------------------------------------------------------------------
    |
    | Default analysis categories evaluated by the Gemini review engine.
    | Options: 'security', 'performance', 'bug', 'style'
    |
    */
    'focus_areas' => [
        'security',
        'performance',
        'bug',
        'style',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Database connection used to store reviews and findings. Set to null
    | to use your application's default database connection.
    |
    */
    'database_connection' => env('CODEPULSE_DB_CONNECTION', null),
];
