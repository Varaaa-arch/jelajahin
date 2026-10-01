<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Booking $booking;

    public ?string $reason;

    public function __construct(Booking $booking, ?string $reason = null)
    {
        $this->booking = $booking;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Pesanan {$this->booking->pnr_code} belum bisa kami konfirmasi - Jelajahin")
            ->greeting("Halo {$notifiable->name},")
            ->line("Mohon maaf, pesanan {$this->booking->pnr_code} ({$this->booking->flight?->flight_number}) belum bisa dikonfirmasi admin.")
            ->line('Pembayaran kamu akan dikembalikan otomatis. Refund tercatat dan bisa dipantau di halaman Refund.')
            ->action('Lihat Refund Saya', url('/refunds'))
            ->line('Terima kasih atas pengertiannya.');

        if ($this->reason) {
            $mail->line("Catatan admin: {$this->reason}");
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'pnr_code' => $this->booking->pnr_code,
            'message' => "Pesanan {$this->booking->pnr_code} ditolak admin, refund otomatis dibuat.",
            'type' => 'booking_rejected',
            'url' => '/refunds',
        ];
    }
}
