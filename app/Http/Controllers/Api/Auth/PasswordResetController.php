<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function forgot(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [$this->statusMessage($status)],
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Мы отправили ссылку для сброса пароля на ваш email.',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Пароли не совпадают.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [$this->statusMessage($status)],
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Пароль успешно изменён. Теперь вы можете войти.',
        ]);
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            Password::RESET_LINK_SENT => 'Мы отправили ссылку для сброса пароля на ваш email.',
            Password::PASSWORD_RESET => 'Пароль успешно изменён.',
            Password::INVALID_TOKEN => 'Ссылка для сброса пароля недействительна или устарела.',
            Password::INVALID_USER => 'Пользователь с таким email не найден.',
            Password::RESET_THROTTLED => 'Подождите немного перед повторной попыткой.',
            default => 'Не удалось выполнить сброс пароля.',
        };
    }
}
