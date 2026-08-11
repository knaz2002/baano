<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationCheck extends Model
{
    protected $fillable = [
        'moderatable_type',
        'moderatable_id',
        'content_type',
        'content_reference',
        'content_hash',
        'content_snapshot',
        'provider',
        'model',
        'status',
        'categories',
        'scores',
        'reason',
        'checked_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'content_snapshot' => 'array',
            'categories' => 'array',
            'scores' => 'array',
            'checked_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
