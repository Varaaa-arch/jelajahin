<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetOtpController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        $recentCount = OtpCode::where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentCount >= 3) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.',
            ], 429);
        }

        $otp = OtpCode::generateFor($request->email);
        $user->notify(new PasswordResetOtpNotification($otp->code, $user->name));

        return response()->json([
            'message'    => 'Kode OTP pengaturan ulang password berhasil dikirim.',
            'expires_at' => $otp->expires_at->toISOString(),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code'  => ['required', 'string', 'size:6'],
        ]);

        $otp = OtpCode::where('email', $request->email)
            ->where('code', $request->code)
            ->valid()
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'Kode tidak valid atau sudah kadaluarsa.',
                'field'   => 'code',
            ], 422);
        }

        $otp->markUsed();

        $user = User::where('email', $request->email)->first();

        $token = $user ? Password::broker()->createToken($user) : null;

        return response()->json([
            'message'  => 'Kode diverifikasi! Silakan atur ulang password.',
            'verified' => true,
            'token'    => $token,
            'email'    => $user->email,
        ]);
    }
}
