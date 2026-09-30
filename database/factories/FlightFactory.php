<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    public function definition(): array
    {
        $airline = Airline::factory()->create();
        $origin = Airport::factory()->create();
        $destination = Airport::factory()->create();
        $route = Route::factory()->create([
            'airline_id' => $airline->id,
            'origin_airport_id' => $origin->id,
            'destination_airport_id' => $destination->id,
        ]);

        return [
            'route_id' => $route->id,
            'flight_number' => fake()->unique()->bothify('GA###'),
            'departure_date' => fake()->date('Y-m-d'),
            'departure_time' => fake()->time('H:i:s'),
            'arrival_time' => fake()->time('H:i:s'),
            'base_price' => fake()->randomFloat(2, 500000, 3000000),
            'tax_surcharge' => fake()->randomFloat(2, 50000, 200000),
            'fuel_surcharge' => fake()->randomFloat(2, 30000, 150000),
            'status' => 'scheduled',
            'seats_available' => fake()->numberBetween(50, 200),
        ];
    }
}
