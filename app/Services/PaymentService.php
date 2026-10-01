<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    private NotificationService $notificationService;

    /** Base URL Go microservice (seat locking). */
    private string $goServiceUrl;

    public function __construct(NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService ?? new NotificationService();
        $this->goServiceUrl = rtrim(config('services.go_seat_service.url', env('GO_SERVICE_URL', 'http://localhost:8080')), '/');
    }

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
            // Token kedaluwarsa: booking kembali/stay pending agar user bisa
            // initiate payment ulang. JANGAN pakai status siluman.
            $this->updateBookingStatus($payment->booking, 'pending');
            throw new \Exception("Payment token expired");
        }

        $isSuccess = true;

        if ($isSuccess) {
            $this->updatePaymentStatus($payment, 'success');
            // Pembayaran sukses tapi booking BELANG konfirmasi: menunggu
            // persetujuan admin sebelum kursi di-booked + e-tiket tergenerate.
            $this->updateBookingStatus($payment->booking, 'awaiting_confirmation');

            // Tahan kursi lebih lama (24 jam) agar tidak direbut orang lain
            // selama menunggu persetujuan admin (lock awal cuma 15 menit).
            $this->extendSeatHold($payment->booking);

            // Alert admin agar ada order baru yang perlu dipersetujui.
            $this->notificationService->notifyAdminsNewBooking($payment->booking->fresh(['user', 'flight']));

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
            // Payment gagal: booking tetap pending agar user bisa coba bayar lagi.
            $this->updateBookingStatus($payment->booking, 'pending');

            return [
                'success' => false,
                'message' => 'Payment failed',
                'transaction_id' => $payment->transaction_id,
                'status' => 'failed',
            ];
        }
    }

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
            // Pembayaran sukses tapi booking BELANG konfirmasi admin.
            $this->updateBookingStatus($payment->booking, 'awaiting_confirmation');

            // Tahan kursi lebih lama selama menunggu persetujuan admin.
            $this->extendSeatHold($payment->booking);

            // Alert admin agar ada order baru yang perlu dipersetujui.
            $this->notificationService->notifyAdminsNewBooking($payment->booking->fresh(['user', 'flight']));
        } elseif (in_array($mappedStatus, ['expired', 'deny', 'failed'])) {
            // Payment gagal/expired: booking tetap pending agar bisa retry.
            // (Dulu pakai status siluman payment_failed -> error CHECK DB.)
            $this->updateBookingStatus($payment->booking, 'pending');
        }

        return [
            'success' => true,
            'message' => 'Webhook processed',
            'transaction_id' => $transactionId,
            'status' => $mappedStatus,
        ];
    }

    public function initiatePayment(Booking $booking): array
    {
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

        $token = $this->generatePaymentToken();
        $transactionId = $this->generateTransactionId();

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => $transactionId,
            'payment_method' => 'fake_gateway',
            'amount' => $booking->total_price,
            'status' => 'pending',
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
     * Finalize booking setelah admin persetujuan (awaiting_confirmation → confirmed).
     *
     * Setelah pembayaran sukses, kursi masih di-lock (Redis) dan dokumen belum
     * tergenerate. Saat admin persetujui di panel, metode ini:
     *   1. Ubah status booking menjadi confirmed.
     *   2. Konfirmasi kursi di Go service (tandai booked secara permanen).
     *   3. Generate e-tiket + invoice (paid) agar muncul di dashboard.
     *   4. Kirim notifikasi "confirmed" + "ticket ready" ke user.
     *
     * Idempoten: kalau booking bukan lagi awaiting_confirmation (misal. sudah
     * confirmed/completed), metode tidak melakukan apa-apa.
     *
     * @param Booking $booking booking yang baru disetujui admin.
     */
    public function approveBooking(Booking $booking): void
    {
        if ($booking->status !== 'awaiting_confirmation') {
            Log::info("PaymentService: booking {$booking->pnr_code} tidak di-awaiting_confirmation, jadi tidak diproces ulang.");
            return;
        }

        $this->updateBookingStatus($booking, 'confirmed');

        // Konfirmasi kursi di Go service (tandai booked di Redis, hapus lock)
        $this->confirmSeatsInGoService($booking);

        // Generate dokumen (e-tiket + invoice) agar muncul di dashboard.
        // Non-blocking: kegagalan generate tidak menggagalkan konfirmasi.
        $this->generateDocuments($booking);

        // Send notifications (user) — kursi sudah terkonfirmasi & tiket siap.
        $this->notificationService->notifyBookingComplete($booking->fresh(['user', 'flight', 'passengers']));
    }

    /**
     * Perpanjang masa tahan kursi di Go service selama menunggu persetujuan
     * admin (default lock cuma 15 menit, approval bisa berjam-jam).
     *
     * Best-effort: gagal extend hanya dicatat di log, tidak menggagalkan payment.
     */
    private function extendSeatHold(Booking $booking): void
    {
        try {
            $seatIds = $booking->passengers()
                ->whereNotNull('flight_seat_id')
                ->pluck('flight_seat_id')
                ->filter()
                ->values()
                ->toArray();

            if (empty($seatIds)) {
                return;
            }

            $response = Http::timeout(5)->post("{$this->goServiceUrl}/api/v1/seats/extend", [
                'flight_id' => $booking->flight_id,
                'seat_ids' => $seatIds,
                'user_id' => (string) $booking->user_id,
                // 24 jam dalam detik — Go pakai ini sebagai TTL baru.
                'ttl_seconds' => 24 * 3600,
            ]);

            if (! $response->successful()) {
                Log::warning('PaymentService: Go service gagal extend seat hold', [
                    'booking_id' => $booking->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('PaymentService: failed to extend seat hold in Go service', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Panggil Go service untuk menandai kursi sebagai booked setelah payment sukses.
     * Mengumpulkan seat_ids dari passengers booking, lalu POST ke /api/v1/seats/confirm.
     *
     * Gagal konfirmasi tidak akan membatalkan payment — hanya dicatat di log
     * agar tidak memblokir alur utama.
     */
    private function confirmSeatsInGoService(Booking $booking): void
    {
        try {
            // Kumpulkan seat IDs yang valid (UUID) dari passengers
            $seatIds = $booking->passengers()
                ->whereNotNull('flight_seat_id')
                ->pluck('flight_seat_id')
                ->filter()
                ->values()
                ->toArray();

            if (empty($seatIds)) {
                Log::info("PaymentService: no seat IDs to confirm for booking {$booking->id}");
                return;
            }

            $payload = [
                'flight_id' => $booking->flight_id,
                'seat_ids'  => $seatIds,
                'user_id'   => (string) $booking->user_id,
            ];

            $response = Http::timeout(5)
                ->post("{$this->goServiceUrl}/api/v1/seats/confirm", $payload);

            if ($response->successful()) {
                Log::info("PaymentService: seats confirmed in Go service", [
                    'booking_id' => $booking->id,
                    'seat_ids'   => $seatIds,
                ]);
            } else {
                Log::warning("PaymentService: Go service returned non-success for seat confirm", [
                    'booking_id' => $booking->id,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            // Jangan lempar exception — payment sudah sukses, log saja
            Log::error("PaymentService: failed to confirm seats in Go service", [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate e-tiket + invoice untuk booking yang baru dibayar.
     *
     * Idempoten (service generate mengecek record yang sudah ada).
     * Gagal generate tidak akan membatalkan payment — hanya dicatat di log.
     */
    private function generateDocuments(Booking $booking): void
    {
        $fresh = $booking->fresh(['passengers', 'flight']);

        try {
            $ok = app(ETicketService::class)->generateAndSendETickets($fresh ?? $booking);
            Log::info("PaymentService: e-tickets generated+sent for booking {$booking->id}", ['ok' => $ok]);
        } catch (\Throwable $e) {
            Log::error("PaymentService: failed to generate/send e-tickets", [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        try {
            $invoiceService = app(InvoiceService::class);
            $ok = $invoiceService->generateAndSendInvoice($fresh ?? $booking);
            // Tandai lunas setelah invoice terkirim (idempoten).
            $invoice = $invoiceService->generateInvoice($booking);
            $invoiceService->markAsPaid($invoice);
            Log::info("PaymentService: invoice generated+sent for booking {$booking->id}", ['ok' => $ok]);
        } catch (\Throwable $e) {
            Log::error("PaymentService: failed to generate/send invoice", [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function updatePaymentStatus(Payment $payment, string $status): void
    {
        $updateData = ['status' => $status];

        if ($status === 'success') {
            $updateData['paid_at'] = now();
        }

        $payment->update($updateData);
    }

    private function updateBookingStatus(Booking $booking, string $status): void
    {
        // Harus sama dengan CHECK/ENUM di DB + ORDER_STATUSES admin.
        $validStatuses = [
            'pending',
            'awaiting_confirmation',
            'confirmed',
            'completed',
            'cancelled',
            'refund_requested',
            'refunded',
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid booking status: {$status}");
        }

        $booking->update(['status' => $status]);
    }

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

    private function generatePaymentToken(): string
    {
        do {
            $token = 'FAKE_TOKEN_' . strtoupper(Str::random(16));
        } while (Payment::where('token', $token)->exists());

        return $token;
    }

    private function generateTransactionId(): string
    {
        do {
            $id = 'TXN_' . time() . '_' . Str::random(8);
        } while (Payment::where('transaction_id', $id)->exists());

        return $id;
    }
}

