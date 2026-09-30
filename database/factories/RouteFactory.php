<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'airline_id' => Airline::factory(),
            'origin_airport_id' => Airport::factory(),
            'destination_airport_id' => Airport::factory(),
            'distance_km' => fake()->randomFloat(2, 500, 5000),
            'estimated_duration_minutes' => fake()->numberBetween(60, 300),
            'is_active' => true,
        ];
    }
}
