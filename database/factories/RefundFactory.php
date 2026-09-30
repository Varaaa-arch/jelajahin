<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefundFactory extends Factory
{
    public function definition(): array
    {
        $booking = Booking::factory()->create();
        $payment = Payment::factory()->create(['booking_id' => $booking->id, 'status' => 'success']);

        return [
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'user_id' => $booking->user_id,
            'refund_number' => 'REF-'.date('Ymd').'-'.strtoupper(fake()->bothify('######')),
            'reason' => fake()->sentence(),
            'refund_type' => 'full',
            'requested_amount' => $booking->total_price,
            'status' => 'pending',
        ];
    }
}
