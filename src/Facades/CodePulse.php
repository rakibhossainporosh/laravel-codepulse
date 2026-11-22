<?php

namespace CodePulse\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array review(string $sourceCode, string $language = 'PHP', array $focusAreas = ['security', 'performance', 'bug', 'style'], ?string $model = null)
 * @method static \Illuminate\Database\Eloquent\Builder reviews()
 *
 * @see \CodePulse\CodePulse
 */
class CodePulse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'codepulse';
    }
}
