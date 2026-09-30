<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundRequest;
use App\Models\Booking;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RefundController extends Controller
{
    public function __construct(
        private RefundService $refundService
    ) {}

    public function index(Request $request): Response
    {
        $refunds = $request->user()->refunds()
            ->with(['booking.flight', 'booking.passengers'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Refunds/Index', [
            'refunds' => $refunds,
        ]);
    }

    public function show(Refund $refund): Response
    {
        if ($refund->user_id !== request()->user()->id) {
            abort(403);
        }

        $refund->load(['booking.flight', 'booking.passengers', 'booking.payment', 'user', 'processor']);

        return Inertia::render('Refunds/Show', [
            'refund' => $refund,
        ]);
    }

    public function store(RefundRequest $request): RedirectResponse
    {
        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->refundService->requestRefund($booking, $request->user(), $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()->route('refunds.index')->with('success', 'Refund request berhasil diajukan.');
    }

    public function cancel(Refund $refund): RedirectResponse
    {
        if ($refund->user_id !== request()->user()->id) {
            abort(403);
        }

        try {
            $this->refundService->cancelRefund($refund);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('refunds.index')->with('success', 'Refund request berhasil dibatalkan.');
    }
}
