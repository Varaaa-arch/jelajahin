<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = [
            ['name' => 'Garuda Indonesia', 'code' => 'GA'],
            ['name' => 'Lion Air', 'code' => 'JT'],
            ['name' => 'Batik Air', 'code' => 'ID'],
            ['name' => 'Citilink', 'code' => 'QG'],
        ];

        foreach ($airlines as $airline) {
            Airline::updateOrCreate(['code' => $airline['code']], $airline);
        }
    }
}