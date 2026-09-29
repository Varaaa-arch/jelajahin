<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\FlightSeat;
use App\Models\Invoice;
use App\Models\Route;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrderController extends Controller
{
    public const ORDER_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled', 'refund_requested', 'refunded'];

    public const PAYMENT_MAP = [
        'paid' => ['success'],
        'unpaid' => ['pending', 'failed', 'expired', 'deny'],
        'refunded' => ['refunded'],
    ];

    /** Transisi status yang diizinkan. */
    public const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled', 'refund_requested'],
        'refund_requested' => ['refunded', 'cancelled', 'confirmed'],
        'completed' => [],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));
        $orderStatus = $request->input('order_status', 'all');
        $paymentStatus = $request->input('payment_status', 'all');
        $airlineId = $request->input('airline_id');
        $routeId = $request->input('route_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sort = $request->input('sort', 'recent');
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;

        $query = Booking::query()
            ->with([
                'user:id,name,email',
                'flight.route.airline',
                'flight.route.originAirport',
                'flight.route.destinationAirport',
                'passengers.flightSeat.aircraftSeat.seatClass',
                'payment' => fn ($p) => $p->orderByDesc('created_at'),
            ]);

        if ($q !== '') {
            $upper = strtoupper($q);
            $query->where(function ($w) use ($q, $upper) {
                $w->where('pnr_code', 'ilike', "%{$upper}%")
                    ->orWhere('special_requests', 'ilike', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('name', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%"))
                    ->orWhereHas('passengers', fn ($p) => $p
                        ->where('first_name', 'ilike', "%{$q}%")
                        ->orWhere('last_name', 'ilike', "%{$q}%"));
            });
        }

        if ($orderStatus !== 'all' && in_array($orderStatus, self::ORDER_STATUSES, true)) {
            $query->where('status', $orderStatus);
        }

        if ($paymentStatus !== 'all') {
            $group = self::PAYMENT_MAP[$paymentStatus] ?? [$paymentStatus];
            $query->whereHas('payment', fn ($p) => $p->whereIn('status', $group));
        }

        if ($airlineId) {
            $query->whereHas('flight.route', fn ($r) => $r->where('airline_id', $airlineId));
        }

        if ($routeId) {
            $query->whereHas('flight', fn ($f) => $f->where('route_id', $routeId));
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        match ($sort) {
            'oldest' => $query->orderBy('bookings.created_at'),
            'amount_desc' => $query->orderByDesc('total_price'),
            'amount_asc' => $query->orderBy('total_price'),
            'flight_date' => $query->orderBy(
                Flight::select('departure_date')->whereColumn('flights.id', 'bookings.flight_id')->limit(1)
            )->orderByDesc('bookings.created_at'),
            default => $query->orderByDesc('bookings.created_at'),
        };

        $query->select('bookings.*');

        $paginator = $query->paginate($perPage)->withQueryString();

        $data = collect($paginator->items())->map(fn (Booking $b) => $this->toArray($b));

        $routes = Route::with([
            'airline:id,code,name',
            'originAirport:id,code,city',
            'destinationAirport:id,code,city',
        ])->orderBy('flight_number_prefix')->get()->map(fn (Route $r) => [
            'id' => $r->id,
            'label' => ($r->originAirport?->code ?? '?').' → '.($r->destinationAirport?->code ?? '?').' · '.($r->airline?->code ?? ''),
            'origin_code' => $r->originAirport?->code,
            'destination_code' => $r->destinationAirport?->code,
            'airline_id' => $r->airline_id,
            'airline_name' => $r->airline?->name,
        ]);

        $rescheduleFlights = Flight::with(['route.originAirport:id,code', 'route.destinationAirport:id,code', 'route.airline:id,code,name'])
            ->whereDate('departure_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'landed'])
            ->orderBy('departure_date')->orderBy('departure_time')
            ->limit(120)
            ->get()
            ->map(fn (Flight $f) => [
                'id' => $f->id,
                'flight_number' => $f->flight_number,
                'route_id' => $f->route_id,
                'origin_code' => $f->route?->originAirport?->code,
                'destination_code' => $f->route?->destinationAirport?->code,
                'airline_name' => $f->route?->airline?->name,
                'departure_date' => $f->departure_date?->format('Y-m-d'),
                'departure_time' => substr((string) $f->departure_time, 0, 5),
                'seats_available' => (int) ($f->seats_available ?? 0),
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => [
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
                'order_status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'airline_id' => $airlineId,
                'route_id' => $routeId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
            'airlines' => Airline::orderBy('name')->get(['id', 'code', 'name']),
            'routes' => $routes,
            'rescheduleFlights' => $rescheduleFlights,
        ]);
    }

    /** Ubah status pesanan (confirm / complete / cancel / refund flow). */
    public function updateStatus(Request $request, Booking $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,refund_requested,refunded',
        ]);

        $from = $order->status;
        $to = $data['status'];

        if ($from === $to) {
            return back()->with('success', "Pesanan {$order->pnr_code} sudah berstatus {$to}.");
        }

        $allowed = self::TRANSITIONS[$from] ?? [];
        if (! in_array($to, $allowed, true)) {
            return back()->withErrors(['status' => "Transisi {$from} → {$to} tidak diizinkan."]);
        }

        DB::transaction(function () use ($order, $to) {
            $order->update(['status' => $to]);

            if (in_array($to, ['cancelled', 'refunded'], true)) {
                $this->releaseSeats($order);
                $latest = $order->payment()->orderByDesc('created_at')->first();
                if ($latest && $to === 'refunded') {
                    $latest->update(['status' => 'refunded']);
                }
            }
        });

        return redirect()->route('admin.orders.index')
            ->with('success', "Pesanan {$order->pnr_code} diubah menjadi {$to}.");
    }

    /** Modify: add-ons + special requests (tanpa ganti flight / jumlah penumpang). */
    public function update(Request $request, Booking $order)
    {
        $data = $request->validate([
            'special_requests' => 'nullable|string|max:1000',
            'addons.baggage' => 'nullable|string|max:50',
            'addons.insurance' => 'nullable|string|max:50',
            'addons.meals' => 'nullable|array',
            'addons.meals.*' => 'string|max:50',
        ]);

        $addons = $order->addons ?? [];
        if (array_key_exists('addons', $data) && is_array($data['addons'])) {
            $addons = array_merge(is_array($addons) ? $addons : [], array_filter($data['addons'], fn ($v) => $v !== null));
        }

        $order->update([
            'special_requests' => $data['special_requests'] ?? $order->special_requests,
            'addons' => $addons,
        ]);

        return redirect()->route('admin.orders.index')
            ->with('success', "Pesanan {$order->pnr_code} berhasil diperbarui.");
    }

    /** Reschedule: pindah ke flight lain dalam rute yang sama. */
    public function reschedule(Request $request, Booking $order)
    {
        $data = $request->validate([
            'new_flight_id' => 'required|uuid|exists:flights,id',
        ]);

        if (in_array($order->status, ['cancelled', 'refunded', 'completed'], true)) {
            return back()->withErrors(['new_flight_id' => 'Pesanan yang sudah selesai/batal tidak bisa di-reschedule.']);
        }

        $newFlight = Flight::with('route')->findOrFail($data['new_flight_id']);
        $oldFlight = $order->flight;

        if ($newFlight->id === $order->flight_id) {
            return back()->withErrors(['new_flight_id' => 'Penerbangan baru sama dengan yang sekarang.']);
        }
        if ($oldFlight && $newFlight->route_id !== $oldFlight->route_id) {
            return back()->withErrors(['new_flight_id' => 'Reschedule hanya untuk rute yang sama.']);
        }

        $pax = max((int) $order->passenger_count, $order->passengers()->count(), 1);

        // Cek dulu di luar transaksi agar kekurangan kursi kembali sebagai
        // validation error (terbaca modal Inertia), bukan halaman error 422.
        $availableCount = FlightSeat::where('flight_id', $newFlight->id)
            ->where('is_available', true)
            ->count();
        if ($availableCount < $pax) {
            throw ValidationException::withMessages([
                'new_flight_id' => "Kursi tersedia tidak cukup ({$availableCount}/{$pax}).",
            ]);
        }

        DB::transaction(function () use ($order, $oldFlight, $newFlight, $pax) {
            $seats = FlightSeat::where('flight_id', $newFlight->id)
                ->where('is_available', true)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->limit($pax)
                ->get();

            // Pengaman balapan (race): slot habis di antara cek awal & lock.
            if ($seats->count() < $pax) {
                throw ValidationException::withMessages([
                    'new_flight_id' => "Kursi tersedia tidak cukup ({$seats->count()}/{$pax}).",
                ]);
            }

            $this->releaseSeats($order);

            $order->update(['flight_id' => $newFlight->id]);

            $passengers = $order->passengers()->orderBy('created_at')->get();
            foreach ($passengers as $i => $passenger) {
                $seat = $seats[$i] ?? null;
                if (! $seat) {
                    continue;
                }
                $seat->update(['is_available' => false, 'booking_id' => $order->id]);
                $passenger->update(['flight_seat_id' => $seat->id]);
            }

            foreach ([$oldFlight?->id, $newFlight->id] as $fid) {
                if (! $fid) {
                    continue;
                }
                $available = FlightSeat::where('flight_id', $fid)->where('is_available', true)->count();
                Flight::where('id', $fid)->update(['seats_available' => $available]);
            }
        });

        return redirect()->route('admin.orders.index')
            ->with('success', "Pesanan {$order->pnr_code} dipindah ke {$newFlight->flight_number}.");
    }

    /** Unduh receipt (e-ticket / invoice) untuk admin — tanpa owner check. */
    public function receipt(Request $request, Booking $order)
    {
        $doc = $request->query('doc', 'eticket');
        if (! in_array($doc, ['eticket', 'invoice'], true)) {
            abort(404, 'Jenis dokumen tidak dikenal.');
        }

        $order->load(['passengers', 'flight.route.originAirport', 'flight.route.destinationAirport', 'flight.route.airline']);

        $flight = $order->flight;
        $route = $flight?->route;
        $passengers = $order->passengers->map(function ($p, $idx) {
            $seat = '-';
            try {
                $seat = $p->flightSeat?->aircraftSeat?->seat_number ?? 'TBA';
            } catch (\Throwable) {
                $seat = 'TBA';
            }

            return [
                'no' => $idx + 1,
                'name' => trim("{$p->title} {$p->first_name} {$p->last_name}"),
                'seat' => $seat,
                'identity' => $p->identity_number ?? '-',
            ];
        })->toArray();

        $ctx = [
            'pnr_code' => $order->pnr_code,
            'airline' => $route?->airline?->name ?? 'Jelajahin Airlines',
            'flight_number' => $flight?->flight_number ?? '-',
            'origin_code' => $route?->originAirport?->code ?? '-',
            'origin_city' => $route?->originAirport?->city ?? '',
            'destination_code' => $route?->destinationAirport?->code ?? '-',
            'destination_city' => $route?->destinationAirport?->city ?? '',
            'departure_date' => $flight?->departure_date?->format('d M Y') ?? '-',
            'departure_time' => $flight ? substr((string) $flight->departure_time, 0, 5) : '-',
            'arrival_time' => $flight ? substr((string) $flight->arrival_time, 0, 5) : '-',
            'booking_date' => $order->created_at?->format('d M Y') ?? '-',
            'passengers' => $passengers,
            'passenger_count' => count($passengers),
            'base_amount' => (float) $order->base_amount,
            'tax_amount' => (float) $order->tax_amount,
            'discount_amount' => (float) $order->discount_amount,
            'addons_amount' => (float) ($order->addons_amount ?? 0),
            'total_amount' => (float) $order->total_price,
            'status' => $order->status,
        ];

        if ($doc === 'invoice') {
            $invoice = $this->ensureInvoice($order);
            $ctx = array_merge($ctx, [
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->issued_date?->format('d M Y') ?? now()->format('d M Y'),
                'invoice_status' => $invoice->status,
            ]);
            $pdf = Pdf::loadView('documents.invoice', $ctx)->setPaper('a4', 'portrait');

            return $pdf->download("invoice-{$order->pnr_code}.pdf");
        }

        $pdf = Pdf::loadView('documents.ticket', $ctx)->setPaper('a4', 'portrait');

        return $pdf->download("etiket-{$order->pnr_code}.pdf");
    }

    private function toArray(Booking $b): array
    {
        $flight = $b->flight;
        $route = $flight?->route;
        $latestPayment = $b->payment?->first();

        $passengers = $b->passengers->map(function ($p) {
            $seat = $p->flightSeat?->aircraftSeat;

            return [
                'id' => $p->id,
                'name' => trim("{$p->title} {$p->first_name} {$p->last_name}"),
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'seat_number' => $seat?->seat_number,
                'seat_class' => $seat?->seatClass?->display_name,
            ];
        })->toArray();

        $addons = is_array($b->addons) ? $b->addons : [];

        return [
            'id' => $b->id,
            'pnr' => $b->pnr_code,
            'booker_name' => $b->user?->name ?? ($passengers[0]['name'] ?? '-'),
            'booker_email' => $b->user?->email ?? '-',
            'passenger_count' => (int) ($b->passenger_count ?? count($passengers)),
            'passengers' => $passengers,
            'route_id' => $flight?->route_id,
            'origin_code' => $route?->originAirport?->code ?? '-',
            'destination_code' => $route?->destinationAirport?->code ?? '-',
            'flight_id' => $b->flight_id,
            'flight_number' => $flight?->flight_number ?? '-',
            'airline_code' => $route?->airline?->code ?? '-',
            'airline_name' => $route?->airline?->name ?? '-',
            'departure_date' => $flight?->departure_date?->format('Y-m-d'),
            'departure_time' => $flight ? substr((string) $flight->departure_time, 0, 5) : null,
            'arrival_time' => $flight ? substr((string) $flight->arrival_time, 0, 5) : null,
            'booking_date' => $b->created_at?->format('Y-m-d'),
            'total_price' => (float) $b->total_price,
            'payment_status' => $latestPayment?->status ?? 'pending',
            'payment_method' => $latestPayment?->payment_method,
            'order_status' => $b->status,
            'special_requests' => $b->special_requests,
            'addons_baggage' => $addons['baggage'] ?? null,
            'addons_insurance' => $addons['insurance'] ?? null,
            'addons_meals' => $addons['meals'] ?? [],
        ];
    }

    /** Kembalikan kursi penumpang ke available (dipakai saat cancel/refund/reschedule). */
    private function releaseSeats(Booking $order): void
    {
        $seatIds = $order->passengers()->whereNotNull('flight_seat_id')->pluck('flight_seat_id');
        if ($seatIds->isNotEmpty()) {
            FlightSeat::whereIn('id', $seatIds)->update(['is_available' => true, 'booking_id' => null]);
        }
        FlightSeat::where('booking_id', $order->id)->update(['is_available' => true, 'booking_id' => null]);

        if ($order->flight_id) {
            $available = FlightSeat::where('flight_id', $order->flight_id)->where('is_available', true)->count();
            if ($available > 0 || FlightSeat::where('flight_id', $order->flight_id)->exists()) {
                Flight::where('id', $order->flight_id)->update(['seats_available' => $available]);
            }
        }
    }

    private function ensureInvoice(Booking $order): Invoice
    {
        $existing = Invoice::where('booking_id', $order->id)->first();
        if ($existing) {
            return $existing->fresh();
        }

        return Invoice::create([
            'booking_id' => $order->id,
            'invoice_number' => $this->nextInvoiceNumber(),
            'issued_date' => now()->toDateString(),
            'subtotal' => $order->base_amount,
            'tax_amount' => $order->tax_amount,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_price,
            'status' => $order->status === 'confirmed' ? 'paid' : 'issued',
            'invoice_url' => null,
        ]);
    }

    private function nextInvoiceNumber(): string
    {
        $last = Invoice::whereYear('issued_date', now()->year)
            ->whereMonth('issued_date', now()->month)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($last) {
            $parts = explode('/', (string) $last->invoice_number);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('INV/%d/%s/%06d', now()->year, str_pad((string) now()->month, 2, '0', STR_PAD_LEFT), $sequence);
    }
}
