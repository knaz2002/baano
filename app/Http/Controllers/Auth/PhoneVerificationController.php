<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PhoneVerificationController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    public function show()
    {
        return Inertia::render('Auth/VerifyPhone');
    }

    public function sendCode(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->phone) {
            return back()->withErrors(['code' => 'Телефон не указан']);
        }

        if ($user->phone_verified_at) {
            return Inertia::location('/verify-email');
        }

        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        
        $user->phone_verification_code = $code;
        $user->phone_verification_expires_at = now()->addMinutes(10);
        $user->save();

        $this->pushService->logVerificationCode($user->name, $user->email, $user->phone, $code);

        return back()->with('success', 'Код отправлен');
    }

    public function verify(Request $request)
    {
        \Log::info('Verify attempt', ['code' => $request->code, 'user' => Auth::id()]);
        
        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        $user = Auth::user();

        if (!$user->phone_verification_code || !$user->phone_verification_expires_at) {
            \Log::warning('No code found');
            return back()->withErrors(['code' => 'Код не отправлен или истек']);
        }

        if (now()->greaterThan($user->phone_verification_expires_at)) {
            \Log::warning('Code expired');
            return back()->withErrors(['code' => 'Код истек']);
        }

        if ($user->phone_verification_code !== $request->code) {
            \Log::warning('Wrong code', ['expected' => $user->phone_verification_code, 'got' => $request->code]);
            return back()->withErrors(['code' => 'Неверный код']);
        }

        $user->phone_verified_at = now();
        $user->phone_verification_code = null;
        $user->phone_verification_expires_at = null;
        $user->save();

        \Log::info('Phone verified', ['user' => $user->id]);

        $user->sendEmailVerificationNotification();

        return Inertia::location('/verify-email');
    }

public function resend(Request $request)
{
    \Log::info('Resend code attempt', ['user' => Auth::id()]);
    
    $user = Auth::user();
    
    if ($user->phone_verified_at) {
        \Log::info('Phone already verified');
        return Inertia::location('/verify-email');
    }

    $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    
    $user->phone_verification_code = $code;
    $user->phone_verification_expires_at = now()->addMinutes(10);
    $user->save();

    \Log::info('Code generated and saved', ['code' => $code, 'user' => $user->id]);

    // Явно записываем код в файл
    $logFile = 'c:/baano/baano/veryfy.txt';
    $datetime = now()->format('Y-m-d H:i:s');
    $logEntry = "{$datetime} | {$user->name} | {$user->email} | {$user->phone} | Код: {$code} (повторно)\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    \Log::info('Code written to file', ['file' => $logFile]);

    return back()->with('success', 'Новый код отправлен');
}
}
