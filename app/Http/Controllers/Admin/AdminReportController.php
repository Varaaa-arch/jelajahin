<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminReportController extends Controller
{
    public function index(Request $request): Response
    {
        $dateFrom = $request->input('date_from', now()->subDays(29)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        return Inertia::render('Admin/Reports/Index', [
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
            'summary' => $this->summary($dateFrom, $dateTo),
            'revenueTrend' => $this->revenueTrend($dateFrom, $dateTo),
            'bookingsByStatus' => $this->bookingsByStatus($dateFrom, $dateTo),
            'topRoutes' => $this->topRoutes($dateFrom, $dateTo),
            'topFlights' => $this->topFlights($dateFrom, $dateTo),
            'paymentSummary' => $this->paymentSummary($dateFrom, $dateTo),
            'occupancy' => $this->occupancy($dateFrom, $dateTo),
        ]);
    }

    private function summary(string $from, string $to): array
    {
        $revenue = (float) Payment::where('status', 'success')
            ->whereDate('paid_at', '>=', $from)->whereDate('paid_at', '<=', $to)->sum('amount');
        $bookings = Booking::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->count();
        $refunded = (float) Payment::where('status', 'refunded')
            ->whereDate('updated_at', '>=', $from)->whereDate('updated_at', '<=', $to)->sum('amount');
        $avgTicket = $bookings > 0
            ? (float) Booking::whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->avg('total_price')
            : 0;

        return [
            'revenue' => $revenue,
            'bookings' => $bookings,
            'refunded' => $refunded,
            'avg_ticket' => round($avgTicket, 2),
        ];
    }

    private function revenueTrend(string $from, string $to): array
    {
        $start = \Carbon\Carbon::parse($from);
        $end = \Carbon\Carbon::parse($to);
        $days = min($start->diffInDays($end) + 1, 92);

        // Bucket harian bila <= 45 hari, atau mingguan bila lebih panjang.
        $bucketDays = $days <= 45 ? 1 : 7;

        $labels = [];
        $values = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $bucketEnd = (clone $cursor)->addDays($bucketDays - 1);
            if ($bucketEnd->gt($end)) {
                $bucketEnd = $end->copy();
            }
            $sum = (float) Payment::where('status', 'success')
                ->whereDate('paid_at', '>=', $cursor->toDateString())
                ->whereDate('paid_at', '<=', $bucketEnd->toDateString())
                ->sum('amount');
            $labels[] = $cursor->format('d M').($bucketDays > 1 ? ' – '.$bucketEnd->format('d M') : '');
            $values[] = round($sum / 1000000, 2);
            $cursor = $bucketEnd->addDay();
        }

        return ['labels' => $labels, 'values' => $values, 'unit' => 'M'];
    }

    private function bookingsByStatus(string $from, string $to): array
    {
        $colors = [
            'completed' => '#0d1117',
            'confirmed' => '#0F766E',
            'awaiting_confirmation' => '#B45309',
            'pending' => '#D1D5DB',
            'cancelled' => '#DC2626',
            'refund_requested' => '#F59E0B',
            'refunded' => '#8B5CF6',
        ];

        $counts = Booking::selectRaw('status, count(*) as total')
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->groupBy('status')->pluck('total', 'status');
        $total = max((int) $counts->sum(), 1);

        return $counts->map(fn ($c, $status) => [
            'status' => $status,
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'count' => (int) $c,
            'pct' => (int) round($c / $total * 100),
            'color' => $colors[$status] ?? '#9CA3AF',
        ])->values()->toArray();
    }

    private function topRoutes(string $from, string $to, int $limit = 5): array
    {
        $rows = Booking::selectRaw('flights.route_id as route_id, count(*) as bookings, sum(bookings.total_price) as revenue')
            ->join('flights', 'flights.id', '=', 'bookings.flight_id')
            ->whereDate('bookings.created_at', '>=', $from)
            ->whereDate('bookings.created_at', '<=', $to)
            ->whereNotIn('bookings.status', ['cancelled', 'refunded'])
            ->groupBy('flights.route_id')
            ->orderByDesc('bookings')->limit($limit)->get();

        $routes = \App\Models\Route::with(['airline:id,code', 'originAirport:id,code,city', 'destinationAirport:id,code,city'])
            ->whereIn('id', $rows->pluck('route_id'))->get()->keyBy('id');

        return $rows->map(function ($r) use ($routes) {
            $route = $routes->get($r->route_id);

            return [
                'route' => $route
                    ? ($route->originAirport?->city ?? '?').' → '.($route->destinationAirport?->city ?? '?')
                    : '-',
                'codes' => $route
                    ? ($route->originAirport?->code ?? '?').' → '.($route->destinationAirport?->code ?? '?')
                    : '-',
                'airline' => $route?->airline?->code ?? '-',
                'bookings' => (int) $r->bookings,
                'revenue' => (float) $r->revenue,
            ];
        })->toArray();
    }

    private function topFlights(string $from, string $to, int $limit = 5): array
    {
        $rows = Booking::selectRaw('flight_id, count(*) as total')
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->groupBy('flight_id')->orderByDesc('total')->limit($limit)->get();

        $flights = Flight::with(['route.originAirport:id,city', 'route.destinationAirport:id,city'])
            ->whereIn('id', $rows->pluck('flight_id'))->get()->keyBy('id');

        $out = [];
        foreach ($rows->values() as $i => $row) {
            $f = $flights->get($row->flight_id);
            $out[] = [
                'rank' => $i + 1,
                'route' => $f
                    ? ($f->route?->originAirport?->city ?? '?').' → '.($f->route?->destinationAirport?->city ?? '?')
                    : '-',
                'flightNo' => $f?->flight_number ?? '-',
                'bookings' => (int) $row->total,
            ];
        }

        return $out;
    }

    private function paymentSummary(string $from, string $to): array
    {
        return Payment::selectRaw('status, count(*) as total, sum(amount) as amount')
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->groupBy('status')->get()
            ->map(fn ($r) => [
                'status' => $r->status,
                'total' => (int) $r->total,
                'amount' => (float) $r->amount,
            ])->toArray();
    }

    private function occupancy(string $from, string $to): array
    {
        $flights = Flight::withCount([
            'flightSeats as seats_total',
            'flightSeats as seats_booked' => fn ($s) => $s->where('is_available', false),
        ])->whereDate('departure_date', '>=', $from)
            ->whereDate('departure_date', '<=', $to)
            ->orderBy('departure_date')
            ->limit(50)
            ->get();

        $rows = [];
        foreach ($flights as $f) {
            $total = (int) ($f->seats_total ?? 0);
            if ($total === 0) {
                $available = (int) ($f->seats_available ?? 0);
                $total = max($available, (int) ($f->seats_booked ?? 0), 0);
            }
            if ($total <= 0) {
                continue;
            }
            $rows[] = [
                'flight_number' => $f->flight_number,
                'departure_date' => $f->departure_date?->format('Y-m-d'),
                'booked' => (int) ($f->seats_booked ?? 0),
                'total' => $total,
                'pct' => (int) round(((int) ($f->seats_booked ?? 0)) / $total * 100),
            ];
        }

        usort($rows, fn ($a, $b) => $b['pct'] <=> $a['pct']);

        $avg = $rows === [] ? null : (int) round(array_sum(array_column($rows, 'pct')) / count($rows));

        return ['average_pct' => $avg, 'flights' => array_slice($rows, 0, 10)];
    }
}
