<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'ok' => true,
                'message' => 'Email уже подтвержден.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'ok' => true,
            'message' => 'Ссылка отправлена повторно.',
        ]);
    }
}
