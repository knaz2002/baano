<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return Inertia::render('Auth/VerifyEmail');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect('/');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        return redirect('/')->with('success', 'Email подтвержден');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/')->with('success', 'Email уже подтвержден');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Ссылка отправлена повторно');
    }
}
