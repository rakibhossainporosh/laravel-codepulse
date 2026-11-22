# CodePulse AI for Laravel

[![Run Tests](https://github.com/rakibhossainporosh/laravel-codepulse/actions/workflows/run-tests.yml/badge.svg)](https://github.com/rakibhossainporosh/laravel-codepulse/actions/workflows/run-tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/rakibhossainporosh/laravel-codepulse.svg?style=flat-square)](https://packagist.org/packages/rakibhossainporosh/laravel-codepulse)
[![Total Downloads](https://img.shields.io/packagist/dt/rakibhossainporosh/laravel-codepulse.svg?style=flat-square)](https://packagist.org/packages/rakibhossainporosh/laravel-codepulse)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

CodePulse AI is an automated code quality, security vulnerability, and performance auditing package for Laravel applications powered by Google Gemini BYOK (Bring Your Own Key).

---

## Key Highlights

- **Terminal Audit Command**: Scan files, controllers, or staged Git changes right from your terminal with `php artisan codepulse:check --staged`.
- **Automated Git Pre-Commit Hook**: Install a Git pre-commit quality gate with `php artisan codepulse:hook --install` to prevent critical vulnerabilities from ever being committed.
- **Drop-in Web Dashboard**: Interactive, zero-dependency dashboard accessible at `/codepulse` with dark/light themes, drag-and-drop file upload, and real-time status polling.
- **Line-by-Line Code Inspector**: View line-highlighted syntax, detailed vulnerability findings, and 1-click "Copy Fix" buttons.
- **Markdown Export**: Copy or download detailed audit reports in standard Markdown (.md) format.
- **BYOK Architecture**: Zero monthly fees; runs directly on your Google Gemini quota.
- **Automatic Model Fallback**: Automatically falls back from `gemini-3.8-flash` to `gemini-3.5-flash-lite` on rate limits (HTTP 429) or service outages (HTTP 503).
- **CI/CD Ready**: Returns exit code 1 when critical security issues or severe bottlenecks are detected.

---

## Installation

Install the package via Composer:

```bash
composer require rakibhossainporosh/laravel-codepulse --dev
```

Run the interactive install command:

```bash
php artisan codepulse:install
```

This will publish `config/codepulse.php` and run the required database migrations.

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

### 2. Git Pre-Commit Quality Gate

Automatically audit staged files before every `git commit`:

```bash
# Install Git pre-commit hook
php artisan codepulse:hook --install

# Remove Git pre-commit hook
php artisan codepulse:hook --remove
```

### 3. Web Dashboard

Navigate to `/codepulse` in your browser. 

In `local` and `testing` environments, the dashboard is open automatically. In production, protect access via `Gate::define('viewCodePulse', ...)` in your `AppServiceProvider`.

### 4. Programmatic Inspection via Facade

```php
use CodePulse\Facades\CodePulse;

$result = CodePulse::review($sourceCode, language: 'PHP');
$scores = $result['scores'];
$findings = $result['findings'];
```

---

## Testing

```bash
vendor/bin/phpunit
```

---

## License

This package is open-sourced software licensed under the MIT license.
