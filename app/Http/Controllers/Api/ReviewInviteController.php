<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ModerateReview;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Review;
use App\Models\ReviewInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReviewInviteController extends Controller
{
    public function store(int $conversation): JsonResponse
    {
        $userId = Auth::id();

        return DB::transaction(function () use ($conversation, $userId) {
            $model = Conversation::query()
                ->whereKey($conversation)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $model->isParticipant($userId)) {
                abort(403);
            }

            $model->loadMissing('listing');
            $listing = $model->listing;

            if (! $listing) {
                abort(404);
            }

            if ($listing->user_id !== $userId) {
                abort(403);
            }

            if ($listing->status !== 'active' || ! $listing->is_active) {
                return response()->json([
                    'message' => 'Запросить отзыв можно только для активного объявления.',
                ], 422);
            }

            $recipientId = $model->user_one_id === $userId
                ? $model->user_two_id
                : $model->user_one_id;

            if ($recipientId === $userId) {
                abort(403);
            }

            $reviewExists = Review::query()
                ->where('listing_id', $listing->id)
                ->where('user_id', $recipientId)
                ->exists();

            if ($reviewExists) {
                return response()->json([
                    'message' => 'Этот пользователь уже оставил отзыв к объявлению.',
                ], 422);
            }

            $activeInviteExists = ReviewInvite::query()
                ->where('listing_id', $listing->id)
                ->where('recipient_id', $recipientId)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->exists();

            if ($activeInviteExists) {
                return response()->json([
                    'message' => 'Запрос на отзыв уже отправлен этому пользователю.',
                ], 422);
            }

            $invite = ReviewInvite::updateOrCreate(
                [
                    'listing_id' => $listing->id,
                    'recipient_id' => $recipientId,
                ],
                [
                    'owner_id' => $userId,
                    'token' => (string) Str::uuid(),
                    'expires_at' => now()->addDays(7),
                    'used_at' => null,
                ]
            );

            $frontend = rtrim((string) env('FRONTEND_URL', 'http://127.0.0.1:3000'), '/');
            $inviteUrl = "{$frontend}/dashboard/review-invites/{$invite->token}";

            $message = Message::query()->create([
                'conversation_id' => $model->id,
                'sender_id' => $userId,
                'body' => "Пожалуйста, оставьте отзыв по объявлению «{$listing->title}»: {$inviteUrl}",
                'is_read' => false,
            ]);

            $model->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return response()->json([
                'data' => [
                    'invite' => [
                        'id' => $invite->id,
                        'expires_at' => $invite->expires_at->toIso8601String(),
                    ],
                    'message' => [
                        'id' => $message->id,
                        'body' => $message->body,
                        'sender_id' => $message->sender_id,
                        'sender_name' => Auth::user()->name,
                        'is_mine' => true,
                        'created_at' => $message->created_at->format('H:i'),
                    ],
                ],
                'message' => 'Запрос на отзыв отправлен пользователю.',
            ], 201);
        });
    }

    public function show(string $token): JsonResponse
    {
        $userId = Auth::id();
        $invite = ReviewInvite::query()->where('token', $token)->firstOrFail();

        if ($invite->recipient_id !== $userId) {
            abort(403);
        }

        if ($invite->used_at) {
            return response()->json(['message' => 'Запрос на отзыв уже использован.'], 410);
        }

        if ($invite->expires_at->isPast()) {
            return response()->json(['message' => 'Срок действия запроса на отзыв истёк.'], 410);
        }

        $invite->loadMissing('listing');
        $listing = $invite->listing;

        if (! $listing || $listing->status !== 'active' || ! $listing->is_active) {
            return response()->json(['message' => 'Объявление больше не активно.'], 410);
        }

        if ($listing->user_id === $userId) {
            abort(403);
        }

        return response()->json([
            'data' => [
                'token' => $invite->token,
                'listing' => [
                    'id' => $listing->id,
                    'title' => $listing->title,
                ],
                'expires_at' => $invite->expires_at->toIso8601String(),
            ],
        ]);
    }

    public function submit(Request $request, string $token): JsonResponse
    {
        $userId = Auth::id();
        $invite = ReviewInvite::query()->where('token', $token)->firstOrFail();

        if ($invite->recipient_id !== $userId) {
            abort(403);
        }

        if ($invite->used_at) {
            throw ValidationException::withMessages([
                'invite' => ['Этот запрос на отзыв уже использован.'],
            ]);
        }

        if ($invite->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'invite' => ['Срок действия запроса на отзыв истёк.'],
            ]);
        }

        $invite->loadMissing('listing');
        $listing = $invite->listing;

        if (! $listing || $listing->status !== 'active' || ! $listing->is_active) {
            throw ValidationException::withMessages([
                'invite' => ['Оставить отзыв можно только к активному объявлению.'],
            ]);
        }

        if ($listing->user_id === $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $review = DB::transaction(function () use ($invite, $listing, $userId, $validated) {
            $lockedInvite = ReviewInvite::query()
                ->whereKey($invite->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvite->recipient_id !== $userId) {
                abort(403);
            }

            if ($lockedInvite->used_at) {
                throw ValidationException::withMessages([
                    'invite' => ['Этот запрос на отзыв уже использован.'],
                ]);
            }

            if ($lockedInvite->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'invite' => ['Срок действия запроса на отзыв истёк.'],
                ]);
            }

            $reviewExists = Review::query()
                ->where('listing_id', $listing->id)
                ->where('user_id', $userId)
                ->exists();

            if ($reviewExists) {
                throw ValidationException::withMessages([
                    'invite' => ['Вы уже оставили отзыв к этому объявлению.'],
                ]);
            }

            $review = Review::query()->create([
                'listing_id' => $listing->id,
                'user_id' => $userId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'moderation_status' => \App\Enums\ModerationStatus::PendingModeration,
                'moderation_reason' => null,
                'moderated_at' => null,
                'is_active' => false,
            ]);

            $lockedInvite->update(['used_at' => now()]);

            return $review;
        });

        ModerateReview::dispatch($review->id);

        return response()->json([
            'data' => [
                'id' => $review->id,
                'is_active' => false,
                'moderation_status' => $review->moderation_status,
            ],
            'message' => 'Отзыв отправлен на модерацию.',
        ], 201);
    }
}
