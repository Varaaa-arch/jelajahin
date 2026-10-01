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
            ['code' => 'YIA', 'name' => 'Yogyakarta International Airport', 'city' => 'Yogyakarta', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'SRG', 'name' => 'Jenderal Ahmad Yani International Airport', 'city' => 'Semarang', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'SOC', 'name' => 'Adi Soemarmo International Airport', 'city' => 'Solo', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'BDO', 'name' => 'Husein Sastranegara International Airport', 'city' => 'Bandung', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'PLM', 'name' => 'Sultan Mahmud Badaruddin II International Airport', 'city' => 'Palembang', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'BTH', 'name' => 'Hang Nadim International Airport', 'city' => 'Batam', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'PNK', 'name' => 'Supadio International Airport', 'city' => 'Pontianak', 'country' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'BPN', 'name' => 'Sultan Aji Muhammad Sulaiman Airport', 'city' => 'Balikpapan', 'country' => 'Indonesia', 'timezone' => 'Asia/Makassar'],
            ['code' => 'LOP', 'name' => 'Zainuddin Abdul Madjid International Airport', 'city' => 'Lombok', 'country' => 'Indonesia', 'timezone' => 'Asia/Makassar'],
            ['code' => 'MDC', 'name' => 'Sam Ratulangi International Airport', 'city' => 'Manado', 'country' => 'Indonesia', 'timezone' => 'Asia/Makassar'],
            ['code' => 'AMQ', 'name' => 'Pattimura International Airport', 'city' => 'Ambon', 'country' => 'Indonesia', 'timezone' => 'Asia/Jayapura'],
            ['code' => 'DJJ', 'name' => 'Sentani International Airport', 'city' => 'Jayapura', 'country' => 'Indonesia', 'timezone' => 'Asia/Jayapura'],
        ];

        foreach ($airports as $airport) {
            Airport::updateOrCreate(['code' => $airport['code']], $airport);
        }
    }
}