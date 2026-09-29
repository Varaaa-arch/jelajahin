<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPassengerController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'q', 'status', 'check_in', 'nationality', 'payment', 'sort', 'date_from', 'date_to', 'per_page',
        ]);

        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'all');
        $checkIn = $request->input('check_in', 'all');
        $nationality = $request->input('nationality', 'all');
        $payment = $request->input('payment', 'all');
        $sort = $request->input('sort', 'recent');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;

        $query = Passenger::query()
            ->with([
                'booking.user:id,name,email',
                'booking.flight.route.originAirport',
                'booking.flight.route.destinationAirport',
                'booking.payment' => fn ($p) => $p->orderByDesc('created_at'),
                'flightSeat.aircraftSeat.seatClass',
            ])
            ->with('booking');

        if ($q !== '') {
            $upper = strtoupper($q);
            $query->where(function ($w) use ($q, $upper) {
                $w->where('first_name', 'ilike', "%{$q}%")
                    ->orWhere('last_name', 'ilike', "%{$q}%")
                    ->orWhere('identity_number', 'ilike', "%{$q}%")
                    ->orWhere('passport_number', 'ilike', "%{$upper}%")
                    ->orWhereHas('booking', fn ($b) => $b
                        ->where('pnr_code', 'ilike', "%{$upper}%")
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'ilike', "%{$q}%")));
            });
        }

        if ($status !== 'all') {
            $query->whereHas('booking', fn ($b) => $b->where('status', $status));
        }

        if ($checkIn !== 'all') {
            $query->where('check_in_status', $checkIn);
        }

        if ($nationality !== 'all') {
            $query->where('nationality', $nationality);
        }

        if ($payment !== 'all') {
            $query->whereHas('booking.payment', fn ($p) => $p->where('status', $payment));
        }

        if ($dateFrom) {
            $query->whereHas('booking.flight', fn ($f) => $f->whereDate('departure_date', '>=', $dateFrom));
        }
        if ($dateTo) {
            $query->whereHas('booking.flight', fn ($f) => $f->whereDate('departure_date', '<=', $dateTo));
        }

        match ($sort) {
            'oldest'      => $query->orderBy('passengers.created_at'),
            'name_az'     => $query->orderBy('first_name')->orderBy('last_name'),
            'flight_date' => $query->orderBy(
                \App\Models\Flight::select('departure_date')
                    ->whereColumn('flights.id', 'bookings.flight_id')
                    ->limit(1)
            ),
            default       => $query->orderByDesc('passengers.created_at'),
        };

        // Hindari join ambigu: pastikan select passengers.* saat orderBy subquery
        $query->select('passengers.*');

        $paginator = $query->paginate($perPage)->withQueryString();

        $data = collect($paginator->items())->map(function (Passenger $p) {
            $booking = $p->booking;
            $flight = $booking?->flight;
            $seat = $p->flightSeat?->aircraftSeat;
            $addons = $booking?->addons ?? [];
            $latestPayment = $booking?->payment?->first();

            return [
                'id' => $p->id,
                'name' => trim("{$p->first_name} {$p->last_name}"),
                'title' => $p->title,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'nationality' => $p->nationality,
                'gender' => $p->gender,
                'date_of_birth' => $p->date_of_birth,
                'email' => $booking?->user?->email ?? '-',
                'booker_name' => $booking?->user?->name ?? '-',
                'identity_type' => $p->identity_type,
                'identity_masked' => self::mask($p->identity_number),
                'identity_number' => $p->identity_number,
                'passport_masked' => self::mask($p->passport_number),
                'passport_number' => $p->passport_number,
                'pnr' => $booking?->pnr_code ?? '-',
                'flight_number' => $flight?->flight_number ?? '-',
                'departure_date' => $flight?->departure_date?->format('Y-m-d'),
                'origin_code' => $flight?->route?->originAirport?->code,
                'destination_code' => $flight?->route?->destinationAirport?->code,
                'booking_status' => $booking?->status ?? 'pending',
                'payment_status' => $latestPayment?->status ?? 'pending',
                'payment_method' => $latestPayment?->payment_method,
                'check_in_status' => $p->check_in_status,
                'seat_number' => $seat?->seat_number,
                'seat_class' => $seat?->seatClass?->display_name,
                'baggage_allowance_kg' => $seat?->seatClass?->baggage_allowance_kg,
                'carry_on_allowance_kg' => $seat?->seatClass?->carry_on_allowance_kg,
                'addons_baggage' => $addons['baggage'] ?? null,
                'addons_insurance' => $addons['insurance'] ?? null,
                'addons_meals' => $addons['meals'] ?? [],
                'special_requests' => $booking?->special_requests,
            ];
        });

        return Inertia::render('Admin/Passengers/Index', [
            'passengers' => [
                'data' => $data->toArray(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => [
                'q' => $q,
                'status' => $status,
                'check_in' => $checkIn,
                'nationality' => $nationality,
                'payment' => $payment,
                'sort' => $sort,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'nationalities' => Passenger::query()
                ->whereNotNull('nationality')
                ->distinct()
                ->orderBy('nationality')
                ->pluck('nationality'),
        ]);
    }

    public function update(Request $request, Passenger $passenger)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:10',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'identity_type' => 'nullable|string|max:50',
            'identity_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'passport_number' => 'nullable|string|max:50',
            'check_in_status' => 'required|in:not_checked_in,checked_in',
        ]);

        $passenger->update($data);

        return redirect()->route('admin.passengers.index')
            ->with('success', "Data penumpang {$passenger->first_name} {$passenger->last_name} berhasil diperbarui.");
    }

    private static function mask(?string $value): string
    {
        $value = (string) ($value ?? '');
        if (strlen($value) <= 5) {
            return $value !== '' ? str_repeat('*', strlen($value)) : '-';
        }

        return substr($value, 0, 3).'*****'.substr($value, -2);
    }
}
