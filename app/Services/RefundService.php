<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\FlightSeat;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function isEligibleForRefund(Booking $booking): array
    {
        if ($booking->status !== 'confirmed') {
            return ['eligible' => false, 'reason' => 'Booking tidak dalam status confirmed'];
        }

        $payment = $booking->payment()->where('status', 'success')->latest()->first();
        if (! $payment) {
            return ['eligible' => false, 'reason' => 'Pembayaran belum berhasil'];
        }

        $pendingRefund = $booking->refunds()->where('status', Refund::STATUS_PENDING)->exists();
        if ($pendingRefund) {
            return ['eligible' => false, 'reason' => 'Sudah ada refund request yang pending'];
        }

        $flight = $booking->flight;
        if (! $flight) {
            return ['eligible' => false, 'reason' => 'Flight tidak ditemukan'];
        }

        $departure = \Carbon\Carbon::parse($flight->departure_date->format('Y-m-d').' '.$flight->departure_time);
        if ($departure <= now()->addHours(24)) {
            return ['eligible' => false, 'reason' => 'Refund hanya bisa dilakukan minimal 24 jam sebelum departure'];
        }

        return ['eligible' => true, 'reason' => null];
    }

    public function requestRefund(Booking $booking, User $user, array $data): Refund
    {
        $eligibility = $this->isEligibleForRefund($booking);
        if (! $eligibility['eligible']) {
            throw new \InvalidArgumentException($eligibility['reason']);
        }

        $payment = $booking->payment()->where('status', 'success')->latest()->first();

        $refundType = $data['refund_type'] ?? Refund::TYPE_FULL;
        $requestedAmount = $refundType === Refund::TYPE_FULL
            ? $booking->total_price
            : $data['requested_amount'];

        return DB::transaction(function () use ($booking, $payment, $user, $data, $refundType, $requestedAmount) {
            $refund = Refund::create([
                'booking_id' => $booking->id,
                'payment_id' => $payment?->id,
                'user_id' => $user->id,
                'refund_number' => $this->generateRefundNumber(),
                'reason' => $data['reason'],
                'refund_type' => $refundType,
                'requested_amount' => $requestedAmount,
                'status' => Refund::STATUS_PENDING,
            ]);

            $booking->update(['status' => 'refund_requested']);

            return $refund;
        });
    }

    public function approveRefund(Refund $refund, ?float $approvedAmount = null): Refund
    {
        if (! $refund->isPending()) {
            throw new \InvalidArgumentException('Only pending refunds can be approved');
        }

        return DB::transaction(function () use ($refund, $approvedAmount) {
            $refund->update([
                'status' => Refund::STATUS_APPROVED,
                'approved_amount' => $approvedAmount ?? $refund->requested_amount,
            ]);

            return $refund;
        });
    }

    public function rejectRefund(Refund $refund, string $reason): Refund
    {
        if (! $refund->isPending()) {
            throw new \InvalidArgumentException('Only pending refunds can be rejected');
        }

        return DB::transaction(function () use ($refund, $reason) {
            $refund->update([
                'status' => Refund::STATUS_REJECTED,
                'admin_notes' => $reason,
            ]);

            $booking = $refund->booking;
            if ($booking && $booking->status === 'refund_requested') {
                $booking->update(['status' => 'confirmed']);
            }

            return $refund;
        });
    }

    public function processRefund(Refund $refund, User $processor): Refund
    {
        if (! $refund->isApproved()) {
            throw new \InvalidArgumentException('Only approved refunds can be processed');
        }

        return DB::transaction(function () use ($refund, $processor) {
            $refund->update([
                'status' => Refund::STATUS_PROCESSED,
                'processed_by' => $processor->id,
                'processed_at' => now(),
            ]);

            $payment = $refund->payment;
            if ($payment && $payment->status === 'success') {
                $payment->update(['status' => 'refunded']);
            }

            $booking = $refund->booking;
            if ($booking) {
                $booking->update(['status' => 'refunded']);
                $this->releaseSeats($booking);
            }

            return $refund;
        });
    }

    public function cancelRefund(Refund $refund): Refund
    {
        if (! $refund->isPending()) {
            throw new \InvalidArgumentException('Only pending refunds can be cancelled');
        }

        return DB::transaction(function () use ($refund) {
            $refund->update(['status' => Refund::STATUS_CANCELLED]);

            $booking = $refund->booking;
            if ($booking && $booking->status === 'refund_requested') {
                $booking->update(['status' => 'confirmed']);
            }

            return $refund;
        });
    }

    private function releaseSeats(Booking $booking): void
    {
        $seatIds = $booking->passengers()->whereNotNull('flight_seat_id')->pluck('flight_seat_id');
        if ($seatIds->isNotEmpty()) {
            FlightSeat::whereIn('id', $seatIds)->update(['is_available' => true, 'booking_id' => null]);
        }
        FlightSeat::where('booking_id', $booking->id)->update(['is_available' => true, 'booking_id' => null]);

        if ($booking->flight_id) {
            $available = FlightSeat::where('flight_id', $booking->flight_id)->where('is_available', true)->count();
            if ($available > 0 || FlightSeat::where('flight_id', $booking->flight_id)->exists()) {
                Flight::where('id', $booking->flight_id)->update(['seats_available' => $available]);
            }
        }
    }

    private function generateRefundNumber(): string
    {
        return 'REF-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
    }
}
