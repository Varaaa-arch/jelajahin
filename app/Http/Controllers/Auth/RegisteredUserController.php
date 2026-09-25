<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Kirim OTP ke email
        $otp = OtpCode::generateFor($user->email);
        $user->notify(new OtpNotification($otp->code, $user->name));

        // Kembalikan JSON untuk modal flow (AuthModal via axios)
        if ($request->wantsJson()) {
            return response()->json(array_filter([
                'otp_sent'   => true,
                'email'      => $user->email,
                'debug_code' => app()->environment(['local', 'testing']) ? $otp->code : null,
            ], fn ($v) => $v !== null));
        }

        return redirect(route('home', absolute: false))->with('verify_otp', true);
    }
}
