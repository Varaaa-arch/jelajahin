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
