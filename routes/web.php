<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
        'verifyOtp'   => (bool) session()->pull('verify_otp', false),
    ]);
})->name('home');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/flights/search', function () {
    return Inertia::render('Flight/Results', [
        'origin'      => request('origin'),
        'destination' => request('destination'),
        'dates'       => request('dates'),
        'passengers'  => request('passengers'),
    ]);
})->name('flights.results');

Route::get('/penerbangan', function () {
    return Inertia::render('Flight/Search', [
        'origin'      => request('origin'),
        'destination' => request('destination'),
        'dates'       => request('dates'),
        'passengers'  => request('passengers'),
    ]);
})->name('flights.search');

Route::get('/faq', function () {
    return Inertia::render('FAQ');
})->name('faq');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::get('/terms', function () {
    return Inertia::render('Terms');
})->name('terms');

Route::get('/privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

Route::get('/flights/{flightId}', function (string $flightId) {
    return Inertia::render('Flight/Detail', [
        'flightId' => $flightId,
    ]);
})->name('flight.detail');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/booking/review', function () {
    return Inertia::render('Booking/Review', [
        'flightId'       => request('flightId'),
        'passengerCount' => (int) request('passengerCount', 1),
        'adultCount'     => (int) request('adultCount', 1),
        'childCount'     => (int) request('childCount', 0),
        'infantCount'    => (int) request('infantCount', 0),
    ]);
})->name('booking.review');

Route::get('/booking/create', function () {
    return Inertia::render('Booking/Create', [
        'flightId'       => request('flightId'),
        'passengerCount' => (int) request('passengerCount', 1),
        'adultCount'     => (int) request('adultCount', 1),
        'childCount'     => (int) request('childCount', 0),
        'infantCount'    => (int) request('infantCount', 0),
    ]);
})->name('booking.create');

Route::get('/booking/payment', function () {
    return Inertia::render('Booking/Payment', [
        'bookingId'      => request('bookingId'),
        'pnrCode'        => request('pnr'),
        'totalAmount'    => (float) request('total', 0),
        'flightNumber'   => request('flight'),
        'passengerCount' => (int) request('passengers', 1),
        'paymentMethod'  => request('method', 'credit_card'),
    ]);
})->name('booking.payment');

Route::get('/booking/success', function () {
    return Inertia::render('Booking/Success', [
        'pnrCode'        => request('pnr'),
        'totalAmount'    => (float) request('total', 0),
        'flightNumber'   => request('flight'),
        'passengerCount' => (int) request('passengers', 1),
        'paymentMethod'  => request('method', 'credit_card'),
        'originCode'     => request('origin', 'CGK'),
        'originCity'     => request('originCity', 'Jakarta'),
        'destinationCode'=> request('destination', 'DPS'),
        'destinationCity'=> request('destinationCity', 'Bali'),
    ]);
})->name('booking.success');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';