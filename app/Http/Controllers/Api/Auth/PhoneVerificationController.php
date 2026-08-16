<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function __construct(
        protected PhoneVerificationService $phoneVerification
    ) {}

    public function status(): JsonResponse
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return response()->json([
                'data' => [
                    'phone_verified' => true,
                    'phone_masked' => $this->phoneVerification->maskPhone($user->phone),
                    'resend_available_in' => 0,
                    'debug_code' => null,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'phone_verified' => false,
                'phone_masked' => $this->phoneVerification->maskPhone($user->phone),
                'resend_available_in' => $this->phoneVerification->resendAvailableIn($user->id),
                'debug_code' => $this->phoneVerification->debugCodeFor($user),
            ],
        ]);
    }

    public function resend(): JsonResponse
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return response()->json([
                'ok' => true,
                'message' => 'Телефон уже подтверждён',
                'data' => [
                    'phone_verified' => true,
                    'resend_available_in' => 0,
                    'debug_code' => null,
                ],
            ]);
        }

        if (! $user->phone) {
            throw ValidationException::withMessages([
                'code' => ['Телефон не указан'],
            ]);
        }

        if ($error = $this->phoneVerification->ensureCanSend($user->id)) {
            throw ValidationException::withMessages([
                'code' => [$error],
            ]);
        }

        if (! $this->phoneVerification->issueAndSendCode($user)) {
            throw ValidationException::withMessages([
                'code' => ['Не удалось отправить код. Попробуйте позже.'],
            ]);
        }

        $user->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Код отправлен',
            'data' => [
                'phone_verified' => false,
                'phone_masked' => $this->phoneVerification->maskPhone($user->phone),
                'resend_available_in' => $this->phoneVerification->resendAvailableIn($user->id),
                'debug_code' => $this->phoneVerification->debugCodeFor($user),
            ],
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return response()->json([
                'ok' => true,
                'message' => 'Телефон уже подтверждён',
                'data' => $this->userPayload($user),
            ]);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:4'],
        ]);

        if ($error = $this->phoneVerification->verifyCode($user, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => [$error],
            ]);
        }

        $user->refresh();

        if (! $user->email_verified_at) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Телефон подтверждён',
            'data' => $this->userPayload($user),
        ]);
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'email_verified_at' => $user->email_verified_at,
            'phone_verified_at' => $user->phone_verified_at,
        ];
    }
}
