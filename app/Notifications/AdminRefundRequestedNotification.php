<?php

namespace App\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRefundRequestedNotification extends Notification implements ShouldQueue
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Refund request baru: {$this->refund->refund_number}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Ada refund request baru yang perlu direview.")
            ->line("No. Refund: {$this->refund->refund_number}")
            ->line("PNR: {$this->refund->booking?->pnr_code}")
            ->line("User: {$this->refund->user?->name} ({$this->refund->user?->email})")
            ->line("Jumlah: Rp " . number_format($this->refund->requested_amount, 0, ',', '.'))
            ->line("Alasan: {$this->refund->reason}")
            ->action('Review Refund', url("/admin/refunds/{$this->refund->id}"))
            ->line('Segera approve / reject dari dashboard admin.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'refund_id' => $this->refund->id,
            'refund_number' => $this->refund->refund_number,
            'pnr_code' => $this->refund->booking?->pnr_code,
            'message' => "Refund baru {$this->refund->refund_number} ({$this->refund->booking?->pnr_code}) perlu direview",
            'type' => 'admin_refund_requested',
            'url' => "/admin/refunds/{$this->refund->id}",
        ];
    }
}
