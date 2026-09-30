<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pnr_code' => strtoupper(fake()->unique()->bothify('PNR####')),
            'user_id' => User::factory(),
            'flight_id' => Flight::factory(),
            'base_amount' => fake()->randomFloat(2, 500000, 3000000),
            'discount_amount' => 0,
            'tax_amount' => fake()->randomFloat(2, 50000, 200000),
            'total_price' => fake()->randomFloat(2, 500000, 3000000),
            'passenger_count' => fake()->numberBetween(1, 4),
            'status' => 'pending',
        ];
    }
}
