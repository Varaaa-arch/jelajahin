<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ETicket;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\TicketReadyNotification;

class NotificationService
{
    /**
     * Send booking confirmed notification
     */
    public function notifyBookingConfirmed(Booking $booking): void
    {
        try {
            if ($booking->user) {
                $booking->user->notify(new BookingConfirmedNotification($booking));
                \Log::info("Booking confirmed notification sent for booking: {$booking->pnr_code}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send booking confirmed notification: {$e->getMessage()}");
        }
    }

    /**
     * Send ticket ready notification
     */
    public function notifyTicketReady(ETicket $eticket): void
    {
        try {
            if ($eticket->booking->user) {
                $eticket->booking->user->notify(new TicketReadyNotification($eticket));
                \Log::info("Ticket ready notification sent for e-ticket: {$eticket->eticket_number}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send ticket ready notification: {$e->getMessage()}");
        }
    }

    /**
     * Send all notifications untuk booking (booking confirmed + ticket ready)
     */
    public function notifyBookingComplete(Booking $booking): void
    {
        // Send booking confirmed
        $this->notifyBookingConfirmed($booking);

        // Send ticket ready untuk setiap e-ticket
        $etickets = ETicket::where('booking_id', $booking->id)->get();
        foreach ($etickets as $eticket) {
            $this->notifyTicketReady($eticket);
        }
    }
}
