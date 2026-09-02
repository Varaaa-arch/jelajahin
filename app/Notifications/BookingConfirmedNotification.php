<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Confirmed - Jelajahin Airlines')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your booking has been confirmed!")
            ->line("Booking Reference: **{$this->booking->pnr_code}**")
            ->line("Flight: {$this->booking->flight->flight_number}")
            ->line("Departure: {$this->booking->flight->departure_date->format('d M Y')} at {$this->booking->flight->departure_time}")
            ->line("Total Amount: Rp " . number_format($this->booking->total_price, 0, ',', '.'))
            ->action('View Booking', url("/booking/{$this->booking->pnr_code}"))
            ->line('Thank you for choosing Jelajahin Airlines!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'pnr_code' => $this->booking->pnr_code,
            'message' => "Your booking {$this->booking->pnr_code} has been confirmed",
            'type' => 'booking_confirmed',
            'url' => "/booking/{$this->booking->pnr_code}",
        ];
    }
}
