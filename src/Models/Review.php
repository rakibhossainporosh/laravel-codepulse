<?php

namespace CodePulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $table = 'codepulse_reviews';

    protected $guarded = ['id'];

    protected $casts = [
        'overall_score' => 'integer',
        'security_score' => 'integer',
        'performance_score' => 'integer',
        'maintainability_score' => 'integer',
        'tokens_used' => 'integer',
        'focus_areas' => 'array',
    ];

    public function getConnectionName()
    {
        return config('codepulse.database_connection') ?? parent::getConnectionName();
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'review_id');
    }

    public function criticalFindings(): HasMany
    {
        return $this->hasMany(Finding::class, 'review_id')->where('severity', 'critical');
    }
}
