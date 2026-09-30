<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetOtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ─────────────────────────────────────────────────────────

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/otp/verify', [ApiAuthController::class, 'verifyOtp']);
});

Route::prefix('v1/flights')->group(function () {
    Route::get('/search', [\App\Http\Controllers\FlightController::class, 'search']);
    Route::get('/{id}/seats', [\App\Http\Controllers\FlightController::class, 'seats']);
    Route::get('/{id}', [\App\Http\Controllers\FlightController::class, 'show']);
});

// ─── OTP & Password Reset (digunakan frontend + test) ─────────────────────
Route::post('otp/send', [OtpController::class, 'send']);
Route::post('otp/verify', [OtpController::class, 'verify']);
Route::post('forgot-password', [PasswordResetOtpController::class, 'store']);
Route::post('forgot-password/otp', [PasswordResetOtpController::class, 'verify']);
Route::post('password/reset', [NewPasswordController::class, 'store']);

// ─── Protected Routes (auth:sanctum) ───────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/v1/auth/logout', [ApiAuthController::class, 'logout']);

    Route::prefix('bookings')->group(function () {
        Route::post('/', [\App\Http\Controllers\BookingController::class, 'createBooking']);
        Route::get('/{pnr}', [\App\Http\Controllers\BookingController::class, 'getByPNR']);
        Route::get('/user/{userId}', [\App\Http\Controllers\BookingController::class, 'getUserBookings']);
    });

    Route::prefix('payments')->group(function () {
        Route::post('/initiate', [\App\Http\Controllers\PaymentController::class, 'initiatePayment']);
        Route::post('/process', [\App\Http\Controllers\PaymentController::class, 'processPayment']);
        Route::get('/{transactionId}', [\App\Http\Controllers\PaymentController::class, 'getPaymentStatus']);
    });
});

// ─── Webhook (public - external callback) ──────────────────────────────────

Route::post('payments/webhook', [\App\Http\Controllers\PaymentController::class, 'webhookCallback']);
