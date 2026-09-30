<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRefundController extends Controller
{
    public function __construct(
        private RefundService $refundService
    ) {}

    public function index(Request $request): Response
    {
        $refunds = Refund::with(['booking.flight', 'booking.passengers', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Refunds/Index', [
            'refunds' => $refunds,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Refund $refund): Response
    {
        $refund->load(['booking.flight', 'booking.passengers', 'booking.payment', 'user', 'processor']);

        return Inertia::render('Admin/Refunds/Show', [
            'refund' => $refund,
        ]);
    }

    public function approve(Request $request, Refund $refund): RedirectResponse
    {
        $request->validate([
            'approved_amount' => ['nullable', 'numeric', 'min:0', 'max:'.$refund->requested_amount],
        ]);

        try {
            $this->refundService->approveRefund($refund, $request->approved_amount);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Refund berhasil disetujui.');
    }

    public function reject(Request $request, Refund $refund): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->refundService->rejectRefund($refund, $request->admin_notes);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Refund berhasil ditolak.');
    }

    public function process(Refund $refund): RedirectResponse
    {
        try {
            $this->refundService->processRefund($refund, request()->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Refund berhasil diproses.');
    }
}
