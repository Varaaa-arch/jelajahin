<?php

namespace App\Mail;

use App\Models\ETicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class SendETicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public ETicket $eticket;

    public function __construct(ETicket $eticket)
    {
        $this->eticket = $eticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your E-Ticket #{$this->eticket->eticket_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.eticket',
            with: [
                'eticket' => $this->eticket,
                'passenger' => $this->eticket->booking->passengers->first(),
                'booking' => $this->eticket->booking,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path("app/public/{$this->eticket->pdf_path}"))
                ->as("eticket_{$this->eticket->eticket_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
