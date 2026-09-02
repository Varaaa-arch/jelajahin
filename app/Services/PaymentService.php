<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Initiate payment - generate fake payment token
     */
    public function initiatePayment(Booking $booking): array
    {
        // Check if payment already exists (pending or success)
        $existingPayment = Payment::where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'success'])
            ->first();

        if ($existingPayment && $existingPayment->status === 'success') {
            return [
                'success' => true,
                'transaction_id' => $existingPayment->transaction_id,
                'token' => $existingPayment->token,
                'amount' => $existingPayment->amount,
                'status' => 'success',
                'redirect_url' => "http://localhost:8001/payment/{$existingPayment->token}",
                'expires_at' => $existingPayment->expires_at->toIso8601String(),
            ];
        }

        // Generate unique token
        $token = $this->generatePaymentToken();
        $transactionId = $this->generateTransactionId();

        // Create payment record with PENDING status
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => $transactionId,
            'payment_method' => 'fake_gateway',
            'amount' => $booking->total_price,
            'status' => 'pending',  // IMPORTANT: Start with pending
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'token' => $token,
            'amount' => $booking->total_price,
            'status' => 'pending',
            'redirect_url' => "http://localhost:8001/payment/{$token}",
            'expires_at' => $payment->expires_at->toIso8601String(),
        ];
    }

    /**
     * Process payment - simulate auto-success
     */
    public function processPayment(string $token): array
    {
        $payment = Payment::where('token', $token)->first();

        if (!$payment) {
            throw new \Exception("Payment not found");
        }

        if ($payment->status !== 'pending') {
            throw new \Exception("Payment already processed with status: {$payment->status}");
        }

        if ($payment->expires_at && $payment->expires_at < now()) {
            $this->updatePaymentStatus($payment, 'expired');
            $this->updateBookingStatus($payment->booking, 'payment_expired');
            throw new \Exception("Payment token expired");
        }

        $isSuccess = true;

        if ($isSuccess) {
            $this->updatePaymentStatus($payment, 'success');
            $this->updateBookingStatus($payment->booking, 'confirmed');

            return [
                'success' => true,
                'message' => 'Payment successful',
                'transaction_id' => $payment->transaction_id,
                'status' => 'success',
                'pnr_code' => $payment->booking->pnr_code,
                'paid_at' => $payment->fresh()->paid_at?->toIso8601String(),
            ];
        } else {
            $this->updatePaymentStatus($payment, 'failed');
            $this->updateBookingStatus($payment->booking, 'payment_failed');

            return [
                'success' => false,
                'message' => 'Payment failed',
                'transaction_id' => $payment->transaction_id,
                'status' => 'failed',
            ];
        }
    }

    /**
     * Webhook callback - handle payment confirmation
     */
    public function handleWebhookCallback(array $data): array
    {
        $transactionId = $data['transaction_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$transactionId || !$status) {
            throw new \Exception("Invalid webhook data");
        }

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            throw new \Exception("Payment not found");
        }

        $mappedStatus = $this->mapWebhookStatus($status);

        $this->updatePaymentStatus($payment, $mappedStatus);

        if ($mappedStatus === 'success') {
            $this->updateBookingStatus($payment->booking, 'confirmed');
        } elseif (in_array($mappedStatus, ['expired', 'deny', 'failed'])) {
            $this->updateBookingStatus($payment->booking, 'payment_failed');
        }

        return [
            'success' => true,
            'message' => 'Webhook processed',
            'transaction_id' => $transactionId,
            'status' => $mappedStatus,
        ];
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            throw new \Exception("Payment not found");
        }

        return [
            'transaction_id' => $payment->transaction_id,
            'booking_id' => $payment->booking_id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'created_at' => $payment->created_at->toIso8601String(),
            'booking_status' => $payment->booking->status,
        ];
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus(Payment $payment, string $status): void
    {
        $updateData = ['status' => $status];

        if ($status === 'success') {
            $updateData['paid_at'] = now();
        }

        $payment->update($updateData);
    }

    /**
     * Update booking status
     */
    private function updateBookingStatus(Booking $booking, string $status): void
    {
        $validStatuses = [
            'pending',
            'confirmed',
            'payment_failed',
            'payment_expired',
            'cancelled',
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid booking status: {$status}");
        }

        $booking->update(['status' => $status]);
    }

    /**
     * Map webhook status
     */
    private function mapWebhookStatus(string $webhookStatus): string
    {
        $mapping = [
            'settlement' => 'success',
            'pending' => 'pending',
            'expire' => 'expired',
            'deny' => 'failed',
            'failure' => 'failed',
            'success' => 'success',
            'failed' => 'failed',
            'expired' => 'expired',
        ];

        return $mapping[$webhookStatus] ?? 'pending';
    }

    /**
     * Generate unique payment token
     */
    private function generatePaymentToken(): string
    {
        do {
            $token = 'FAKE_TOKEN_' . strtoupper(Str::random(16));
        } while (Payment::where('token', $token)->exists());

        return $token;
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId(): string
    {
        do {
            $id = 'TXN_' . time() . '_' . Str::random(8);
        } while (Payment::where('transaction_id', $id)->exists());

        return $id;
    }
}
