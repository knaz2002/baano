<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return Inertia::render('Auth/VerifyEmail');
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        if (! hash_equals((string) $request->route('id'), (string) $user->getKey())
            || ! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->verifiedRedirect();
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return $this->verifiedRedirect(true);
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/')->with('success', 'Email уже подтвержден');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Ссылка отправлена повторно');
    }

    private function verifiedRedirect(bool $justVerified = false)
    {
        $frontend = rtrim((string) env('FRONTEND_URL', ''), '/');

        if ($frontend !== '') {
            $query = $justVerified ? '?verified=1' : '';

            return redirect()->away($frontend.'/verify-email'.$query);
        }

        return redirect('/')->with('success', $justVerified ? 'Email подтвержден' : null);
    }
}