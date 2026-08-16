<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'data' => $this->userPayload(Auth::user()),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
        $formattedPhone = $phone !== '' ? '+'.$phone : null;

        if ($formattedPhone && User::where('phone', $formattedPhone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['Этот телефон уже зарегистрирован.'],
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'personal_data_consent' => ['required', 'accepted'],
        ], [
            'personal_data_consent.required' => 'Необходимо согласие на обработку персональных данных.',
            'personal_data_consent.accepted' => 'Необходимо согласие на обработку персональных данных.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $formattedPhone,
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return response()->json([
            'data' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userPayload($request->user()),
        ]);
    }

    private function userPayload(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

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
