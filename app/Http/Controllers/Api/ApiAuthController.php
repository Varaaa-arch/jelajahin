<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ApiAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->status === 'suspended') {
            return response()->json(['message' => 'Account suspended'], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            OtpCode::where('email', $user->email)->delete();
            $otp = OtpCode::generateFor($user->email);
            $user->notify(new OtpNotification($otp->code, $user->name));

            return response()->json([
                'needs_otp' => true,
                'email' => $user->email,
                'debug_code' => app()->environment(['local', 'testing']) ? $otp->code : null,
            ]);
        }

        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'customer',
            'status' => 'inactive',
        ]);

        OtpCode::where('email', $user->email)->delete();
        $otp = OtpCode::generateFor($user->email);
        $user->notify(new OtpNotification($otp->code, $user->name));

        return response()->json([
            'needs_otp' => true,
            'email' => $user->email,
            'debug_code' => app()->environment(['local', 'testing']) ? $otp->code : null,
        ], 201);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $otp = OtpCode::where('email', $request->email)
            ->where('code', $request->code)
            ->valid()
            ->latest()
            ->first();

        if (! $otp) {
            return response()->json([
                'message' => 'Kode tidak valid atau sudah kadaluarsa.',
                'field' => 'code',
            ], 422);
        }

        $otp->markUsed();

        $user = User::where('email', $request->email)->first();

        if ($user && ! $user->email_verified_at) {
            $user->markEmailAsVerified();
        }

        if ($user && ($user->status ?? 'active') === 'inactive') {
            $user->update(['status' => 'active']);
        }

        $token = $user->createToken('api-token', ['*'], now()->addHours(24))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
