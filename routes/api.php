<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
