<?php

namespace App\Models;

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
    'status',
    'is_active',
];
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

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

public function favorites()
{
    return $this->morphMany(Favorite::class, 'favoritable');
}

public function isFavoritedBy($user)
{
    return $this->favorites()->where('user_id', $user->id)->exists();
}
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
             ->useDisk('public');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(300)
             ->height(200)
             ->nonQueued();
    }
}
