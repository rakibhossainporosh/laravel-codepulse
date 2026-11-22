<?php

namespace CodePulse\Services;

class PromptBuilder
{
    /**
     * Build system instructions for Gemini code reviewer.
     */
    public function buildSystemInstruction(string $language, array $focusAreas): string
    {
        $focusList = implode(', ', $focusAreas);

        return <<<PROMPT
You are CodePulse AI, an elite automated code quality, security vulnerability, and performance auditing engine for software engineers.
Your task is to conduct an authoritative, rigorous static code analysis on the provided {$language} source code.

Primary Focus Areas: {$focusList}.

Evaluation Guidelines:
1. Security: Inspect for OWASP Top 10 vulnerabilities including SQL Injection, XSS, SSRF, IDOR, Mass Assignment, Hardcoded Secrets, Insecure Deserialization, and Path Traversal.
2. Performance: Detect N+1 database queries, unbounded collection operations, memory leaks, high Big-O algorithmic complexity, redundant I/O, and missing indexing.
3. Bug & Robustness: Identify race conditions, null pointer/type errors, improper exception handling, unhandled edge cases, and arithmetic overflows.
4. Style & Maintainability: Flag violations of clean code, SOLID principles, PSR standards, DRY, and code readability.

Scoring Rules (0 to 100 integer for each dimension):
- 90-100: Production-grade, secure, performant, clean architecture.
- 75-89: Minor improvements or non-critical refactoring needed.
- 50-74: Noticeable performance bottlenecks or medium security concerns.
- 0-49: Critical vulnerabilities, exploits, or severe performance flaws.

Output must strictly conform to the provided JSON schema without any conversational filler or external markdown wrapping.
PROMPT;
    }

    /**
     * Build user prompt with the source code and context.
     */
    public function buildUserPrompt(string $sourceCode, string $language, array $focusAreas, ?string $filePath = null): string
    {
        $focusList = implode(', ', $focusAreas);
        $fileInfo = $filePath ? "File: {$filePath}\n" : "";

        return <<<PROMPT
Please audit the following {$language} code with focus on: {$focusList}.
{$fileInfo}
Source Code:
```{$language}
{$sourceCode}
```
PROMPT;
    }

    /**
     * Define the strict JSON response schema for Gemini API.
     */
    public function getResponseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'required' => ['scores', 'findings'],
            'properties' => [
                'scores' => [
                    'type' => 'OBJECT',
                    'required' => ['security', 'performance', 'maintainability'],
                    'properties' => [
                        'security' => [
                            'type' => 'INTEGER',
                            'description' => 'Security rating score between 0 and 100',
                        ],
                        'performance' => [
                            'type' => 'INTEGER',
                            'description' => 'Performance and efficiency score between 0 and 100',
                        ],
                        'maintainability' => [
                            'type' => 'INTEGER',
                            'description' => 'Clean code and maintainability score between 0 and 100',
                        ],
                    ],
                ],
                'findings' => [
                    'type' => 'ARRAY',
                    'description' => 'List of detected issues, security vulnerabilities, or performance bottlenecks',
                    'items' => [
                        'type' => 'OBJECT',
                        'required' => ['category', 'severity', 'title', 'description'],
                        'properties' => [
                            'category' => [
                                'type' => 'STRING',
                                'enum' => ['security', 'performance', 'bug', 'style'],
                                'description' => 'The category of the finding',
                            ],
                            'severity' => [
                                'type' => 'STRING',
                                'enum' => ['critical', 'high', 'medium', 'low', 'info'],
                                'description' => 'Impact severity of the finding',
                            ],
                            'title' => [
                                'type' => 'STRING',
                                'description' => 'Concise summary of the issue (under 80 characters)',
                            ],
                            'description' => [
                                'type' => 'STRING',
                                'description' => 'Detailed technical explanation of why this is problematic',
                            ],
                            'line_start' => [
                                'type' => 'INTEGER',
                                'description' => 'Starting line number of the issue in the snippet (1-indexed)',
                            ],
                            'line_end' => [
                                'type' => 'INTEGER',
                                'description' => 'Ending line number of the issue in the snippet (1-indexed)',
                            ],
                            'suggested_fix' => [
                                'type' => 'STRING',
                                'description' => 'Concrete code snippet or diff fixing the identified problem',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
