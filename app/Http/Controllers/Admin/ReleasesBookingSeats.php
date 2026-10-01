<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\FlightSeat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dipakai bersama AdminOrderController & AdminPaymentController:
 * mengembalikan kursi penumpang ke available (cancel / refund / expire / reschedule).
 */
trait ReleasesBookingSeats
{
    private function releaseSeats(Booking $order): void
    {
        $seatIds = $order->passengers()->whereNotNull('flight_seat_id')->pluck('flight_seat_id');
        if ($seatIds->isNotEmpty()) {
            FlightSeat::whereIn('id', $seatIds)->update(['is_available' => true, 'booking_id' => null]);
        }
        FlightSeat::where('booking_id', $order->id)->update(['is_available' => true, 'booking_id' => null]);

        if ($order->flight_id) {
            $available = FlightSeat::where('flight_id', $order->flight_id)->where('is_available', true)->count();
            if ($available > 0 || FlightSeat::where('flight_id', $order->flight_id)->exists()) {
                Flight::where('id', $order->flight_id)->update(['seats_available' => $available]);
            }
        }

        $this->releaseSeatHoldsInGoService($order, $seatIds);
    }

    /**
     * Lepaskan hold/lock kursi di Go service (Redis) agar bisa dipesan orang lain.
     * Best-effort: lock mungkin sudah kedaluwarsa (404) — itu bukan error.
     */
    private function releaseSeatHoldsInGoService(Booking $order, $seatIds): void
    {
        try {
            $ids = $seatIds instanceof \Illuminate\Support\Collection ? $seatIds->all() : (array) $seatIds;
            $ids = array_values(array_filter($ids));
            if (empty($ids) || empty($order->flight_id)) {
                return;
            }

            $base = rtrim(config('services.go_seat_service.url', env('GO_SERVICE_URL', 'http://localhost:8080')), '/');
            foreach ($ids as $seatId) {
                try {
                    Http::timeout(3)->post("{$base}/api/v1/seats/unlock", [
                        'flight_id' => $order->flight_id,
                        'seat_id' => $seatId,
                        'user_id' => (string) $order->user_id,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('ReleasesBookingSeats: gagal unlock 1 kursi di Go', [
                        'booking_id' => $order->id,
                        'seat_id' => $seatId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('ReleasesBookingSeats: gagal release hold di Go', [
                'booking_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
