<?php

namespace App\Services;

use App\Mail\SendETicketMail;
use App\Models\Booking;
use App\Models\ETicket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class ETicketService
{
    /**
     * Generate e-ticket untuk booking
     */
    public function generateETicket(Booking $booking): ETicket
    {
        $flight = $booking->flight;
        $passengers = $booking->passengers;

        $lastETicket = null;

        foreach ($passengers as $idx => $passenger) {
            $eticketNumber = $this->generateETicketNumber();
            $seatNumber = $passenger->flight_seat_id ? $this->getSeatNumber($passenger->flight_seat_id) : 'TBA';

            $pdfPath = $this->generatePDF($booking, $passenger, $eticketNumber, $seatNumber);

            $eticket = ETicket::create([
                'booking_id' => $booking->id,
                'eticket_number' => $eticketNumber,
                'passenger_name' => "{$passenger->first_name} {$passenger->last_name}",
                'flight_number' => $flight->flight_number,
                'departure_date' => $flight->departure_date,
                'departure_time' => $flight->departure_time,
                'seat_number' => $seatNumber,
                'pdf_path' => $pdfPath,
                'is_sent' => false,
            ]);

            $lastETicket = $eticket;
        }

        return $lastETicket;
    }

    /**
     * Send e-ticket via email
     */
    public function sendETicketEmail(ETicket $eticket): bool
    {
        try {
            // Get passenger email
            $passenger = $eticket->booking->passengers->where('id', $eticket->booking->passengers->first()->id)->first();
            $userEmail = $eticket->booking->user->email ?? null;

            if (!$userEmail) {
                \Log::warning("No email found for booking: {$eticket->booking_id}");
                return false;
            }

            // Send email
            Mail::to($userEmail)->send(new SendETicketMail($eticket));

            // Update sent status
            $eticket->update([
                'is_sent' => true,
                'sent_at' => now(),
            ]);

            \Log::info("E-ticket sent to {$userEmail}: {$eticket->eticket_number}");
            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to send e-ticket {$eticket->eticket_number}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Generate e-tickets dan send emails untuk booking
     */
    public function generateAndSendETickets(Booking $booking): bool
    {
        try {
            // Generate e-tickets
            $etickets = ETicket::where('booking_id', $booking->id)->get();

            if ($etickets->isEmpty()) {
                $this->generateETicket($booking);
                $etickets = ETicket::where('booking_id', $booking->id)->get();
            }

            // Send emails untuk setiap e-ticket
            foreach ($etickets as $eticket) {
                $this->sendETicketEmail($eticket);
            }

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to generate and send e-tickets for booking {$booking->id}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Generate PDF e-ticket
     */
    private function generatePDF(Booking $booking, $passenger, string $eticketNumber, string $seatNumber): string
    {
        $flight = $booking->flight;

        $data = [
            'eticket_number' => $eticketNumber,
            'pnr_code' => $booking->pnr_code,
            'passenger_name' => "{$passenger->first_name} {$passenger->last_name}",
            'passenger_title' => $passenger->title,
            'flight_number' => $flight->flight_number,
            'departure_date' => $flight->departure_date->format('d M Y'),
            'departure_time' => substr($flight->departure_time, 0, 5),
            'arrival_time' => substr($flight->arrival_time, 0, 5),
            'seat_number' => $seatNumber,
            'gate' => 'TBA',
            'booking_date' => $booking->created_at->format('d M Y'),
        ];

        $pdf = Pdf::loadView('eticket', $data);

        $filename = "eticket_{$eticketNumber}.pdf";
        $path = "etickets/{$booking->id}/{$filename}";
        
        if (!is_dir(storage_path("app/public/etickets/{$booking->id}"))) {
            mkdir(storage_path("app/public/etickets/{$booking->id}"), 0755, true);
        }

        $pdf->save(storage_path("app/public/{$path}"));

        return $path;
    }

    /**
     * Generate unique e-ticket number
     */
    private function generateETicketNumber(): string
    {
        do {
            $number = 'TKT' . strtoupper(Str::random(2)) . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (ETicket::where('eticket_number', $number)->exists());

        return $number;
    }

    /**
     * Get seat number dari flight_seat_id
     */
    private function getSeatNumber(string $flightSeatId): string
    {
        return 'TBA';
    }

    /**
     * Get e-ticket by number
     */
    public function getETicketByNumber(string $eticketNumber): ?ETicket
    {
        return ETicket::where('eticket_number', $eticketNumber)
            ->with(['booking'])
            ->first();
    }
}
