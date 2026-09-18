<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/flights/search', function () {
    return Inertia::render('Flight/Search');
})->name('flights.search');

Route::get('/flights/{flightId}', function (string $flightId) {
    return Inertia::render('Flight/Detail', [
        'flightId' => $flightId,
    ]);
})->name('flight.detail');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/booking/create', function () {
    return Inertia::render('Booking/Create', [
        'flightId' => request('flightId'),
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