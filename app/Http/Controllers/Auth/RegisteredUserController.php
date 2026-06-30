<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $phone = preg_replace('/\D/', '', $request->phone);
        if (strlen($phone) === 10) {
            $phone = '7' . $phone;
        }
        $phone = '+' . $phone;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
        ]);

        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $user->phone_verification_code = $code;
        $user->phone_verification_expires_at = now()->addMinutes(10);
        $user->save();

        \Log::info("Код подтверждения для {$phone}: {$code}");

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('phone.verify')
            ->with('success', 'Код отправлен');
    }
}