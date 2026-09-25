<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OtpController extends Controller
{
    /**
     * Tampilkan halaman verifikasi OTP (Inertia).
     */
    public function show(): Response
    {
        // Pastikan ada user yang sedang login tapi belum verified
        if (! Auth::check()) {
            return Inertia::location(route('login'));
        }

        return Inertia::render('Auth/VerifyOtp', [
            'email' => Auth::user()->email,
        ]);
    }

    /**
     * Kirim / resend OTP ke email user.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        // Rate limit: max 3 kali per 10 menit
        $recentCount = OtpCode::where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentCount >= 3) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.',
            ], 429);
        }

        $otp = OtpCode::generateFor($request->email);
        $user->notify(new OtpNotification($otp->code, $user->name));

        return response()->json([
            'message'    => 'Kode OTP berhasil dikirim.',
            'expires_at' => $otp->expires_at->toISOString(),
        ]);
    }

    /**
     * Verifikasi kode OTP yang diinput user.
     */
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

        // Tandai OTP sudah dipakai
        $otp->markUsed();

        // Set email_verified_at pada user
        $user = User::where('email', $request->email)->first();

        if ($user && ! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        return response()->json([
            'message'  => 'Email berhasil diverifikasi!',
            'verified' => true,
        ]);
    }
}
