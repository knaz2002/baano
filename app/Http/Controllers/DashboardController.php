<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Review;
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
                    'image' => ($l->getFirstMediaUrl('images') ?: null),
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
                        'image' => $listing->getFirstMediaUrl('images'),
                        'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
                    ] : null,
                ];
            })->filter(fn($item) => $item['favoritable'] !== null);

        return Inertia::render('Dashboard/Favorites', ['favorites' => $favorites->values()]);
    }

    public function messages($conversationId = null)
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne:id,name', 'userTwo:id,name', 'lastMessage:id,conversation_id,body,sender_id,created_at'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($userId) {
                $otherUser = $conv->user_one_id === $userId ? $conv->userTwo : $conv->userOne;
                $unreadCount = Message::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();

                return [
                    'id' => $conv->id,
                    'other_user' => ['id' => $otherUser->id, 'name' => $otherUser->name],
                    'last_message' => $conv->lastMessage ? [
                        'body' => $conv->lastMessage->body,
                        'sender_id' => $conv->lastMessage->sender_id,
                        'created_at' => $conv->lastMessage->created_at->toIso8601String(),
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
            $conversation = Conversation::with(['userOne:id,name', 'userTwo:id,name'])->findOrFail($conversationId);
            if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) abort(403);

            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            $data['messages'] = Message::where('conversation_id', $conversation->id)
                ->with('sender:id,name')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id, 'body' => $m->body, 'sender_id' => $m->sender_id,
                    'sender_name' => $m->sender->name, 'is_mine' => $m->sender_id === $userId,
                    'created_at' => $m->created_at->format('H:i'),
                ]);

            $data['selectedConversation'] = [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => ($conversation->user_one_id === $userId ? $conversation->userTwo->id : $conversation->userOne->id),
                    'name' => ($conversation->user_one_id === $userId ? $conversation->userTwo->name : $conversation->userOne->name),
                ]
            ];
        }

        return Inertia::render('Messages/Index', $data);
    }

    public function getConversationMessages($conversationId)
    {
        $userId = Auth::id();
        $conversation = Conversation::with(['userOne:id,name', 'userTwo:id,name'])->findOrFail($conversationId);

        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            abort(403);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('conversation_id', $conversation->id)
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id, 'body' => $m->body, 'sender_id' => $m->sender_id,
                'sender_name' => $m->sender->name, 'is_mine' => $m->sender_id === $userId,
                'created_at' => $m->created_at->format('H:i'),
            ]);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => ($conversation->user_one_id === $userId ? $conversation->userTwo->id : $conversation->userOne->id),
                    'name' => ($conversation->user_one_id === $userId ? $conversation->userTwo->name : $conversation->userOne->name),
                ]
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate(['body' => 'required|string|max:1000']);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('messages.show', $conversationId);
    }

    public function reviews()
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['listing:id,title', 'user:id,name'])
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id, 'rating' => $r->rating, 'comment' => $r->comment,
                'created_at' => $r->created_at,
                'listing' => $r->listing ? ['id' => $r->listing->id, 'title' => $r->listing->title] : null,
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
            ]);
        return Inertia::render('Dashboard/Reviews', ['reviews' => $reviews]);
    }
}
