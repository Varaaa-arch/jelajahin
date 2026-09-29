<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminFlightRequest;
use App\Models\Aircraft;
use App\Models\AircraftSeat;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\FlightSeat;
use App\Models\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminFlightController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['q', 'airline_id', 'status_tab', 'sort', 'date_from', 'date_to', 'occupancy_min']);

        $q = trim((string) $request->input('q', ''));
        $airlineId = $request->input('airline_id');
        $statusTab = $request->input('status_tab', 'semua');
        $sort = $request->input('sort', 'terbaru');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $occupancyMin = (int) $request->input('occupancy_min', 0);

        $query = Flight::query()
            ->with(['route.airline', 'route.originAirport', 'route.destinationAirport'])
            ->withCount([
                'flightSeats as seats_total',
                'flightSeats as seats_booked' => fn ($s) => $s->where('is_available', false),
            ]);

        if ($q !== '') {
            $upper = strtoupper($q);
            $query->where(function ($w) use ($q, $upper) {
                $w->where('flight_number', 'ilike', "%{$q}%")
                    ->orWhereHas('route.airline', fn ($a) => $a
                        ->where('name', 'ilike', "%{$q}%")
                        ->orWhere('code', 'ilike', "%{$upper}%"))
                    ->orWhereHas('route.originAirport', fn ($a) => $a
                        ->where('code', 'ilike', "%{$upper}%")
                        ->orWhere('city', 'ilike', "%{$q}%"))
                    ->orWhereHas('route.destinationAirport', fn ($a) => $a
                        ->where('code', 'ilike', "%{$upper}%")
                        ->orWhere('city', 'ilike', "%{$q}%"));
            });
        }

        if ($airlineId) {
            $query->whereHas('route', fn ($r) => $r->where('airline_id', $airlineId));
        }

        match ($statusTab) {
            'aktif'     => $query->whereIn('status', ['scheduled', 'boarding', 'in_flight']),
            'nonaktif'  => $query->whereIn('status', ['cancelled', 'landed']),
            'terjadwal' => $query->where('status', 'scheduled')->whereDate('departure_date', '>=', now()->toDateString()),
            default     => null,
        };

        if ($dateFrom) {
            $query->whereDate('departure_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('departure_date', '<=', $dateTo);
        }

        match ($sort) {
            'terlama'           => $query->orderBy('departure_date')->orderBy('departure_time'),
            'harga_tertinggi'   => $query->orderByDesc('base_price'),
            'harga_terendah'    => $query->orderBy('base_price'),
            'okupansi'          => $query->orderByDesc('seats_available'),
            default             => $query->orderByDesc('departure_date')->orderByDesc('departure_time'),
        };

        $paginator = $query->paginate(20)->withQueryString();

        $flights = collect($paginator->items())->map(function (Flight $f) {
            $total = (int) ($f->seats_total ?? 0);
            $booked = (int) ($f->seats_booked ?? 0);
            // Fallback bila flight_seats belum di-generate: pakai kolom seats_available
            if ($total === 0) {
                $available = (int) ($f->seats_available ?? 0);
                $total = max($available, $booked, 0);
            }
            $available = max($total - $booked, 0);
            $pct = $total > 0 ? (int) round($booked / $total * 100) : 0;

            $airline = $f->route?->airline;
            $origin = $f->route?->originAirport;
            $dest = $f->route?->destinationAirport;

            return [
                'id' => $f->id,
                'flight_number' => $f->flight_number,
                'airline_id' => $airline?->id,
                'airline_code' => $airline?->code ?? '-',
                'airline_name' => $airline?->name ?? '-',
                'origin_code' => $origin?->code ?? '-',
                'origin_city' => $origin?->city ?? '',
                'destination_code' => $dest?->code ?? '-',
                'destination_city' => $dest?->city ?? '',
                'route_id' => $f->route_id,
                'aircraft_id' => $f->aircraft_id,
                'departure_date' => $f->departure_date?->format('Y-m-d'),
                'departure_time' => substr((string) $f->departure_time, 0, 5),
                'arrival_time' => substr((string) $f->arrival_time, 0, 5),
                'base_price' => (float) $f->base_price,
                'status' => $f->status,
                'seats_total' => $total,
                'seats_booked' => $booked,
                'seats_available' => $available,
                'occupancy_pct' => $pct,
            ];
        });

        if ($occupancyMin > 0) {
            $flights = $flights->filter(fn ($f) => $f['occupancy_pct'] >= $occupancyMin)->values();
        }

        // Tetap kirim meta paginator asli agar info total akurat
        $flightsPayload = [
            'data' => $occupancyMin > 0 ? $flights->toArray() : $flights->toArray(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];

        return Inertia::render('Admin/Flights/Index', [
            'flights' => $flightsPayload,
            'filters' => [
                'q' => $q,
                'airline_id' => $airlineId,
                'status_tab' => $statusTab,
                'sort' => $sort,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'occupancy_min' => $occupancyMin,
            ],
            'airlines' => Airline::orderBy('name')->get(['id', 'code', 'name']),
            'airports' => Airport::orderBy('code')->get(['id', 'code', 'city']),
            'routes' => Route::with(['airline:id,code,name', 'originAirport:id,code,city', 'destinationAirport:id,code,city'])
                ->get(['id', 'airline_id', 'origin_airport_id', 'destination_airport_id', 'flight_number_prefix']),
            'aircrafts' => Aircraft::orderBy('registration_number')
                ->get(['id', 'aircraft_type_id', 'airline_id', 'registration_number']),
        ]);
    }

    public function store(AdminFlightRequest $request)
    {
        $data = $request->validated();
        $data['tax_surcharge'] ??= 0;
        $data['fuel_surcharge'] ??= 0;

        $flight = Flight::create($data);
        $this->syncFlightSeats($flight);

        return redirect()->route('admin.flights.index')->with('success', "Penerbangan {$flight->flight_number} berhasil ditambahkan.");
    }

    public function update(AdminFlightRequest $request, Flight $flight)
    {
        $data = $request->validated();
        $flight->update($data);
        $this->syncFlightSeats($flight, true);

        return redirect()->route('admin.flights.index')->with('success', "Penerbangan {$flight->flight_number} berhasil diperbarui.");
    }

    public function destroy(Flight $flight)
    {
        $number = $flight->flight_number;
        $flight->flightSeats()->delete();
        $flight->delete();

        return redirect()->route('admin.flights.index')->with('success', "Penerbangan {$number} berhasil dihapus.");
    }

    /**
     * Generate flight_seats dari layout aircraft_type bila belum ada.
     * $priceOnly=true: hanya sinkron harga kursi yang masih available (saat edit base_price).
     */
    private function syncFlightSeats(Flight $flight, bool $priceOnly = false): void
    {
        $aircraft = Aircraft::find($flight->aircraft_id);
        if (! $aircraft) {
            return;
        }

        $layout = AircraftSeat::where('aircraft_type_id', $aircraft->aircraft_type_id)->get();
        if ($layout->isEmpty()) {
            $flight->update(['seats_available' => $flight->flightSeats()->where('is_available', true)->count()]);
            return;
        }

        if ($priceOnly) {
            FlightSeat::where('flight_id', $flight->id)
                ->where('is_available', true)
                ->update(['current_price' => $flight->base_price]);
            $flight->update(['seats_available' => FlightSeat::where('flight_id', $flight->id)->where('is_available', true)->count()]);
            return;
        }

        $exists = FlightSeat::where('flight_id', $flight->id)->exists();
        if ($exists) {
            return;
        }

        $rows = $layout->map(fn (AircraftSeat $s) => [
            'flight_id' => $flight->id,
            'aircraft_seat_id' => $s->id,
            'current_price' => $flight->base_price,
            'is_available' => true,
        ])->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            FlightSeat::insert($chunk);
        }

        $flight->update(['seats_available' => count($rows)]);
    }
}
