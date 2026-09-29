<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentController extends Controller
{
    use ReleasesBookingSeats;

    public const FAILED_GROUP = ['failed', 'expired', 'deny'];

    public const PAYMENT_GROUPS = [
        'paid' => ['success'],
        'pending' => ['pending'],
        'failed' => ['failed', 'expired', 'deny'],
        'refunded' => ['refunded'],
    ];

    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'all');
        $method = $request->input('method', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sort = $request->input('sort', 'recent');
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;
        $reconDate = $request->input('recon_date', now()->toDateString());

        $query = Payment::query()
            ->with([
                'booking.user:id,name,email',
                'booking.flight.route.originAirport',
                'booking.flight.route.destinationAirport',
            ]);

        if ($q !== '') {
            $upper = strtoupper($q);
            $query->where(function ($w) use ($q, $upper) {
                $w->where('transaction_id', 'ilike', "%{$upper}%")
                    ->orWhereHas('booking', fn ($b) => $b
                        ->where('pnr_code', 'ilike', "%{$upper}%")
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'ilike', "%{$q}%")
                            ->orWhere('email', 'ilike', "%{$q}%")));
            });
        }

        if ($status !== 'all') {
            $group = self::PAYMENT_GROUPS[$status] ?? [$status];
            $query->whereIn('status', $group);
        }

        if ($method !== 'all') {
            $query->where('payment_method', $method);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        match ($sort) {
            'oldest' => $query->orderBy('payments.created_at'),
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc' => $query->orderBy('amount'),
            default => $query->orderByDesc('payments.created_at'),
        };

        $query->select('payments.*');

        $paginator = $query->paginate($perPage)->withQueryString();

        $data = collect($paginator->items())->map(fn (Payment $p) => $this->toArray($p));

        return Inertia::render('Admin/Payments/Index', [
            'payments' => [
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
                'method' => $method,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort' => $sort,
                'per_page' => $perPage,
                'recon_date' => $reconDate,
            ],
            'stats' => $this->stats(),
            'methods' => $this->methodSummary(),
            'recon' => $this->reconciliation($reconDate),
        ]);
    }

    /**
     * Aksi admin: pending → expired (Expire) atau success → refunded (Refund manual).
     * Refund ikut mengubah booking menjadi refunded + melepas kursi dalam 1 transaksi.
     * Expire ikut membatalkan booking (cancelled) + melepas kursi.
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => 'required|in:expired,refunded',
        ]);

        $to = $data['status'];
        $booking = $payment->booking;

        if ($to === 'expired') {
            if ($payment->status !== 'pending') {
                return back()->withErrors(['status' => 'Hanya payment pending yang bisa di-expire.']);
            }

            DB::transaction(function () use ($payment, $booking) {
                $payment->update(['status' => 'expired']);
                if ($booking && ! in_array($booking->status, ['cancelled', 'refunded', 'completed'], true)) {
                    $booking->update(['status' => 'cancelled']);
                    $this->releaseSeats($booking);
                }
            });

            return redirect()->route('admin.payments.index')
                ->with('success', "Payment {$payment->transaction_id} di-expire; booking dibatalkan.");
        }

        // refunded
        if ($payment->status !== 'success') {
            return back()->withErrors(['status' => 'Hanya payment success yang bisa di-refund.']);
        }
        if (! $booking || ! in_array($booking->status, ['confirmed', 'refund_requested'], true)) {
            return back()->withErrors(['status' => 'Refund hanya untuk booking confirmed / refund_requested.']);
        }

        DB::transaction(function () use ($payment, $booking) {
            $payment->update(['status' => 'refunded']);
            $booking->update(['status' => 'refunded']);
            $this->releaseSeats($booking);
        });

        return redirect()->route('admin.payments.index')
            ->with('success', "Payment {$payment->transaction_id} di-refund.");
    }

    private function toArray(Payment $p): array
    {
        $booking = $p->booking;

        return [
            'id' => $p->id,
            'transaction_id' => $p->transaction_id,
            'pnr' => $booking?->pnr_code ?? '-',
            'booking_id' => $p->booking_id,
            'booker_name' => $booking?->user?->name ?? '-',
            'booker_email' => $booking?->user?->email ?? '-',
            'origin_code' => $booking?->flight?->route?->originAirport?->code,
            'destination_code' => $booking?->flight?->route?->destinationAirport?->code,
            'amount' => (float) $p->amount,
            'payment_method' => $p->payment_method,
            'status' => $p->status,
            'paid_at' => $p->paid_at?->format('Y-m-d H:i'),
            'expires_at' => $p->expires_at?->format('Y-m-d H:i'),
            'created_at' => $p->created_at?->format('Y-m-d H:i'),
        ];
    }

    private function stats(): array
    {
        $sum = fn (array $statuses) => (float) Payment::whereIn('status', $statuses)->sum('amount');
        $count = fn (array $statuses) => (int) Payment::whereIn('status', $statuses)->count();

        $revenue = $sum(['success']);
        $lastMonth = (float) Payment::where('status', 'success')
            ->where('paid_at', '>=', now()->subMonth()->startOfMonth())
            ->where('paid_at', '<', now()->startOfMonth())
            ->sum('amount');

        return [
            'revenue' => $revenue,
            'revenue_growth_pct' => $lastMonth > 0 ? round(($revenue - $lastMonth) / $lastMonth * 100, 1) : null,
            'pending_count' => $count(['pending']),
            'pending_amount' => $sum(['pending']),
            'failed_count' => $count(self::FAILED_GROUP),
            'failed_amount' => $sum(self::FAILED_GROUP),
            'refunded_count' => $count(['refunded']),
            'refunded_amount' => $sum(['refunded']),
        ];
    }

    private function methodSummary(): array
    {
        return Payment::query()
            ->selectRaw('payment_method, count(*) as total, sum(amount) as amount')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'method' => $r->payment_method,
                'total' => (int) $r->total,
                'amount' => (float) $r->amount,
            ])
            ->toArray();
    }

    private function reconciliation(string $date): array
    {
        $expected = (float) Booking::whereDate('created_at', $date)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total_price');

        $actual = (float) Payment::where('status', 'success')
            ->whereDate('paid_at', $date)
            ->sum('amount');

        return [
            'date' => $date,
            'expected' => $expected,
            'actual' => $actual,
            'discrepancy' => $actual - $expected,
        ];
    }
}
