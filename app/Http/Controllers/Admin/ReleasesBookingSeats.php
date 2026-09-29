<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\FlightSeat;

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
    }
}
