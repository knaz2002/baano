<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Listing extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'price_type',
        'location',
        'city',
        'listing_attributes',
        'status',
        'is_active',
        'is_premium',
        'premium_days',
        'premium_until',
        'requested_is_active',
        'moderation_status',
        'moderation_reason',
        'moderated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'premium_days' => 'integer',
        'premium_until' => 'datetime',
        'requested_is_active' => 'boolean',
        'listing_attributes' => 'array',
        'moderated_at' => 'datetime',
        'moderation_status' => ModerationStatus::class,
    ];

    protected $appends = ['image'];

    protected static function booted(): void
    {
        static::saving(function (Listing $listing): void {
            if (! $listing->is_premium) {
                $listing->premium_until = null;

                return;
            }

            if (
                $listing->premium_days !== null
                && (
                    $listing->isDirty('is_premium')
                    || $listing->isDirty('premium_days')
                    || $listing->premium_until === null
                )
            ) {
                $listing->premium_until = now()->addDays($listing->premium_days);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function moderationChecks(): MorphMany
    {
        return $this->morphMany(ModerationCheck::class, 'moderatable');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function isFavoritedBy($user)
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function getImageAttribute(): ?string
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl() : null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(300)
             ->height(200)
             ->nonQueued();
    }

// Геттер для обратной совместимости
public function getAttributesAttribute()
{
    return $this->listing_attributes;
}

// Сеттер для обратной совместимости
public function setAttributesAttribute($value)
{
    $this->listing_attributes = $value;
}
}