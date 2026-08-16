<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PhoneVerificationService
{
    private const MAX_RESEND_PER_MINUTE = 1;

    private const RESEND_DECAY_SECONDS = 60;

    private const MAX_SENDS_PER_HOUR = 5;

    private const SEND_HOUR_DECAY_SECONDS = 3600;

    private const MAX_VERIFY_ATTEMPTS = 5;

    private const VERIFY_DECAY_SECONDS = 900;

    public function __construct(
        protected SmsService $smsService
    ) {}

    public function maskPhone(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) < 4) {
            return $phone;
        }

        $lastTwo = substr($digits, -2);

        return '+7 *** ***-**-'.$lastTwo;
    }

    public function debugCodeFor(?User $user): ?string
    {
        if (! app()->environment('local') || ! $user) {
            return null;
        }

        return $user->phone_verification_code;
    }

    public function resendAvailableIn(int $userId): int
    {
        return RateLimiter::availableIn($this->resendKey($userId));
    }

    public function ensureCanSend(int $userId): ?string
    {
        if (RateLimiter::tooManyAttempts($this->resendKey($userId), self::MAX_RESEND_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($this->resendKey($userId));

            return "Повторная отправка через {$seconds} сек.";
        }

        if (RateLimiter::tooManyAttempts($this->hourKey($userId), self::MAX_SENDS_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($this->hourKey($userId));
            $minutes = (int) ceil($seconds / 60);

            return "Превышен лимит отправок. Попробуйте через {$minutes} мин.";
        }

        return null;
    }

    public function ensureCanVerify(int $userId): ?string
    {
        if (! RateLimiter::tooManyAttempts($this->verifyKey($userId), self::MAX_VERIFY_ATTEMPTS)) {
            return null;
        }

        $seconds = RateLimiter::availableIn($this->verifyKey($userId));

        return "Слишком много попыток. Подождите {$seconds} сек.";
    }

    public function issueAndSendCode(User $user): bool
    {
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->phone_verification_code = $code;
        $user->phone_verification_expires_at = now()->addMinutes(10);
        $user->save();

        $sent = $this->smsService->sendVerificationCode((string) $user->phone, $code);

        if (! $sent) {
            // В local/dev код всё равно в БД и виден на /verify-phone (debug_code).
            // На проде без доставки SMS — ошибка.
            if (app()->isProduction()) {
                return false;
            }

            Log::warning('Phone verification code saved, but delivery failed', [
                'user' => $user->id,
                'phone' => $user->phone,
            ]);
        }

        $this->markSent($user->id);
        RateLimiter::clear($this->verifyKey($user->id));

        return true;
    }

    public function markSent(int $userId): void
    {
        RateLimiter::hit($this->resendKey($userId), self::RESEND_DECAY_SECONDS);
        RateLimiter::hit($this->hourKey($userId), self::SEND_HOUR_DECAY_SECONDS);
    }

    public function verifyCode(User $user, string $code): ?string
    {
        if ($error = $this->ensureCanVerify($user->id)) {
            return $error;
        }

        if (! $user->phone_verification_code || ! $user->phone_verification_expires_at) {
            return 'Код не отправлен или истек';
        }

        if (now()->greaterThan($user->phone_verification_expires_at)) {
            return 'Код истек';
        }

        if ($user->phone_verification_code !== $code) {
            RateLimiter::hit($this->verifyKey($user->id), self::VERIFY_DECAY_SECONDS);

            $remaining = self::MAX_VERIFY_ATTEMPTS - RateLimiter::attempts($this->verifyKey($user->id));

            if ($remaining <= 0) {
                $seconds = RateLimiter::availableIn($this->verifyKey($user->id));

                return "Слишком много попыток. Подождите {$seconds} сек.";
            }

            return "Неверный код. Осталось попыток: {$remaining}";
        }

        RateLimiter::clear($this->verifyKey($user->id));

        $user->phone_verified_at = now();
        $user->phone_verification_code = null;
        $user->phone_verification_expires_at = null;
        $user->save();

        Log::info('Phone verified', ['user' => $user->id]);

        return null;
    }

    protected function resendKey(int $userId): string
    {
        return 'phone-verify-resend:'.$userId;
    }

    protected function hourKey(int $userId): string
    {
        return 'phone-verify-send-hour:'.$userId;
    }

    protected function verifyKey(int $userId): string
    {
        return 'phone-verify-attempts:'.$userId;
    }
}
