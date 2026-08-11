<?php

namespace App\Http\Controllers;

use App\Jobs\ModerateReview;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Review;
use App\Models\ReviewInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ReviewInviteController extends Controller
{
    public function store(Conversation $conversation): JsonResponse
    {
        $userId = Auth::id();

        return DB::transaction(function () use ($conversation, $userId) {
            // Сериализуем параллельные запросы из одного диалога.
            $conversation = Conversation::query()
                ->whereKey($conversation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$conversation->isParticipant($userId)) {
                abort(403);
            }

            $conversation->loadMissing('listing');

            $listing = $conversation->listing;

            if (!$listing) {
                abort(404);
            }

            // Запрашивать отзыв может только владелец объявления.
            if ($listing->user_id !== $userId) {
                abort(403);
            }

            if ($listing->status !== 'active' || !$listing->is_active) {
                return response()->json([
                    'message' => 'Запросить отзыв можно только для активного объявления.',
                ], 422);
            }

            $recipientId = $conversation->user_one_id === $userId
                ? $conversation->user_two_id
                : $conversation->user_one_id;

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

            $inviteUrl = URL::temporarySignedRoute(
                'dashboard.review-invites.show',
                $invite->expires_at,
                ['reviewInvite' => $invite->token]
            );

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'body' => "Пожалуйста, оставьте отзыв по объявлению «{$listing->title}»: {$inviteUrl}",
                'is_read' => false,
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Запрос на отзыв отправлен пользователю.',
                'invite' => [
                    'id' => $invite->id,
                    'expires_at' => $invite->expires_at->toIso8601String(),
                ],
            ]);
        });
    }

    public function show(ReviewInvite $reviewInvite)
    {
        $userId = Auth::id();

        // Ссылка предназначена только конкретному получателю
        if ($reviewInvite->recipient_id !== $userId) {
            abort(403);
        }

        if ($reviewInvite->used_at) {
            abort(410, 'Запрос на отзыв уже использован.');
        }

        if ($reviewInvite->expires_at->isPast()) {
            abort(410, 'Срок действия запроса на отзыв истёк.');
        }

        $reviewInvite->loadMissing('listing');

        $listing = $reviewInvite->listing;

        if (
            !$listing
            || $listing->status !== 'active'
            || !$listing->is_active
        ) {
            abort(410, 'Объявление больше не активно.');
        }

        if ($listing->user_id === $userId) {
            abort(403);
        }

        return Inertia::render('Reviews/Create', [
            'invite' => [
                'token' => $reviewInvite->token,
                'listing' => [
                    'id' => $listing->id,
                    'title' => $listing->title,
                ],
                'expires_at' => $reviewInvite->expires_at->toIso8601String(),
            ],
        ]);
    }


    public function submit(Request $request, ReviewInvite $reviewInvite)
    {
        $userId = Auth::id();

        // Отзыв может отправить только получатель персонального запроса
        if ($reviewInvite->recipient_id !== $userId) {
            abort(403);
        }

        if ($reviewInvite->used_at) {
            return back()->withErrors([
                'invite' => 'Этот запрос на отзыв уже использован.',
            ]);
        }

        if ($reviewInvite->expires_at->isPast()) {
            return back()->withErrors([
                'invite' => 'Срок действия запроса на отзыв истёк.',
            ]);
        }

        $reviewInvite->loadMissing('listing');

        $listing = $reviewInvite->listing;

        if (
            !$listing
            || $listing->status !== 'active'
            || !$listing->is_active
        ) {
            return back()->withErrors([
                'invite' => 'Оставить отзыв можно только к активному объявлению.',
            ]);
        }

        // Владелец объявления не может оставить отзыв самому себе
        if ($listing->user_id === $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $reviewExists = Review::query()
            ->where('listing_id', $listing->id)
            ->where('user_id', $userId)
            ->exists();

        if ($reviewExists) {
            return back()->withErrors([
                'invite' => 'Вы уже оставили отзыв к этому объявлению.',
            ]);
        }

        $review = DB::transaction(function () use (
            $reviewInvite,
            $listing,
            $userId,
            $validated
        ) {
            // Блокируем приглашение до конца транзакции.
            // Два параллельных POST не смогут одновременно использовать его.
            $lockedInvite = ReviewInvite::query()
                ->whereKey($reviewInvite->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvite->recipient_id !== $userId) {
                abort(403);
            }

            if ($lockedInvite->used_at) {
                throw ValidationException::withMessages([
                    'invite' => 'Этот запрос на отзыв уже использован.',
                ]);
            }

            if ($lockedInvite->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'invite' => 'Срок действия запроса на отзыв истёк.',
                ]);
            }

            // Повторяем проверку уже после получения блокировки.
            $reviewExists = Review::query()
                ->where('listing_id', $listing->id)
                ->where('user_id', $userId)
                ->exists();

            if ($reviewExists) {
                throw ValidationException::withMessages([
                    'invite' => 'Вы уже оставили отзыв к этому объявлению.',
                ]);
            }

            $review = Review::create([
                'listing_id' => $listing->id,
                'user_id' => $userId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'moderation_status' => \App\Enums\ModerationStatus::PendingModeration,
                'moderation_reason' => null,
                'moderated_at' => null,
                'is_active' => false,
            ]);

            $lockedInvite->update([
                'used_at' => now(),
            ]);

            return $review;
        });

        ModerateReview::dispatch($review->id);

        return redirect()
            ->route('dashboard.reviews')
            ->with('success', 'Отзыв отправлен на модерацию.');
    }

}
