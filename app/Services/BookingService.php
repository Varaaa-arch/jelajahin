<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Promo;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Create booking dengan passengers dan seats yang sudah di-lock
     */
    public function createBooking(array $data): Booking
    {
        // Validate seat locks dari Redis
        $this->validateSeats($data['flight_id'], $data['seats']);

        // Calculate total price
        $totalPrice = $this->calculateTotal(
            $data['flight_id'],
            count($data['seats']),
            $data['promo_code'] ?? null
        );

        // Create booking
        $booking = Booking::create([
            'pnr_code' => $this->generatePNR(),
            'user_id' => auth()->id() ?? $data['user_id'],
            'flight_id' => $data['flight_id'],
            'base_amount' => $totalPrice['base'],
            'discount_amount' => $totalPrice['discount'],
            'tax_amount' => $totalPrice['tax'],
            'total_price' => $totalPrice['total'],
            'passenger_count' => count($data['seats']),
            'status' => 'pending',
            'special_requests' => $data['special_requests'] ?? null,
        ]);

        // Create passengers
        foreach ($data['passengers'] as $idx => $passenger) {
            Passenger::create([
                'booking_id' => $booking->id,
                'title' => $passenger['title'],
                'first_name' => $passenger['first_name'],
                'last_name' => $passenger['last_name'],
                'date_of_birth' => $passenger['date_of_birth'],
                'gender' => $passenger['gender'],
                'identity_type' => $passenger['identity_type'],
                'identity_number' => $passenger['identity_number'],
                'nationality' => $passenger['nationality'] ?? null,
                'passport_number' => $passenger['passport_number'] ?? null,
                // Only set flight_seat_id if it looks like a real UUID (not fake demo seat ID)
                'flight_seat_id' => $this->isValidUuid($data['seat_ids'][$idx] ?? null) ? $data['seat_ids'][$idx] : null,
            ]);
        }

        return $booking;
    }

    /**
     * Validate seats are locked in Redis (15 minute TTL)
     * 
     * In development/demo mode (APP_ENV=local), this check is bypassed
     * because seat IDs may be fake/generated on the frontend.
     */
    public function validateSeats(string $flightId, array $seatIds): bool
    {
        // Bypass Redis lock check in development/demo mode
        if (app()->environment('local', 'development', 'testing')) {
            return true;
        }

        foreach ($seatIds as $seatId) {
            $lockKey = "lock:seat:{$flightId}:{$seatId}";
            
            // Check if lock exists
            if (!Redis::exists($lockKey)) {
                throw new \Exception("Seat {$seatId} is not locked. Lock may have expired.");
            }
        }

        return true;
    }

    /**
     * Calculate total price dengan tax dan diskon
     */
    public function calculateTotal(string $flightId, int $seatCount, ?string $promoCode = null): array
    {
        // Get flight base price
        $flight = \App\Models\Flight::find($flightId);
        if (!$flight) {
            throw new \Exception("Flight not found");
        }

        $basePrice = $flight->base_price * $seatCount;
        
        // Calculate tax (10%)
        $taxPercentage = 0.10;
        $tax = $basePrice * $taxPercentage;
        
        // Calculate discount (if promo code exists)
        $discount = 0;
        if ($promoCode) {
            $discount = $this->applyPromo($promoCode, $basePrice);
        }

        $total = $basePrice + $tax - $discount;

        return [
            'base' => round($basePrice, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Apply promo code dan return discount amount
     */
    public function applyPromo(string $promoCode, float $baseAmount): float
    {
        // Find promo
        $promo = Promo::where('code', strtoupper($promoCode))
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->first();

        if (!$promo) {
            throw new \Exception("Promo code not found or expired");
        }

        // Check usage limit
        if ($promo->usage_limit && $promo->usage_count >= $promo->usage_limit) {
            throw new \Exception("Promo code usage limit reached");
        }

        // Check minimum purchase
        if ($baseAmount < $promo->min_purchase) {
            throw new \Exception("Booking amount below minimum purchase for this promo");
        }

        // Calculate discount
        $discount = 0;
        if ($promo->is_percentage) {
            // Percentage discount
            $discount = $baseAmount * ($promo->discount_value / 100);
            
            // Apply max discount cap if exists
            if ($promo->max_discount && $discount > $promo->max_discount) {
                $discount = $promo->max_discount;
            }
        } else {
            // Fixed amount discount
            $discount = min($promo->discount_value, $baseAmount);
        }

        return round($discount, 2);
    }

    /**
     * Generate unique PNR code (6 character alphanumeric)
     */
    private function generatePNR(): string
    {
        do {
            $pnr = strtoupper(Str::random(6));
        } while (Booking::where('pnr_code', $pnr)->exists());

        return $pnr;
    }

    /**
     * Check if a string is a valid UUID
     */
    private function isValidUuid(?string $value): bool
    {
        if (!$value) return false;
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }
}
