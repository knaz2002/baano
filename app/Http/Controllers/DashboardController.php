<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Review;
use App\Models\ReviewInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Dashboard/Index', [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone],
        ]);
    }

    public function listings()
    {
        $listings = Listing::where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->get()
            ->map(function($l) {
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'price' => $l->price,
                    'status' => $l->is_active ? 'active' : 'pending',
                    'category' => $l->category ? ['name' => $l->category->name] : null,
                    'image' => $l->getFirstMediaUrl('images', 'thumb'),
                    'favorites_count' => Favorite::where('favoritable_type', 'App\\Models\\Listing')
                        ->where('favoritable_id', $l->id)
                        ->count(),
                ];
            });

        return Inertia::render('Listing/Index', ['listings' => $listings]);
    }

    public function favorites()
    {
        $favorites = Favorite::where('user_id', Auth::id())
            ->with(['favoritable.user', 'favoritable.category'])
            ->latest()
            ->get()
            ->map(function ($favorite) {
                $listing = $favorite->favoritable;
                return [
                    'id' => $favorite->id,
                    'favoritable' => $listing ? [
                        'id' => $listing->id,
                        'title' => $listing->title,
                        'price' => $listing->price,
                        'image' => $listing->getFirstMediaUrl('images', 'thumb'),
                        'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
                    ] : null,
                ];
            })->filter(fn($item) => $item['favoritable'] !== null);

        return Inertia::render('Dashboard/Favorites', ['favorites' => $favorites->values()]);
    }

    public function messages($conversationId = null)
    {
        $userId = Auth::id();

        $conversations = Conversation::visibleFor($userId)
            ->with([
                'userOne:id,name',
                'userTwo:id,name',
                'listing:id,title,user_id,status,is_active',
                'lastMessage:id,conversation_id,body,sender_id,created_at',
            ])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) use ($userId) {
                $otherUser = $conversation->user_one_id === $userId
                    ? $conversation->userTwo
                    : $conversation->userOne;

                $unreadCount = $conversation
                    ->messagesVisibleFor($userId)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();

                $hiddenAt = $conversation->hiddenAtFor($userId);

                $visibleLastMessage = $conversation->lastMessage
                    && (
                        !$hiddenAt
                        || $conversation->lastMessage->created_at->gt($hiddenAt)
                    )
                        ? $conversation->lastMessage
                        : null;

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                    ],
                    'listing' => $conversation->listing ? [
                        'id' => $conversation->listing->id,
                        'title' => $conversation->listing->title,
                    ] : null,
                    'last_message' => $visibleLastMessage ? [
                        'body' => $visibleLastMessage->body,
                        'sender_id' => $visibleLastMessage->sender_id,
                        'created_at' => $visibleLastMessage
                            ->created_at
                            ->toIso8601String(),
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            });

        $data = [
            'conversations' => $conversations,
            'messages' => [],
            'selectedConversation' => null,
        ];

        if ($conversationId) {
            $conversation = Conversation::visibleFor($userId)
                ->with([
                    'userOne:id,name',
                    'userTwo:id,name',
                    'listing:id,title,user_id,status,is_active',
                ])
                ->findOrFail($conversationId);

            if (!$conversation->isParticipant($userId)) {
                abort(403);
            }

            $conversation
                ->messagesVisibleFor($userId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $data['messages'] = $conversation
                ->messagesVisibleFor($userId)
                ->with('sender:id,name')
                ->orderBy('created_at')
                ->get()
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'is_mine' => $message->sender_id === $userId,
                    'created_at' => $message->created_at->format('H:i'),
                ]);

            $otherUser = $conversation->user_one_id === $userId
                ? $conversation->userTwo
                : $conversation->userOne;

            $data['selectedConversation'] = [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                ],
                'listing' => $conversation->listing ? [
                    'id' => $conversation->listing->id,
                    'title' => $conversation->listing->title,
                ] : null,
            ];
        }

        return Inertia::render('Messages/Index', $data);
    }

    public function getConversationMessages($conversationId)
    {
        $userId = Auth::id();

        $conversation = Conversation::visibleFor($userId)
            ->with([
                'userOne:id,name',
                'userTwo:id,name',
                'listing:id,title,user_id,status,is_active',
            ])
            ->findOrFail($conversationId);

        if (!$conversation->isParticipant($userId)) {
            abort(403);
        }

        $conversation
            ->messagesVisibleFor($userId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $conversation
            ->messagesVisibleFor($userId)
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'is_mine' => $message->sender_id === $userId,
                'created_at' => $message->created_at->format('H:i'),
            ]);

        $otherUser = $conversation->user_one_id === $userId
            ? $conversation->userTwo
            : $conversation->userOne;

        $canRequestReview = false;

        if (
            $conversation->listing
            && $conversation->listing->user_id === $userId
            && $conversation->listing->status === 'active'
            && $conversation->listing->is_active
        ) {
            $reviewExists = Review::query()
                ->where('listing_id', $conversation->listing->id)
                ->where('user_id', $otherUser->id)
                ->exists();

            $activeInviteExists = ReviewInvite::query()
                ->where('listing_id', $conversation->listing->id)
                ->where('recipient_id', $otherUser->id)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->exists();

            $canRequestReview = !$reviewExists && !$activeInviteExists;
        }

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                ],
                'listing' => $conversation->listing ? [
                    'id' => $conversation->listing->id,
                    'title' => $conversation->listing->title,
                ] : null,
                'can_request_review' => $canRequestReview,
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $userId = Auth::id();

        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->isParticipant($userId)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_id' => $message->id,
            'last_message_at' => now(),
        ]);


        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route(
            'messages.show',
            $conversation->id
        );
    }

    public function hideConversation($conversationId)
    {
        $userId = Auth::id();

        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->isParticipant($userId)) {
            abort(403);
        }

        $conversation->hideFor($userId);

        return response()->json([
            'success' => true,
        ]);
    }

    public function reviews()
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['listing:id,title,user_id,status,is_active', 'user:id,name'])
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at,
                'listing' => $r->listing ? ['id' => $r->listing->id, 'title' => $r->listing->title] : null,
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
            ]);
        return Inertia::render('Dashboard/Reviews', ['reviews' => $reviews]);
    }
}