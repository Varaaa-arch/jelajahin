<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Announcement $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->announcement->subject)
            ->greeting("Halo {$notifiable->name},")
            ->line($this->announcement->message)
            ->action('Buka Jelajahin', url('/dashboard'))
            ->line('Pengumuman resmi dari tim Jelajahin Airlines.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'message' => $this->announcement->subject,
            'preview' => mb_substr($this->announcement->message, 0, 120),
            'type' => 'admin_announcement',
            'url' => '/notifications',
        ];
    }
}
