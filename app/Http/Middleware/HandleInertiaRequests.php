<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return config('app.asset_url')
            ? md5(config('app.asset_url'))
            : parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        
        $unreadMessagesCount = 0;
        if ($user) {
            $unreadMessagesCount = Message::where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->whereIn('conversation_id', function ($query) use ($user) {
                    $query->select('id')
                        ->from('conversations')
                        ->where('user_one_id', $user->id)
                        ->orWhere('user_two_id', $user->id);
                })
                ->count();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
            ],
            'unreadMessagesCount' => $unreadMessagesCount,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
