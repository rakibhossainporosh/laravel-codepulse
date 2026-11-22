<?php

namespace CodePulse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finding extends Model
{
    protected $table = 'codepulse_findings';

    protected $guarded = ['id'];

    protected $casts = [
        'line_start' => 'integer',
        'line_end' => 'integer',
    ];

    public function getConnectionName()
    {
        return config('codepulse.database_connection') ?? parent::getConnectionName();
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}
