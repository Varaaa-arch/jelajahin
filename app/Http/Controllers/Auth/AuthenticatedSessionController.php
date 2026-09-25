<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\OtpCode;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        $needsOtp = $user && ! $user->hasVerifiedEmail();
        $debugCode = null;

        if ($needsOtp) {
            OtpCode::where('email', $user->email)->delete();
            $otp = OtpCode::generateFor($user->email);
            $user->notify(new OtpNotification($otp->code, $user->name));
            $debugCode = app()->environment(['local', 'testing']) ? $otp->code : null;

            session(['verify_otp' => true]);
        }

        if ($request->wantsJson()) {
            return response()->json(array_filter([
                'needs_otp'  => $needsOtp,
                'email'      => $user->email,
                'debug_code' => $debugCode,
            ], fn ($v) => $v !== null));
        }

        return redirect(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
