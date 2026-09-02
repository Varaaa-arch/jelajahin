<?php

namespace App\Services;

use App\Mail\SendInvoiceMail;
use App\Models\Booking;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    // Invoice untuk booking
    public function generateInvoice(Booking $booking): Invoice
    {
        // Check if invoice already exists
        $existingInvoice = Invoice::where('booking_id', $booking->id)->first();
        if($existingInvoice){
            return $existingInvoice;
        }

        $invoiceNumber = $this->generateInvoiceNumber();
        $invoiceDate = now();

        // Generate PDF
        $invoiceUrl = $this->generatePDF($booking, $invoiceNumber, $invoiceDate);

        // Create invoice record
        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'invoice_number' => $invoiceNumber,
            'issued_date' => $invoiceDate,
            'subtotal' => $booking->base_amount,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total_amount' => $booking->total_price,
            'status' => 'unpaid',
            'invoice_url' => $invoiceUrl,
        ]);

        return $invoice;
    }

    // Send Invoice Via Email
    public function sendInvoiceEmail(Invoice $invoice): bool
    {
        try{
            $userEmail = $invoice->booking->user->email ?? null;

            if (!$userEmail) {
                \Log::warning("No email found for booking: {$invoice->booking_id}");
                return false;
            }

            // Send Email
            Mail::to($userEmail)->send(new SendInvoiceMail($invoice));

            \Log::info("Invoice sent to {$userEmail}: {$invoice->invoice_number}");
            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to send invoice {$invoice->invoice_number}: {$e->getMessage()}");
            return false;
        }
    }

    // Generate invoice dan send email untuk booking
    public function generateAndSendInvoice(Booking $booking): bool
    {
        try {
            // Generate invoice
            $invoice = $this->generateInvoice($booking);

            // Send email
            $this->sendInvoiceEmail($invoice);

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to generate and send invoice for booking {$booking->id}: {$e->getMessage()}");
            return false;
        }
    }

    // Mark invoice as paid
    public function markAsPaid(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'paid',
        ]);

        // Update booking status
        $invoice->booking->update(['status' => 'confirmed']);
    }

    // Generate Unique invoice number
    private function generateInvoiceNumber(): string
    {
        // Format: INV/2026/09/000001
        $year = now()->year;
        $month = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
        
        $lastInvoice = Invoice::whereYear('issued_date', $year)
            ->whereMonth('issued_date', now()->month)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastInvoice) {
            $lastNumber = explode('/', $lastInvoice->invoice_number);
            $sequence = (int)end($lastNumber) + 1;
        }

        $invoiceNumber = sprintf('INV/%d/%s/%06d', $year, $month, $sequence);

        return $invoiceNumber;
    }

    // Generate PDF Invoice
    private function generatePDF(Booking $booking, string $invoiceNumber, $invoiceDate): string
    {
        $flight = $booking->flight;
        $passengers = $booking->passengers;

        $data = [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate->format('d M Y'),
            'pnr_code' => $booking->pnr_code,
            'flight_number' => $flight->flight_number,
            'departure_date' => $flight->departure_date->format('d M Y'),
            'passenger_count' => $passengers->count(),
            'subtotal' => $booking->base_amount,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total_amount' => $booking->total_price,
            'passengers' => $passengers->map(fn($p) => "{$p->title}. {$p->first_name} {$p->last_name}")->toArray(),
        ];
        $pdf = Pdf::loadView('invoice', $data);

        $filename = "invoice_{$invoiceNumber}.pdf";
        $path = "invoices/{$booking->id}/{$filename}";
        
        if (!is_dir(storage_path("app/public/invoices/{$booking->id}"))) {
            mkdir(storage_path("app/public/invoices/{$booking->id}"), 0755, true);
        }

        $pdf->save(storage_path("app/public/{$path}"));

        return $path;
    }

    // Get invoice by number
    public function getInvoiceByNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::where('invoice_number', $invoiceNumber)
            ->with(['booking'])
            ->first();
    }
}