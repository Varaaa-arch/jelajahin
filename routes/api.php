<?php

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetOtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/flights')->group(function () {
    Route::get('/search', [\App\Http\Controllers\FlightController::class, 'search']);
    Route::get('/{id}/seats', [\App\Http\Controllers\FlightController::class, 'seats']);
    Route::get('/{id}', [\App\Http\Controllers\FlightController::class, 'show']);
});

Route::prefix('bookings')->group(function () {
    Route::post('/', [\App\Http\Controllers\BookingController::class, 'createBooking']);
    Route::get('/{pnr}', [\App\Http\Controllers\BookingController::class, 'getByPNR']);
    Route::get('/user/{userId}', [\App\Http\Controllers\BookingController::class, 'getUserBookings']);
});

Route::prefix('payments')->group(function () {
    Route::post('/initiate', [\App\Http\Controllers\PaymentController::class, 'initiatePayment']);
    Route::post('/process', [\App\Http\Controllers\PaymentController::class, 'processPayment']);
    Route::post('/webhook', [\App\Http\Controllers\PaymentController::class, 'webhookCallback']);
    Route::get('/{transactionId}', [\App\Http\Controllers\PaymentController::class, 'getPaymentStatus']);
});

// ─── OTP & Password Reset (digunakan frontend + test) ─────────────────────
Route::post('otp/send', [OtpController::class, 'send']);
Route::post('otp/verify', [OtpController::class, 'verify']);
Route::post('forgot-password', [PasswordResetOtpController::class, 'store']);
Route::post('forgot-password/otp', [PasswordResetOtpController::class, 'verify']);
Route::post('password/reset', [NewPasswordController::class, 'store']);
