<?php

use App\Http\Controllers\BookingDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserPaymentMethodController;
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
        'originCode'     => request('origin', 'CGK'),
        'originCity'     => request('originCity', 'Jakarta'),
        'destinationCode'=> request('destination', 'DPS'),
        'destinationCity'=> request('destinationCity', 'Denpasar'),
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

Route::get('/booking/documents/{pnr}', [BookingDocumentController::class, 'download'])
    ->middleware('auth')
    ->name('booking.documents');

// Metode pembayaran tersimpan (JSON untuk dashboard, auth session + owner check)
Route::middleware('auth')->prefix('payment-methods')->name('payment-methods.')->group(function () {
    Route::get('/', [UserPaymentMethodController::class, 'index'])->name('index');
    Route::post('/', [UserPaymentMethodController::class, 'store'])->name('store');
    Route::patch('/{id}', [UserPaymentMethodController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserPaymentMethodController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/default', [UserPaymentMethodController::class, 'setDefault'])->name('default');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Token Sanctum untuk sesi web (dipakai frontend memanggil routes/api.php
    // via Bearer). Diterbitkan on-demand memakai session cookie; dipanggil
    // sekali saat boot bila localStorage belum punya token (mis. habis
    // social login, reload halaman, atau token kedaluwarsa).
    Route::get('/web/auth-token', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'token' => \App\Services\WebAuthToken::issue($request->user()),
        ]);
    })->name('web.auth-token');
});

// Refund routes (user-facing)
Route::middleware(['auth', 'verified'])->prefix('refunds')->name('refunds.')->group(function () {
    Route::get('/', [\App\Http\Controllers\RefundController::class, 'index'])->name('index');
    Route::get('/{refund}', [\App\Http\Controllers\RefundController::class, 'show'])->name('show');
    Route::post('/', [\App\Http\Controllers\RefundController::class, 'store'])->name('store');
    Route::delete('/{refund}', [\App\Http\Controllers\RefundController::class, 'cancel'])->name('cancel');
});

// Admin area (Inertia, tema navy/teal). Filament dipindah ke /sysadmin.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\Admin\AdminDashboardController::class)->name('dashboard');
    Route::get('/flights', [\App\Http\Controllers\Admin\AdminFlightController::class, 'index'])->name('flights.index');
    Route::post('/flights', [\App\Http\Controllers\Admin\AdminFlightController::class, 'store'])->name('flights.store');
    Route::put('/flights/{flight}', [\App\Http\Controllers\Admin\AdminFlightController::class, 'update'])->name('flights.update');
    Route::delete('/flights/{flight}', [\App\Http\Controllers\Admin\AdminFlightController::class, 'destroy'])->name('flights.destroy');
    Route::get('/passengers', [\App\Http\Controllers\Admin\AdminPassengerController::class, 'index'])->name('passengers.index');
    Route::put('/passengers/{passenger}', [\App\Http\Controllers\Admin\AdminPassengerController::class, 'update'])->name('passengers.update');
    Route::get('/orders', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'update'])->name('orders.update');
    Route::put('/orders/{order}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::put('/orders/{order}/reschedule', [\App\Http\Controllers\Admin\AdminOrderController::class, 'reschedule'])->name('orders.reschedule');
    Route::get('/orders/{order}/receipt', [\App\Http\Controllers\Admin\AdminOrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/payments', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'index'])->name('payments.index');
    Route::put('/payments/{payment}', [\App\Http\Controllers\Admin\AdminPaymentController::class, 'updateStatus'])->name('payments.status');
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/password', [\App\Http\Controllers\Admin\AdminUserController::class, 'resetPassword'])->name('users.password');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/reports', [\App\Http\Controllers\Admin\AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/refunds', [\App\Http\Controllers\Admin\AdminRefundController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/{refund}', [\App\Http\Controllers\Admin\AdminRefundController::class, 'show'])->name('refunds.show');
    Route::put('/refunds/{refund}/approve', [\App\Http\Controllers\Admin\AdminRefundController::class, 'approve'])->name('refunds.approve');
    Route::put('/refunds/{refund}/reject', [\App\Http\Controllers\Admin\AdminRefundController::class, 'reject'])->name('refunds.reject');
    Route::put('/refunds/{refund}/process', [\App\Http\Controllers\Admin\AdminRefundController::class, 'process'])->name('refunds.process');
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\AdminSettingController::class, 'update'])->name('settings.update');
    Route::put('/settings/profile', [\App\Http\Controllers\Admin\AdminSettingController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [\App\Http\Controllers\Admin\AdminSettingController::class, 'updatePassword'])->name('settings.password');
});

require __DIR__.'/auth.php';