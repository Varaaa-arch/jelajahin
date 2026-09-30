<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification implements ShouldQueue
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
            ->subject('Refund Selesai Diproses - Jelajahin Airlines')
            ->greeting("Halo {$notifiable->name},")
            ->line("Refund Anda telah selesai diproses!")
            ->line("No. Refund: **{$this->refund->refund_number}**")
            ->line("PNR: {$this->refund->booking->pnr_code}")
            ->line("Jumlah Refund: Rp ".number_format($this->refund->approved_amount ?? $this->refund->requested_amount, 0, ',', '.'))
            ->action('Lihat Detail', url("/refunds/{$this->refund->id}"))
            ->line('Dana akan masuk ke rekening Anda dalam 1-3 hari kerja.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'refund_id' => $this->refund->id,
            'refund_number' => $this->refund->refund_number,
            'pnr_code' => $this->refund->booking->pnr_code,
            'message' => "Refund {$this->refund->refund_number} telah diproses",
            'type' => 'refund_processed',
            'url' => "/refunds/{$this->refund->id}",
        ];
    }
}
