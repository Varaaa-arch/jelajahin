<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate payment for booking
     * POST /api/payments/initiate
     */
    public function initiatePayment(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'booking_id' => 'required|string',
            ]);

            // Get booking
            $booking = Booking::find($data['booking_id']);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'error' => 'Booking not found',
                ], 404);
            }

            // Initiate payment
            $result = $this->paymentService->initiatePayment($booking);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process payment (simulate payment)
     * POST /api/payments/process
     */
    public function processPayment(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'token' => 'required|string',
            ]);

            $result = $this->paymentService->processPayment($data['token']);

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Webhook callback from payment gateway
     * POST /api/payments/webhook
     */
    public function webhookCallback(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'transaction_id' => 'required|string',
                'status' => 'required|in:success,pending,expired,deny,failed',
            ]);

            $result = $this->paymentService->handleWebhookCallback($data);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get payment status
     * GET /api/payments/{transactionId}
     */
    public function getPaymentStatus(string $transactionId): JsonResponse
    {
        try {
            $result = $this->paymentService->getPaymentStatus($transactionId);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
