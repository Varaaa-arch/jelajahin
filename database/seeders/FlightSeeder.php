<?php

namespace Database\Seeders;

use App\Models\Flight;
use App\Models\Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        $routes = Route::with(['airline', 'originAirport', 'destinationAirport'])->get();

        $routeIdx = 0;
        $flights = [];

        foreach ($routes as $route) {
            $routeIdx++;

            // Mix of upcoming (+1..+14 days) and past (-5..-30 days) departures
            $dates = [
                now()->addDays(($routeIdx % 5) + 1)->toDateString(),
                now()->addDays(($routeIdx % 7) + 8)->toDateString(),
                now()->subDays(($routeIdx % 6) + 10)->toDateString(),
            ];

            foreach ($dates as $i => $date) {
                $flightNumber = $route->flight_number_prefix . str_pad((string) (100 + $routeIdx + $i), 3, '0', STR_PAD_LEFT);

                $flights[] = [
                    'route_id' => $route->id,
                    'aircraft_id' => null,
                    'flight_number' => $flightNumber,
                    'departure_date' => $date,
                    'departure_time' => ['07:00', '12:30', '16:45', '19:20'][($routeIdx + $i) % 4],
                    'arrival_time' => ['08:50', '14:20', '18:35', '21:10'][($routeIdx + $i) % 4],
                    'base_price' => (600000 + ($routeIdx * 75000) + ($i * 50000)),
                    'tax_surcharge' => 60000,
                    'fuel_surcharge' => 45000,
                    'status' => 'scheduled',
                    'seats_available' => 60 + ($i * 10),
                ];
            }
        }

        foreach ($flights as $flight) {
            Flight::updateOrCreate(['flight_number' => $flight['flight_number']], $flight);
        }

        // Make some past flights "arrived/completed" for realistic history
        Flight::where('departure_date', '<', now()->toDateString())
            ->inRandomOrder()
            ->limit(max(1, intdiv(Flight::count(), 3)))
            ->update(['status' => 'arrived']);
    }
}