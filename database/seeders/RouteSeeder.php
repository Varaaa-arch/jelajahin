<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            ['airline' => 'GA', 'origin' => 'CGK', 'destination' => 'DPS', 'prefix' => 'GA', 'distance_km' => 980, 'duration' => 110],
            ['airline' => 'GA', 'origin' => 'DPS', 'destination' => 'CGK', 'prefix' => 'GA', 'distance_km' => 980, 'duration' => 105],
            ['airline' => 'JT', 'origin' => 'CGK', 'destination' => 'SUB', 'prefix' => 'JT', 'distance_km' => 690, 'duration' => 85],
            ['airline' => 'JT', 'origin' => 'SUB', 'destination' => 'DPS', 'prefix' => 'JT', 'distance_km' => 310, 'duration' => 55],
            ['airline' => 'ID', 'origin' => 'CGK', 'destination' => 'KNO', 'prefix' => 'ID', 'distance_km' => 1390, 'duration' => 150],
            ['airline' => 'QG', 'origin' => 'CGK', 'destination' => 'UPG', 'prefix' => 'QG', 'distance_km' => 1390, 'duration' => 160],
        ];

        foreach ($routes as $route) {
            $airline = Airline::where('code', $route['airline'])->first();
            $origin = Airport::where('code', $route['origin'])->first();
            $destination = Airport::where('code', $route['destination'])->first();

            Route::updateOrCreate([
                'airline_id' => $airline->id,
                'origin_airport_id' => $origin->id,
                'destination_airport_id' => $destination->id,
            ], [
                'flight_number_prefix' => $route['prefix'],
                'distance_km' => $route['distance_km'],
                'estimated_duration_minutes' => $route['duration'],
                'is_active' => true,
            ]);
        }
    }
}