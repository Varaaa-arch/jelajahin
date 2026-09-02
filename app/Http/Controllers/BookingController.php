<?php

namespace App\Http\Controllers;

use App\Repositories\BookingRepository;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService    $bookingService,
        private readonly BookingRepository $bookingRepository,
    ) {}

    /**
     * POST /api/bookings
     *
     * Receive: flight_id, seats (locked di Redis), seat_ids, passengers
     * Validate seat locks dari Redis, buat booking, generate PNR
     * Return: booking data + payment status
     */
    public function createBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'flight_id'                    => 'required|string',
            'user_id'                      => 'nullable|string',
            'seats'                        => 'required|array|min:1',
            'seats.*'                      => 'required|string',
            'seat_ids'                     => 'required|array|min:1',
            'seat_ids.*'                   => 'required|string',
            'promo_code'                   => 'nullable|string',
            'special_requests'             => 'nullable|string',
            'passengers'                   => 'required|array|min:1',
            'passengers.*.title'           => 'required|string',
            'passengers.*.first_name'      => 'required|string|max:100',
            'passengers.*.last_name'       => 'required|string|max:100',
            'passengers.*.date_of_birth'   => 'required|date',
            'passengers.*.gender'          => 'required|in:M,F',
            'passengers.*.identity_type'   => 'required|in:passport,ktp,sim',
            'passengers.*.identity_number' => 'required|string|max:50',
            'passengers.*.nationality'     => 'nullable|string|max:50',
            'passengers.*.passport_number' => 'nullable|string|max:20',
        ]);

        try {
            $booking = $this->bookingService->createBooking($validated);
        } catch (\Exception $e) {
            // Seat lock tidak ditemukan / sudah expired di Redis
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'seat_lock_invalid',
            ], 422);
        }

        $booking->load(['passengers', 'flight']);

        return response()->json([
            'message' => 'Booking created successfully.',
            'booking' => [
                'id'               => $booking->id,
                'pnr_code'         => $booking->pnr_code,
                'flight_id'        => $booking->flight_id,
                'passenger_count'  => $booking->passenger_count,
                'base_amount'      => $booking->base_amount,
                'discount_amount'  => $booking->discount_amount,
                'tax_amount'       => $booking->tax_amount,
                'total_price'      => $booking->total_price,
                'status'           => $booking->status,
                'special_requests' => $booking->special_requests,
                'passengers'       => $booking->passengers,
                'flight'           => $booking->flight,
                'created_at'       => $booking->created_at,
            ],
            'payment' => [
                'status'     => 'pending',
                'expires_at' => now()->addMinutes(15)->toISOString(),
                'amount'     => $booking->total_price,
            ],
        ], 201);
    }

    /**
     * GET /api/bookings/{pnr}
     *
     * Ambil booking berdasarkan PNR code.
     */
    public function getByPNR(string $pnr): JsonResponse
    {
        $booking = $this->bookingRepository->getByPNR(strtoupper($pnr));

        if (! $booking) {
            return response()->json([
                'message' => "Booking with PNR {$pnr} not found.",
            ], 404);
        }

        return response()->json($booking);
    }

    /**
     * GET /api/bookings/user/{userId}
     *
     * Ambil semua booking milik user.
     */
    public function getUserBookings(string $userId): JsonResponse
    {
        $bookings = $this->bookingRepository->getUserBookings($userId);

        return response()->json($bookings);
    }
}
