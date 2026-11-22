# CodePulse AI for Laravel

CodePulse AI is an automated code quality, security vulnerability, and performance auditing package for Laravel applications powered by Google Gemini BYOK (Bring Your Own Key).

---

## Key Highlights

- **Terminal Audit Command**: Scan files, controllers, or staged Git changes right from your terminal with `php artisan codepulse:check --staged`.
- **Drop-in Web Dashboard**: Interactive, zero-setup dashboard accessible at `/codepulse` with dark/light themes.
- **BYOK Architecture**: Zero subscription costs; runs directly on your Google Gemini quota.
- **Automatic Model Fallback**: Falls back from `gemini-3.8-flash` to `gemini-3.5-flash-lite` automatically on rate limits (HTTP 429) or service outages (HTTP 503).
- **CI/CD Ready**: Returns non-zero exit codes when critical security vulnerabilities or severe performance bottlenecks are detected.

---

## Installation

Install the package via Composer:

```bash
composer require codepulse/laravel-codepulse --dev
```

Run the interactive install command:

```bash
php artisan codepulse:install
```

This will publish `config/codepulse.php` and the required database migrations.

---

## Configuration

Obtain a Gemini API key from [Google AI Studio](https://aistudio.google.com/app/apikey) and add it to your `.env` file:

```env
CODEPULSE_GEMINI_KEY=AQ.your_gemini_api_key_here
CODEPULSE_PATH=codepulse
CODEPULSE_DEFAULT_MODEL=gemini-3.8-flash
```

---

## Usage

### 1. Terminal Code Auditing

Audit staged Git changes before committing:

```bash
php artisan codepulse:check --staged
```

Audit a specific controller or directory:

```bash
php artisan codepulse:check app/Http/Controllers/OrderController.php
```

### 2. Web Dashboard

Navigate to `/codepulse` in your browser. 

In `local` and `testing` environments, the dashboard is open automatically. In production, protect access via `Gate::define('viewCodePulse', ...)` in your `AppServiceProvider`.

### 3. Programmatic Inspection via Facade

```php
use CodePulse\Facades\CodePulse;

$result = CodePulse::review($sourceCode, language: 'PHP');
$scores = $result['scores'];
$findings = $result['findings'];
```

---

## License

This package is open-sourced software licensed under the MIT license.
