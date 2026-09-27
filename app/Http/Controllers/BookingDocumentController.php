<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FlightSeat;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingDocumentController extends Controller
{
    /**
     * Unduh dokumen booking sebagai PDF.
     * GET /booking/documents/{pnr}?doc=eticket|invoice
     */
    public function download(Request $request, string $pnr)
    {
        $booking = Booking::where('pnr_code', strtoupper($pnr))
            ->with(['passengers', 'flight.route.originAirport', 'flight.route.destinationAirport', 'flight.route.airline'])
            ->first();

        if (! $booking) {
            abort(404, 'Booking tidak ditemukan.');
        }

        if ($booking->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $doc = $request->query('doc', 'eticket');
        if (! in_array($doc, ['eticket', 'invoice'], true)) {
            abort(404, 'Jenis dokumen tidak dikenal.');
        }

        if ($doc === 'invoice') {
            return $this->downloadInvoice($booking);
        }

        return $this->downloadETicket($booking);
    }

    private function bookingContext(Booking $booking): array
    {
        $flight = $booking->flight;
        $route = $flight?->route;

        $passengers = $booking->passengers->map(function ($p, $idx) use ($booking) {
            return [
                'no' => $idx + 1,
                'name' => trim("{$p->title} {$p->first_name} {$p->last_name}"),
                'seat' => $this->resolveSeat($p->flight_seat_id),
                'identity' => $p->identity_number ?? '-',
            ];
        })->toArray();

        return [
            'pnr_code' => $booking->pnr_code,
            'airline' => $route?->airline?->name ?? 'Jelajahin Airlines',
            'flight_number' => $flight?->flight_number ?? '-',
            'origin_code' => $route?->originAirport?->code ?? 'CGK',
            'origin_city' => $route?->originAirport?->city ?? 'Jakarta',
            'destination_code' => $route?->destinationAirport?->code ?? 'DPS',
            'destination_city' => $route?->destinationAirport?->city ?? 'Denpasar',
            'departure_date' => $flight?->departure_date?->format('d M Y') ?? '-',
            'departure_time' => $flight ? substr((string) $flight->departure_time, 0, 5) : '-',
            'arrival_time' => $flight ? substr((string) $flight->arrival_time, 0, 5) : '-',
            'booking_date' => $booking->created_at?->format('d M Y') ?? '-',
            'passengers' => $passengers,
            'passenger_count' => count($passengers),
            'base_amount' => (float) $booking->base_amount,
            'tax_amount' => (float) $booking->tax_amount,
            'discount_amount' => (float) $booking->discount_amount,
            'addons_amount' => (float) ($booking->addons_amount ?? 0),
            'total_amount' => (float) $booking->total_price,
            'status' => $booking->status,
        ];
    }

    private function resolveSeat(?string $flightSeatId): string
    {
        if (! $flightSeatId) {
            return 'TBA';
        }
        try {
            $seat = FlightSeat::with('aircraftSeat')->find($flightSeatId);
            return $seat?->aircraftSeat?->seat_number ?? 'TBA';
        } catch (\Throwable) {
            return 'TBA';
        }
    }

    private function downloadETicket(Booking $booking): Response
    {
        $ctx = $this->bookingContext($booking);

        $pdf = Pdf::loadView('documents.ticket', $ctx)
            ->setPaper('a4', 'portrait');

        return $pdf->download("etiket-{$booking->pnr_code}.pdf");
    }

    private function downloadInvoice(Booking $booking): Response
    {
        $invoice = $this->ensureInvoice($booking);
        $ctx = array_merge($this->bookingContext($booking), [
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->issued_date?->format('d M Y') ?? now()->format('d M Y'),
            'invoice_status' => $invoice->status,
        ]);

        $pdf = Pdf::loadView('documents.invoice', $ctx)
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$booking->pnr_code}.pdf");
    }

    /**
     * Pastikan ada baris invoice untuk booking (idempoten, tidak menghentikan unduhan bila gagal).
     */
    private function ensureInvoice(Booking $booking): Invoice
    {
        $existing = Invoice::where('booking_id', $booking->id)->first();
        if ($existing) {
            if ($booking->status === 'confirmed' && $existing->status !== 'paid') {
                $existing->update(['status' => 'paid']);
            }
            return $existing->fresh();
        }

        return Invoice::create([
            'booking_id' => $booking->id,
            'invoice_number' => $this->nextInvoiceNumber(),
            'issued_date' => now()->toDateString(),
            'subtotal' => $booking->base_amount,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total_amount' => $booking->total_price,
            'status' => $booking->status === 'confirmed' ? 'paid' : 'issued',
            'invoice_url' => null,
        ]);
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->year;
        $month = str_pad((string) now()->month, 2, '0', STR_PAD_LEFT);

        $last = Invoice::whereYear('issued_date', $year)
            ->whereMonth('issued_date', now()->month)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($last) {
            $parts = explode('/', (string) $last->invoice_number);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('INV/%d/%s/%06d', $year, $month, $sequence);
    }
}
