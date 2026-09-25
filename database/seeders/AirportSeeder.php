<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $airports = [
            ['code' => 'CGK', 'name' => 'Soekarno–Hatta International Airport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'HLP', 'name' => 'Halim Perdanakusuma International Airport', 'city' => 'Jakarta', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'DPS', 'name' => 'Ngurah Rai International Airport', 'city' => 'Denpasar', 'country' => 'Indonesia', 'timezone' => 'Asia/Makassar'],
            ['code' => 'SUB', 'name' => 'Juanda International Airport', 'city' => 'Surabaya', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'KNO', 'name' => 'Kualanamu International Airport', 'city' => 'Medan', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'UPG', 'name' => 'Sultan Hasanuddin International Airport', 'city' => 'Makassar', 'country' => 'Indonesia', 'timezone' => 'Asia/Makassar'],
        ];

        foreach ($airports as $airport) {
            Airport::updateOrCreate(['code' => $airport['code']], $airport);
        }
    }
}