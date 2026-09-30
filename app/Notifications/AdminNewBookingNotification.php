<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pembayaran baru: {$this->booking->pnr_code} - Jelajahin Admin")
            ->greeting("Halo {$notifiable->name},")
            ->line("Ada pembayaran sukses baru.")
            ->line("PNR: {$this->booking->pnr_code}")
            ->line("User: {$this->booking->user?->name} ({$this->booking->user?->email})")
            ->line("Flight: {$this->booking->flight?->flight_number}")
            ->line("Total: Rp " . number_format($this->booking->total_price, 0, ',', '.'))
            ->action('Lihat Order', url("/admin/orders/{$this->booking->id}"))
            ->line('Notifikasi otomatis admin Jelajahin.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'pnr_code' => $this->booking->pnr_code,
            'message' => "Pembayaran baru {$this->booking->pnr_code} - Rp " . number_format($this->booking->total_price, 0, ',', '.'),
            'type' => 'admin_new_booking',
            'url' => "/admin/orders/{$this->booking->id}",
        ];
    }
}
