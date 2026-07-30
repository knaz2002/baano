<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'listing_id',
        'user_one_id',
        'user_two_id',
        'last_message_id',
        'last_message_at',
        'hidden_for_user_one_at',
        'hidden_for_user_two_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'hidden_for_user_one_at' => 'datetime',
        'hidden_for_user_two_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public static function getOrCreate(
        int $listingId,
        int $userId1,
        int $userId2
    ): self {
        $ids = [$userId1, $userId2];
        sort($ids);

        return self::firstOrCreate([
            'listing_id' => $listingId,
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
        ]);
    }

    public function hiddenAtFor(int $userId)
    {
        if ($this->user_one_id === $userId) {
            return $this->hidden_for_user_one_at;
        }

        if ($this->user_two_id === $userId) {
            return $this->hidden_for_user_two_at;
        }

        return null;
    }

    public function hideFor(int $userId): void
    {
        if ($this->user_one_id === $userId) {
            $this->update(['hidden_for_user_one_at' => now()]);
            return;
        }

        if ($this->user_two_id === $userId) {
            $this->update(['hidden_for_user_two_at' => now()]);
            return;
        }

        abort(403);
    }

    public function restoreFor(int $userId): void
    {
        if ($this->user_one_id === $userId) {
            $this->update(['hidden_for_user_one_at' => null]);
            return;
        }

        if ($this->user_two_id === $userId) {
            $this->update(['hidden_for_user_two_at' => null]);
            return;
        }

        abort(403);
    }

    public function isParticipant(int $userId): bool
    {
        return $this->user_one_id === $userId
            || $this->user_two_id === $userId;
    }

    public function scopeVisibleFor($query, int $userId)
    {
        return $query->where(function ($query) use ($userId) {
            $query
                ->where(function ($query) use ($userId) {
                    $query->where('user_one_id', $userId)
                        ->where(function ($query) {
                            $query->whereNull('hidden_for_user_one_at')
                                ->orWhereColumn(
                                    'last_message_at',
                                    '>',
                                    'hidden_for_user_one_at'
                                );
                        });
                })
                ->orWhere(function ($query) use ($userId) {
                    $query->where('user_two_id', $userId)
                        ->where(function ($query) {
                            $query->whereNull('hidden_for_user_two_at')
                                ->orWhereColumn(
                                    'last_message_at',
                                    '>',
                                    'hidden_for_user_two_at'
                                );
                        });
                });
        });
    }

    public function messagesVisibleFor(int $userId)
    {
        $query = $this->messages();

        $hiddenAt = $this->hiddenAtFor($userId);

        if ($hiddenAt) {
            $query->where('created_at', '>', $hiddenAt);
        }

        return $query;
    }
}
