<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class FlightSeatSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');

        // 1. Aircraft Type: B737-800 dengan 60 kursi (10 rows x 6 columns)
        $aircraftType = DB::table('aircraft_types')->where('name', 'B737-800')->first();
        if (! $aircraftType) {
            $id = (string) Str::uuid();
            DB::table('aircraft_types')->insert([
                'id' => $id,
                'name' => 'B737-800',
                'manufacturer' => 'Boeing',
                'model' => '737-800',
                'total_seats' => 60,
            ]);
            $aircraftType = DB::table('aircraft_types')->where('id', $id)->first();
        }

        // 2. Ensure seat class economy exists
        $economyClass = DB::table('seat_classes')->where('name', 'economy')->first();
        if (! $economyClass) {
            $id = (string) Str::uuid();
            DB::table('seat_classes')->insert([
                'id' => $id,
                'name' => 'economy',
                'display_name' => 'Economy',
                'baggage_allowance_kg' => 20,
            ]);
            $economyClass = DB::table('seat_classes')->where('id', $id)->first();
        }

        // 3. Create aircraft_seats for this aircraft_type if empty
        if (DB::table('aircraft_seats')->where('aircraft_type_id', $aircraftType->id)->count() === 0) {
            $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
            $seats = [];
            for ($row = 1; $row <= 10; $row++) {
                foreach ($columns as $col) {
                    $seats[] = [
                        'id' => (string) Str::uuid(),
                        'aircraft_type_id' => $aircraftType->id,
                        'seat_class_id' => $economyClass->id,
                        'seat_number' => $row . $col,
                        'row_number' => $row,
                        'column_letter' => $col,
                        'is_exit_row' => in_array($row, [1, 10]),
                    ];
                }
            }
            DB::table('aircraft_seats')->insert($seats);
            $this->command?->info('Created 60 aircraft_seats for B737-800');
        }

        $aircraftSeats = DB::table('aircraft_seats')->where('aircraft_type_id', $aircraftType->id)->get();

        // 4. Ensure aircraft exists per airline and link to flights
        $flights = DB::table('flights')->join('routes', 'flights.route_id', '=', 'routes.id')->select('flights.*', 'routes.airline_id')->get();
        foreach ($flights as $flight) {
            if (! $flight->aircraft_id) {
                $aircraft = DB::table('aircraft')->where('airline_id', $flight->airline_id)->where('aircraft_type_id', $aircraftType->id)->first();
                if (! $aircraft) {
                    $aircraftId = (string) Str::uuid();
                    DB::table('aircraft')->insert([
                        'id' => $aircraftId,
                        'aircraft_type_id' => $aircraftType->id,
                        'airline_id' => $flight->airline_id,
                        'registration_number' => 'PK-' . strtoupper(Str::random(3)) . '-' . rand(100, 999),
                        'manufacture_year' => 2018 + rand(0, 6),
                        'is_active' => true,
                    ]);
                    $aircraft = DB::table('aircraft')->where('id', $aircraftId)->first();
                }
                DB::table('flights')->where('id', $flight->id)->update(['aircraft_id' => $aircraft->id]);
            }

            $existingCount = DB::table('flight_seats')->where('flight_id', $flight->id)->count();
            if ($existingCount > 0) {
                continue;
            }

            $flightSeats = [];
            foreach ($aircraftSeats as $aSeat) {
                $flightSeats[] = [
                    'id' => (string) Str::uuid(),
                    'flight_id' => $flight->id,
                    'aircraft_seat_id' => $aSeat->id,
                    'current_price' => $flight->base_price,
                    'is_available' => true,
                    'booking_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            foreach (array_chunk($flightSeats, 500) as $chunk) {
                DB::table('flight_seats')->insert($chunk);
            }
            $this->command?->info("Seeded " . count($flightSeats) . " flight_seats for flight {$flight->flight_number} ({$flight->id})");
        }

        $this->command?->info('FlightSeatSeeder completed: ' . DB::table('flight_seats')->count() . ' total flight_seats');
    }
}
