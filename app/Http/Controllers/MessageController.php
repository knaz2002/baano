<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
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
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                    ],
                    'last_message' => $conv->lastMessage ? [
                        'body' => $conv->lastMessage->body,
                        'sender_id' => $conv->lastMessage->sender_id,
                        'created_at' => $conv->lastMessage->created_at->toIso8601String(),
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            });

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Conversation $conversation)
    {
        $userId = Auth::id();

        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            abort(403);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $otherUser = $conversation->user_one_id === $userId
            ? $conversation->userTwo
            : $conversation->userOne;

        $messages = Message::where('conversation_id', $conversation->id)
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender->name,
                'is_mine' => $m->sender_id === $userId,
                'created_at' => $m->created_at->format('H:i'),
            ]);

        return Inertia::render('Messages/Show', [
            'conversation' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                ],
            ],
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();

        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
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

        return back();
    }

public function messageUser(Request $request, User $user)
{
    $userId = Auth::id();

    if ($user->id === $userId) {
        return back()->with('error', 'Нельзя написать себе');
    }

    // Создаем или получаем диалог
    $conversation = Conversation::getOrCreate($userId, $user->id);

    // Если есть текст сообщения — создаем его
    if ($request->filled('body')) {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $validated['body'],
            'is_read' => false,
        ]);
    }

    // Перенаправляем на страницу сообщений с этим диалогом
    return redirect()->route('messages.show', ['conversation' => $conversation->id]);
}

}
