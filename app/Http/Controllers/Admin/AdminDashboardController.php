<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Payment;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->stats(),
            'revenueTrend' => $this->revenueTrend(),
            'bookingsByStatus' => $this->bookingsByStatus(),
            'topFlights' => $this->topFlights(),
            'recentTransactions' => $this->recentTransactions(),
            'alerts' => $this->alerts(),
        ]);
    }

    private function stats(): array
    {
        $revenue = (float) Payment::where('status', 'success')->sum('amount');
        $revThis = (float) Payment::where('status', 'success')
            ->where('paid_at', '>=', now()->startOfMonth())->sum('amount');
        $revLast = (float) Payment::where('status', 'success')
            ->where('paid_at', '>=', now()->subMonth()->startOfMonth())
            ->where('paid_at', '<', now()->startOfMonth())->sum('amount');

        $bookings = Booking::count();
        $bookThis = Booking::where('created_at', '>=', now()->startOfMonth())->count();
        $bookLast = Booking::where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<', now()->startOfMonth())->count();

        $pending = Booking::where('status', 'pending')->count();
        $occupancy = $this->averageOccupancy();
        $activeUsers = User::where('status', 'active')->count();

        return [
            [
                'key' => 'revenue',
                'label' => 'Total Revenue',
                'value' => self::compactIDR($revenue),
                'trend' => $this->growthText($revThis, $revLast, 'from last month'),
                'trendTone' => 'up',
                'icon' => 'Banknote',
            ],
            [
                'key' => 'bookings',
                'label' => 'Total Bookings',
                'value' => number_format($bookings),
                'trend' => $this->growthText($bookThis, $bookLast, 'from last month'),
                'trendTone' => 'up',
                'icon' => 'Ticket',
            ],
            [
                'key' => 'pending',
                'label' => 'Pending Orders',
                'value' => (string) $pending,
                'trend' => $pending > 0 ? 'Awaiting payment' : 'All clear',
                'trendTone' => $pending > 0 ? 'warn' : 'up',
                'icon' => 'Hourglass',
            ],
            [
                'key' => 'occupancy',
                'label' => 'Avg Occupancy',
                'value' => $occupancy === null ? '-' : $occupancy.'%',
                'trend' => $occupancy === null ? 'No seat data yet' : 'Fleet utilization',
                'trendTone' => 'neutral',
                'icon' => 'Armchair',
            ],
            [
                'key' => 'users',
                'label' => 'Active Users',
                'value' => number_format($activeUsers),
                'trend' => 'Registered & active',
                'trendTone' => 'neutral',
                'icon' => 'UsersRound',
            ],
        ];
    }

    private function revenueTrend(): array
    {
        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $sum = (float) Payment::where('status', 'success')
                ->where('paid_at', '>=', $start)
                ->where('paid_at', '<=', $end)
                ->sum('amount');
            $labels[] = $start->format('d M');
            $values[] = round($sum / 1000000, 1);
        }

        return ['labels' => $labels, 'values' => $values, 'unit' => 'M'];
    }

    private function bookingsByStatus(): array
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
            ->groupBy('status')->pluck('total', 'status');
        $total = max((int) $counts->sum(), 1);

        return $counts->map(fn ($c, $status) => [
            'label' => ucfirst(str_replace('_', ' ', $status)),
            'value' => (int) round($c / $total * 100),
            'color' => $colors[$status] ?? '#9CA3AF',
        ])->values()->toArray();
    }

    private function topFlights(): array
    {
        $rows = Booking::selectRaw('flight_id, count(*) as total')
            ->groupBy('flight_id')->orderByDesc('total')->limit(5)->get();

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

    private function recentTransactions(): array
    {
        return Payment::with(['booking.user:id,name', 'booking.passengers'])
            ->orderByDesc('created_at')->limit(5)->get()
            ->map(function (Payment $p) {
                $passenger = $p->booking?->user?->name
                    ?? $p->booking?->passengers?->first()?->first_name.' '.$p->booking?->passengers?->first()?->last_name;
                $passenger = trim((string) $passenger) !== '' ? trim((string) $passenger) : '-';

                return [
                    'id' => $p->transaction_id,
                    'pnr' => $p->booking?->pnr_code ?? '-',
                    'passenger' => $passenger,
                    'amount' => self::compactIDR((float) $p->amount),
                ];
            })->toArray();
    }

    private function alerts(): array
    {
        $alerts = [];

        $low = $this->lowOccupancyFlights();
        if ($low > 0) {
            $alerts[] = [
                'key' => 'capacity',
                'title' => 'Capacity Warning',
                'message' => "{$low} upcoming flight(s) have occupancy below 40%.",
                'tone' => 'amber',
                'icon' => 'TriangleAlert',
            ];
        }

        $failed = Payment::whereIn('status', ['failed', 'deny'])
            ->where('created_at', '>=', now()->subDays(7))->count();
        if ($failed > 0) {
            $alerts[] = [
                'key' => 'gateway',
                'title' => 'Payment Failures',
                'message' => "{$failed} payment failure(s) in the last 7 days.",
                'tone' => 'red',
                'icon' => 'CircleAlert',
            ];
        }

        $refunds = Booking::where('status', 'refund_requested')->count();
        if ($refunds > 0) {
            $alerts[] = [
                'key' => 'refunds',
                'title' => 'Refund Requests',
                'message' => "{$refunds} refund request(s) awaiting action.",
                'tone' => 'blue',
                'icon' => 'Info',
            ];
        }

        return $alerts;
    }

    private function averageOccupancy(): ?int
    {
        $pcts = $this->occupancyPcts();
        if ($pcts === []) {
            return null;
        }

        return (int) round(array_sum($pcts) / count($pcts));
    }

    private function lowOccupancyFlights(): int
    {
        return count(array_filter(
            $this->occupancyPcts(true),
            fn ($pct) => $pct < 40,
        ));
    }

    /**
     * @return int[]
     */
    private function occupancyPcts(bool $upcomingOnly = false): array
    {
        $query = Flight::query()->withCount([
            'flightSeats as seats_total',
            'flightSeats as seats_booked' => fn ($s) => $s->where('is_available', false),
        ]);

        if ($upcomingOnly) {
            $query->whereDate('departure_date', '>=', now()->toDateString());
        }

        $pcts = [];
        foreach ($query->get() as $f) {
            $total = (int) ($f->seats_total ?? 0);
            if ($total === 0) {
                // Fallback kolom seats_available bila flight_seats belum di-generate.
                $available = (int) ($f->seats_available ?? 0);
                $total = max($available, (int) ($f->seats_booked ?? 0), 0);
            }
            if ($total <= 0) {
                continue;
            }
            $pcts[] = (int) round(((int) ($f->seats_booked ?? 0)) / $total * 100);
        }

        return $pcts;
    }

    private function growthText(float $current, float $previous, string $suffix): string
    {
        if ($previous <= 0) {
            return $current > 0 ? 'New this period' : 'No data yet';
        }
        $pct = round(($current - $previous) / $previous * 100, 1);

        return ($pct >= 0 ? '+' : '').$pct.'% '.$suffix;
    }

    public static function compactIDR(float $amount): string
    {
        if ($amount >= 1000000000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1000000000, 1), '0'), '.').'B';
        }
        if ($amount >= 1000000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.').'M';
        }
        if ($amount >= 1000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1000, 1), '0'), '.').'K';
        }

        return 'Rp '.number_format($amount);
    }
}
