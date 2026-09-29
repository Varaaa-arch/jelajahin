<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Akun yang baru verifikasi naik dari inactive -> active (selain suspended).
        if (($request->user()->status ?? 'active') === 'inactive') {
            $request->user()->update(['status' => 'active']);
        }

        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}
