<?php

namespace App\Notifications;

use App\Models\ETicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ETicket $eticket;

    public function __construct(ETicket $eticket)
    {
        $this->eticket = $eticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->eticket->booking;

        return (new MailMessage)
            ->subject('Your E-Ticket is Ready - Jelajahin Airlines')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your e-ticket is now ready!")
            ->line("E-Ticket Number: **{$this->eticket->eticket_number}**")
            ->line("Passenger: {$this->eticket->passenger_name}")
            ->line("Flight: {$this->eticket->flight_number}")
            ->line("Date: {$this->eticket->departure_date->format('d M Y')} at {$this->eticket->departure_time}")
            ->line("Seat: {$this->eticket->seat_number}")
            ->action('Download Ticket', url("/eticket/{$this->eticket->eticket_number}"))
            ->line('Please arrive 2 hours before departure.')
            ->line('Thank you for flying with us!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'eticket_id' => $this->eticket->id,
            'eticket_number' => $this->eticket->eticket_number,
            'message' => "Your e-ticket {$this->eticket->eticket_number} is ready",
            'type' => 'ticket_ready',
            'url' => "/eticket/{$this->eticket->eticket_number}",
        ];
    }
}
