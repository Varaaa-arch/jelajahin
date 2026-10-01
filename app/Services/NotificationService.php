<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ETicket;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\AdminNewBookingNotification;
use App\Notifications\AdminRefundRequestedNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Notifications\RefundApprovedNotification;
use App\Notifications\RefundProcessedNotification;
use App\Notifications\RefundRejectedNotification;
use App\Notifications\RefundRequestedNotification;
use App\Notifications\TicketReadyNotification;
use Illuminate\Support\Facades\Notification;

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

    /**
     * Send booking rejected notification (admin menolak awaiting_confirmation).
     * Refund otomatis dibuat terpisah oleh AdminOrderController.
     */
    public function notifyBookingRejected(Booking $booking, ?string $reason = null): void
    {
        try {
            if ($booking->user) {
                $booking->user->notify(new BookingRejectedNotification($booking, $reason));
                \Log::info("Booking rejected notification sent for booking: {$booking->pnr_code}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send booking rejected notification: {$e->getMessage()}");
        }
    }

    public function notifyRefundRequested(Refund $refund): void
    {
        try {
            if ($refund->user) {
                $refund->user->notify(new RefundRequestedNotification($refund));
                \Log::info("Refund requested notification sent for refund: {$refund->refund_number}");
            }
            $this->notifyAdminsRefundRequested($refund);
        } catch (\Exception $e) {
            \Log::error("Failed to send refund requested notification: {$e->getMessage()}");
        }
    }

    /**
     * Alert ke semua admin saat ada booking lunas (email + database).
     */
    public function notifyAdminsNewBooking(Booking $booking): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isEmpty()) {
                return;
            }
            Notification::send($admins, new AdminNewBookingNotification($booking));
            \Log::info("Admin new-booking alert sent for booking: {$booking->pnr_code}");
        } catch (\Exception $e) {
            \Log::error("Failed to send admin new-booking alert: {$e->getMessage()}");
        }
    }

    /**
     * Alert ke semua admin saat ada refund request baru.
     */
    public function notifyAdminsRefundRequested(Refund $refund): void
    {
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isEmpty()) {
                return;
            }
            // Jangan kirim dobel ke requester kalau dia admin.
            $admins = $admins->reject(fn ($u) => $refund->user && $u->id === $refund->user->id);
            if ($admins->isEmpty()) {
                return;
            }
            Notification::send($admins, new AdminRefundRequestedNotification($refund));
            \Log::info("Admin refund-requested alert sent for refund: {$refund->refund_number}");
        } catch (\Exception $e) {
            \Log::error("Failed to send admin refund-requested alert: {$e->getMessage()}");
        }
    }

    public function notifyRefundApproved(Refund $refund): void
    {
        try {
            if ($refund->user) {
                $refund->user->notify(new RefundApprovedNotification($refund));
                \Log::info("Refund approved notification sent for refund: {$refund->refund_number}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send refund approved notification: {$e->getMessage()}");
        }
    }

    public function notifyRefundRejected(Refund $refund): void
    {
        try {
            if ($refund->user) {
                $refund->user->notify(new RefundRejectedNotification($refund));
                \Log::info("Refund rejected notification sent for refund: {$refund->refund_number}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send refund rejected notification: {$e->getMessage()}");
        }
    }

    public function notifyRefundProcessed(Refund $refund): void
    {
        try {
            if ($refund->user) {
                $refund->user->notify(new RefundProcessedNotification($refund));
                \Log::info("Refund processed notification sent for refund: {$refund->refund_number}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send refund processed notification: {$e->getMessage()}");
        }
    }
}
