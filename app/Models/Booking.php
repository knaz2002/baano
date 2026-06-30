<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'listing_id', 'customer_id', 'owner_id', 'total_price',
        'starts_at', 'ends_at', 'status', 'comment'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
    ];

    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function owner(): BelongsTo   { return $this->belongsTo(User::class, 'owner_id'); }
}