<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RefundRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Refund $refund;

    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Refund Request Diterima - Jelajahin Airlines')
            ->greeting("Halo {$notifiable->name},")
            ->line("Refund request Anda telah diterima dan sedang diproses.")
            ->line("No. Refund: **{$this->refund->refund_number}**")
            ->line("PNR: {$this->refund->booking->pnr_code}")
            ->line("Tipe: ".ucfirst($this->refund->refund_type))
            ->line("Jumlah: Rp ".number_format($this->refund->requested_amount, 0, ',', '.'))
            ->action('Lihat Status Refund', url("/refunds/{$this->refund->id}"))
            ->line('Kami akan menginformasikan setelah refund diproses.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'refund_id' => $this->refund->id,
            'refund_number' => $this->refund->refund_number,
            'pnr_code' => $this->refund->booking->pnr_code,
            'message' => "Refund request {$this->refund->refund_number} telah diterima",
            'type' => 'refund_requested',
            'url' => "/refunds/{$this->refund->id}",
        ];
    }
}
