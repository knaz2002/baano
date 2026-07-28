<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class PhoneVerificationController extends Controller
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

    public function show()
    {
        $user = Auth::user();

        return Inertia::render('Auth/VerifyPhone', [
            'phoneMasked' => $this->maskPhone($user?->phone),
            'resendAvailableIn' => $user
                ? RateLimiter::availableIn($this->resendKey($user->id))
                : 0,
            'debugCode' => $this->debugCodeFor($user),
        ]);
    }

    protected function debugCodeFor($user): ?string
    {
        if (! app()->environment('local') || ! $user) {
            return null;
        }

        return $user->phone_verification_code;
    }

    protected function maskPhone(?string $phone): string
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

    public function sendCode(Request $request)
    {
        $user = Auth::user();

        if (! $user->phone) {
            return back()->withErrors(['code' => 'Телефон не указан']);
        }

        if ($user->phone_verified_at) {
            return Inertia::location('/verify-email');
        }

        if ($error = $this->ensureCanSend($user->id)) {
            return back()->withErrors(['code' => $error]);
        }

        if (! $this->issueAndSendCode($user)) {
            return back()->withErrors(['code' => 'Не удалось отправить код. Попробуйте позже.']);
        }

        return back()->with('success', 'Код отправлен');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();

        if ($error = $this->ensureCanVerify($user->id)) {
            return back()->withErrors(['code' => $error]);
        }

        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        if (! $user->phone_verification_code || ! $user->phone_verification_expires_at) {
            return back()->withErrors(['code' => 'Код не отправлен или истек']);
        }

        if (now()->greaterThan($user->phone_verification_expires_at)) {
            return back()->withErrors(['code' => 'Код истек']);
        }

        if ($user->phone_verification_code !== $request->code) {
            RateLimiter::hit($this->verifyKey($user->id), self::VERIFY_DECAY_SECONDS);

            $remaining = self::MAX_VERIFY_ATTEMPTS - RateLimiter::attempts($this->verifyKey($user->id));

            if ($remaining <= 0) {
                $seconds = RateLimiter::availableIn($this->verifyKey($user->id));

                return back()->withErrors([
                    'code' => "Слишком много попыток. Подождите {$seconds} сек.",
                ]);
            }

            return back()->withErrors([
                'code' => "Неверный код. Осталось попыток: {$remaining}",
            ]);
        }

        RateLimiter::clear($this->verifyKey($user->id));

        $user->phone_verified_at = now();
        $user->phone_verification_code = null;
        $user->phone_verification_expires_at = null;
        $user->save();

        Log::info('Phone verified', ['user' => $user->id]);

        $user->sendEmailVerificationNotification();

        return Inertia::location('/verify-email');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return Inertia::location('/verify-email');
        }

        if (! $user->phone) {
            return back()->withErrors(['code' => 'Телефон не указан']);
        }

        if ($error = $this->ensureCanSend($user->id)) {
            return back()->withErrors(['code' => $error]);
        }

        if (! $this->issueAndSendCode($user)) {
            return back()->withErrors(['code' => 'Не удалось отправить код. Попробуйте позже.']);
        }

        return back()->with('success', 'Новый код отправлен');
    }

    /**
     * Отметить отправку после регистрации (cooldown + часовой лимит).
     */
    public static function markSent(int $userId): void
    {
        RateLimiter::hit(self::resendKeyFor($userId), self::RESEND_DECAY_SECONDS);
        RateLimiter::hit(self::hourKeyFor($userId), self::SEND_HOUR_DECAY_SECONDS);
    }

    protected function issueAndSendCode($user): bool
    {
        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->phone_verification_code = $code;
        $user->phone_verification_expires_at = now()->addMinutes(10);
        $user->save();

        if (! $this->smsService->sendVerificationCode($user->phone, $code)) {
            return false;
        }

        self::markSent($user->id);
        RateLimiter::clear($this->verifyKey($user->id));

        return true;
    }

    protected function ensureCanSend(int $userId): ?string
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

    protected function ensureCanVerify(int $userId): ?string
    {
        if (! RateLimiter::tooManyAttempts($this->verifyKey($userId), self::MAX_VERIFY_ATTEMPTS)) {
            return null;
        }

        $seconds = RateLimiter::availableIn($this->verifyKey($userId));

        return "Слишком много попыток. Подождите {$seconds} сек.";
    }

    protected function resendKey(int $userId): string
    {
        return self::resendKeyFor($userId);
    }

    protected function hourKey(int $userId): string
    {
        return self::hourKeyFor($userId);
    }

    protected function verifyKey(int $userId): string
    {
        return 'phone-verify-attempts:'.$userId;
    }

    protected static function resendKeyFor(int $userId): string
    {
        return 'phone-verify-resend:'.$userId;
    }

    protected static function hourKeyFor(int $userId): string
    {
        return 'phone-verify-send-hour:'.$userId;
    }
}
