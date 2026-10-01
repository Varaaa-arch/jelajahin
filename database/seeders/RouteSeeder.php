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
        // [airline, origin, destination, distance_km, duration_min]
        $routes = [
            // Garuda Indonesia — trunk domestik
            ['GA', 'CGK', 'DPS', 980, 110],
            ['GA', 'DPS', 'CGK', 980, 110],
            ['GA', 'CGK', 'SUB', 690, 90],
            ['GA', 'SUB', 'CGK', 690, 90],
            ['GA', 'CGK', 'KNO', 1390, 150],
            ['GA', 'KNO', 'CGK', 1390, 150],
            ['GA', 'CGK', 'UPG', 1390, 155],
            ['GA', 'UPG', 'CGK', 1390, 155],
            ['GA', 'CGK', 'YIA', 520, 70],
            ['GA', 'YIA', 'CGK', 520, 70],
            ['GA', 'SUB', 'DPS', 310, 55],
            ['GA', 'DPS', 'SUB', 310, 55],
            // Lion Air — padat trunk + timur
            ['JT', 'CGK', 'SUB', 690, 90],
            ['JT', 'SUB', 'CGK', 690, 90],
            ['JT', 'CGK', 'DPS', 980, 110],
            ['JT', 'DPS', 'CGK', 980, 110],
            ['JT', 'CGK', 'KNO', 1390, 150],
            ['JT', 'SUB', 'DPS', 310, 55],
            ['JT', 'CGK', 'BPN', 1250, 140],
            ['JT', 'BPN', 'CGK', 1250, 140],
            ['JT', 'CGK', 'LOP', 1070, 120],
            ['JT', 'UPG', 'SUB', 800, 105],
            // Batik Air — Jawa + barat
            ['ID', 'CGK', 'KNO', 1390, 150],
            ['ID', 'KNO', 'CGK', 1390, 150],
            ['ID', 'CGK', 'PLM', 430, 60],
            ['ID', 'PLM', 'CGK', 430, 60],
            ['ID', 'CGK', 'BTH', 880, 105],
            ['ID', 'BTH', 'CGK', 880, 105],
            ['ID', 'CGK', 'SRG', 440, 60],
            ['ID', 'SRG', 'CGK', 440, 60],
            ['ID', 'HLP', 'DPS', 980, 110],
            ['ID', 'HLP', 'SUB', 690, 90],
            // Citilink — LCC Jawa, Kalimantan, Halim
            ['QG', 'CGK', 'UPG', 1390, 155],
            ['QG', 'UPG', 'CGK', 1390, 155],
            ['QG', 'CGK', 'YIA', 520, 70],
            ['QG', 'YIA', 'SUB', 260, 50],
            ['QG', 'SUB', 'YIA', 260, 50],
            ['QG', 'HLP', 'DPS', 980, 110],
            ['QG', 'DPS', 'LOP', 170, 40],
            ['QG', 'LOP', 'DPS', 170, 40],
            ['QG', 'UPG', 'BPN', 500, 70],
            ['QG', 'BPN', 'UPG', 500, 70],
            // Indonesia AirAsia — Bali hub + timur
            ['IW', 'DPS', 'CGK', 980, 110],
            ['IW', 'CGK', 'DPS', 980, 110],
            ['IW', 'DPS', 'SUB', 310, 55],
            ['IW', 'SUB', 'UPG', 800, 105],
            ['IW', 'DPS', 'UPG', 640, 80],
            ['IW', 'DPS', 'LOP', 170, 40],
            // Sriwijaya Air — Sumatera, Kalimantan, timur jauh
            ['SJ', 'CGK', 'PNK', 730, 95],
            ['SJ', 'PNK', 'CGK', 730, 95],
            ['SJ', 'CGK', 'BDO', 180, 50],
            ['SJ', 'CGK', 'SOC', 510, 70],
            ['SJ', 'UPG', 'MDC', 900, 110],
            ['SJ', 'MDC', 'UPG', 900, 110],
            ['SJ', 'CGK', 'AMQ', 2400, 220],
            ['SJ', 'CGK', 'DJJ', 3750, 290],
        ];

        foreach ($routes as [$airlineCode, $originCode, $destinationCode, $distanceKm, $duration]) {
            $airline = Airline::where('code', $airlineCode)->first();
            $origin = Airport::where('code', $originCode)->first();
            $destination = Airport::where('code', $destinationCode)->first();

            if (! $airline || ! $origin || ! $destination) {
                continue;
            }

            Route::updateOrCreate([
                'airline_id' => $airline->id,
                'origin_airport_id' => $origin->id,
                'destination_airport_id' => $destination->id,
            ], [
                'flight_number_prefix' => $airlineCode,
                'distance_km' => $distanceKm,
                'estimated_duration_minutes' => $duration,
                'is_active' => true,
            ]);
        }
    }
}
