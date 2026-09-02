<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class SendInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice #{$this->invoice->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'booking' => $this->invoice->booking,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path("app/public/{$this->invoice->pdf_path}"))
                ->as("invoice_{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
