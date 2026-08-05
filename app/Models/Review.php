<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    protected $fillable = [
        'listing_id',
        'user_id',
        'booking_id',
        'rating',
        'comment',
        'is_active',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
        'moderated_at' => 'datetime',
        'moderation_status' => ModerationStatus::class,
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function moderationChecks(): MorphMany
    {
        return $this->morphMany(ModerationCheck::class, 'moderatable');
    }
}