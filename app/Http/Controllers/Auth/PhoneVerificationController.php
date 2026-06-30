<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneVerificationController extends Controller
{
    public function show()
    {
        return view('auth.verify-phone');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
        ]);

        $user = Auth::user();

        if (!$user->phone_verification_code || !$user->phone_verification_expires_at) {
            return back()->withErrors(['code' => 'Код не был отправлен']);
        }

        if (now()->greaterThan($user->phone_verification_expires_at)) {
            return back()->withErrors(['code' => 'Срок действия кода истёк']);
        }

        if ($user->phone_verification_code !== $request->code) {
            return back()->withErrors(['code' => 'Неверный код']);
        }

        $user->phone_verified_at = now();
        $user->phone_verification_code = null;
        $user->phone_verification_expires_at = null;
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Телефон подтверждён!');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return back()->with('success', 'Телефон уже подтверждён');
        }

        $user->phone_verification_code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->phone_verification_expires_at = now()->addMinutes(10);
        $user->save();

        // В production — отправка SMS
        \Log::info("Новый SMS код для {$user->phone}: {$user->phone_verification_code}");

        return back()->with('success', 'Новый код отправлен');
    }
}