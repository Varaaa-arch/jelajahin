<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * GET /api/v1/flights/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'origin'         => 'required|string',
            'destination'    => 'required|string',
            'departure_date' => 'required|date',
        ]);

        $flights = Flight::with(['route.originAirport', 'route.destinationAirport'])
            ->whereHas('route', function ($q) use ($request) {
                $q->whereHas('originAirport', fn($q) => $q->where('code', strtoupper($request->origin)))
                  ->whereHas('destinationAirport', fn($q) => $q->where('code', strtoupper($request->destination)));
            })
            ->whereDate('departure_date', $request->departure_date)
            ->where('status', 'scheduled')
            ->get();

        return response()->json([
            'data' => $flights,
        ]);
    }

    /**
     * GET /api/v1/flights/{id}
     */
    public function show(string $id): JsonResponse
    {
        $flight = Flight::with(['route.originAirport', 'route.destinationAirport'])
            ->findOrFail($id);

        return response()->json([
            'data' => $flight,
        ]);
    }

    /**
     * GET /api/v1/flights/{id}/seats
     */
    public function seats(string $id): JsonResponse
    {
        $flight = Flight::findOrFail($id);

        $seats = $flight->flightSeats()
            ->with('aircraftSeat')
            ->get()
            ->map(fn($seat) => [
                'id'          => $seat->id,
                'seat_number' => $seat->aircraftSeat?->seat_number ?? '-',
                'is_available' => $seat->is_available,
                'current_price' => $seat->current_price,
            ]);

        return response()->json([
            'data' => $seats,
        ]);
    }
}
