<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'transaction_id' => 'TRX-'.fake()->unique()->numerify('##########'),
            'payment_method' => 'fake_gateway',
            'amount' => fake()->randomFloat(2, 500000, 3000000),
            'status' => 'pending',
            'token' => fake()->uuid(),
        ];
    }
}
