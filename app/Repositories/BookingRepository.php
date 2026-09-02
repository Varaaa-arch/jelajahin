<?php

namespace App\Repositories;

use App\Models\Booking;

class BookingRepository
{
    /**
     * Get booking by PNR code
     */
    public function getByPNR(string $pnrCode): ?Booking
    {
        return Booking::where('pnr_code', $pnrCode)
            ->with(['passengers', 'flight'])
            ->first();
    }

    /**
     * Get user bookings
     */
    public function getUserBookings(string $userId): array
    {
        return Booking::where('user_id', $userId)
            ->with(['passengers', 'flight'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Update booking status
     */
    public function updateStatus(string $bookingId, string $status): Booking
    {
        $booking = Booking::find($bookingId);
        $booking->update(['status' => $status]);
        return $booking;
    }
}
