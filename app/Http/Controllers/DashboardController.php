<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $bookings = $request->user()->bookings()
            ->with([
                'flight.route.originAirport',
                'flight.route.destinationAirport',
                'flight.route.airline',
                'payment',
                'invoice',
                'etickets',
                'passengers',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Booking $booking) => $this->serialize($booking))
            ->values();

        $today = now()->startOfDay();

        $upcoming = $bookings->filter(fn (array $b) => in_array($b['status'], ['pending', 'confirmed'])
            && Carbon::parse($b['flight']['departure_date'])->startOfDay()->gte($today))
            ->values();

        $history = $bookings->filter(fn (array $b) => !in_array($b['status'], ['pending', 'confirmed'])
            || Carbon::parse($b['flight']['departure_date'])->startOfDay()->lt($today))
            ->values();

        $summary = [
            'total_bookings' => $bookings->count(),
            'upcoming' => $upcoming->count(),
            'completed' => $history->where('status', 'completed')->count(),
            'total_spent' => (float) $bookings->whereNotIn('status', ['cancelled'])->sum('total_price'),
            'member_since' => $request->user()->created_at?->format('F Y'),
        ];

        return Inertia::render('Dashboard', [
            'summary' => $summary,
            'upcoming' => $upcoming,
            'history' => $history,
        ]);
    }

    private function serialize(Booking $booking): array
    {
        $flight = $booking->flight;
        $route = $flight?->route;
        $origin = $route?->originAirport;
        $destination = $route?->destinationAirport;
        $airline = $route?->airline;
        $payment = $booking->payment->first();

        return [
            'id' => $booking->id,
            'pnr_code' => $booking->pnr_code,
            'status' => $booking->status,
            'total_price' => (float) $booking->total_price,
            'passenger_count' => $booking->passenger_count,
            'created_at' => $booking->created_at?->format('d M Y, H:i'),
            'flight' => [
                'flight_number' => $flight?->flight_number,
                'departure_date' => $flight?->departure_date?->format('Y-m-d'),
                'departure_date_display' => $flight?->departure_date?->format('D, d M Y'),
                'departure_time' => $flight ? substr((string) $flight->departure_time, 0, 5) : null,
                'arrival_time' => $flight ? substr((string) $flight->arrival_time, 0, 5) : null,
                'status' => $flight?->status,
                'airline' => $airline?->name,
                'airline_code' => $airline?->code,
                'origin' => $origin?->city,
                'origin_airport' => $origin?->code,
                'destination' => $destination?->city,
                'destination_airport' => $destination?->code,
            ],
            'payment' => $payment ? [
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
            ] : null,
            'invoice' => $booking->invoice->first()?->invoice_number,
            'eticket_count' => $booking->etickets->count(),
            'etickets' => $booking->etickets->map(fn ($ticket) => [
                'eticket_number' => $ticket->eticket_number,
                'passenger_name' => $ticket->passenger_name,
                'seat_number' => $ticket->seat_number,
            ])->values(),
            'passengers' => $booking->passengers->map(
                fn ($passenger) => $passenger->title . ' ' . $passenger->first_name . ' ' . $passenger->last_name
            )->values(),
        ];
    }
}