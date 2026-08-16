<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Review;
use App\Models\ReviewInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $conversations = Conversation::visibleFor($userId)
            ->with([
                'userOne:id,name',
                'userTwo:id,name',
                'listing:id,title',
                'lastMessage:id,conversation_id,body,sender_id,created_at',
            ])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (Conversation $conversation) => $this->mapConversation($conversation, $userId));

        return response()->json(['data' => $conversations]);
    }

    public function messages(int $conversation): JsonResponse
    {
        $userId = Auth::id();

        $model = Conversation::visibleFor($userId)
            ->with([
                'userOne:id,name',
                'userTwo:id,name',
                'listing:id,title,user_id,status,is_active',
            ])
            ->findOrFail($conversation);

        if (! $model->isParticipant($userId)) {
            abort(403);
        }

        $model->messagesVisibleFor($userId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $model->messagesVisibleFor($userId)
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'is_mine' => $message->sender_id === $userId,
                'created_at' => $message->created_at->format('H:i'),
            ]);

        $summary = $this->mapConversationSummary($model, $userId);
        $summary['can_request_review'] = $this->canRequestReview($model, $userId);

        return response()->json([
            'data' => [
                'conversation' => $summary,
                'messages' => $messages,
            ],
        ]);
    }

    public function send(Request $request, int $conversation): JsonResponse
    {
        $userId = Auth::id();
        $model = Conversation::query()->findOrFail($conversation);

        if (! $model->isParticipant($userId)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = Message::query()->create([
            'conversation_id' => $model->id,
            'sender_id' => $userId,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $model->update([
            'last_message_id' => $message->id,
            'last_message_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => Auth::user()->name,
                'is_mine' => true,
                'created_at' => $message->created_at->format('H:i'),
            ],
        ], 201);
    }

    public function hide(int $conversation): JsonResponse
    {
        $userId = Auth::id();
        $model = Conversation::query()->findOrFail($conversation);

        if (! $model->isParticipant($userId)) {
            abort(403);
        }

        $model->hideFor($userId);

        return response()->json(['ok' => true]);
    }

    public function start(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'body' => ['nullable', 'string', 'max:1000'],
        ]);

        if ((int) $validated['user_id'] === $userId) {
            abort(422, 'Нельзя написать самому себе');
        }

        Listing::query()->findOrFail($validated['listing_id']);

        $conversation = Conversation::getOrCreate(
            (int) $validated['listing_id'],
            $userId,
            (int) $validated['user_id']
        );

        $conversation->restoreFor($userId);

        if (! empty($validated['body'])) {
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $userId,
                'body' => $validated['body'],
                'is_read' => false,
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);
        }

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
            ],
        ], 201);
    }

    private function mapConversation(Conversation $conversation, int $userId): array
    {
        $summary = $this->mapConversationSummary($conversation, $userId);

        $unreadCount = $conversation
            ->messagesVisibleFor($userId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();

        $hiddenAt = $conversation->hiddenAtFor($userId);
        $visibleLastMessage = $conversation->lastMessage
            && (
                ! $hiddenAt
                || $conversation->lastMessage->created_at->gt($hiddenAt)
            )
                ? $conversation->lastMessage
                : null;

        $summary['last_message'] = $visibleLastMessage ? [
            'body' => $visibleLastMessage->body,
            'sender_id' => $visibleLastMessage->sender_id,
            'created_at' => $visibleLastMessage->created_at->toIso8601String(),
        ] : null;
        $summary['unread_count'] = $unreadCount;

        return $summary;
    }

    private function mapConversationSummary(Conversation $conversation, int $userId): array
    {
        $otherUser = $conversation->user_one_id === $userId
            ? $conversation->userTwo
            : $conversation->userOne;

        return [
            'id' => $conversation->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
            ],
            'listing' => $conversation->listing ? [
                'id' => $conversation->listing->id,
                'title' => $conversation->listing->title,
                'image' => $this->listingPreviewImage($conversation->listing),
            ] : null,
        ];
    }

    private function canRequestReview(Conversation $conversation, int $userId): bool
    {
        $listing = $conversation->listing;

        if (
            ! $listing
            || $listing->user_id !== $userId
            || $listing->status !== 'active'
            || ! $listing->is_active
        ) {
            return false;
        }

        $otherUserId = $conversation->user_one_id === $userId
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        $reviewExists = Review::query()
            ->where('listing_id', $listing->id)
            ->where('user_id', $otherUserId)
            ->exists();

        if ($reviewExists) {
            return false;
        }

        $activeInviteExists = ReviewInvite::query()
            ->where('listing_id', $listing->id)
            ->where('recipient_id', $otherUserId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->exists();

        return ! $activeInviteExists;
    }

    private function listingPreviewImage(Listing $listing): ?string
    {
        $media = $listing->getFirstMedia('images');

        if ($media === null) {
            return null;
        }

        $url = $media->hasGeneratedConversion('thumb')
            ? $media->getUrl('thumb')
            : $media->getUrl();

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($url, '/');
    }
}
