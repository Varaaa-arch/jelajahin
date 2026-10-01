<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlightSeeder extends Seeder
{
    /** Jam keberangkatan yang dipakai bergiliran (2 slot per rute per hari). */
    private const SLOTS = [
        '05:45', '07:10', '08:30', '10:00', '11:30', '13:00',
        '14:30', '16:00', '17:30', '19:00', '20:30', '21:45',
    ];

    private const DAYS_AHEAD = 30;

    private const DAYS_BACK = 7;

    public function run(): void
    {
        $routes = Route::with(['airline', 'originAirport', 'destinationAirport'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($routes->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($routes as $routeIdx => $route) {
            $duration = (int) ($route->estimated_duration_minutes ?? 90);
            $distance = (int) ($route->distance_km ?? 800);
            $prefix = $route->flight_number_prefix ?: ($route->airline?->code ?? 'JT');

            // Penerbangan masa depan: 30 hari × 2 slot/hari, status scheduled.
            for ($day = 0; $day < self::DAYS_AHEAD; $day++) {
                $date = $now->copy()->addDays($day)->toDateString();
                $slotA = self::SLOTS[($routeIdx * 2 + $day) % count(self::SLOTS)];
                $slotB = self::SLOTS[($routeIdx * 2 + $day + 5) % count(self::SLOTS)];

                foreach ([$slotA, $slotB] as $slotIdx => $departure) {
                    $rows[] = $this->makeRow($route->id, $prefix, $routeIdx, $day, $slotIdx, $date, $departure, $duration, $distance, $this->futureStatus($date, $departure), $now);
                }
            }

            // Riwayat: 7 hari ke belakang × 1 slot/hari, status arrived.
            for ($day = 1; $day <= self::DAYS_BACK; $day++) {
                $date = $now->copy()->subDays($day)->toDateString();
                $departure = self::SLOTS[($routeIdx + $day) % count(self::SLOTS)];
                $rows[] = $this->makeRow($route->id, $prefix, $routeIdx, -$day, 3, $date, $departure, $duration, $distance, 'arrived', $now);
            }
        }

        // Upsert batch: flight_number unik, idempoten bila seed dijalankan ulang.
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('flights')->upsert($chunk, ['flight_number'], [
                'route_id', 'departure_date', 'departure_time', 'arrival_time',
                'base_price', 'tax_surcharge', 'fuel_surcharge', 'status',
                'seats_available', 'updated_at',
            ]);
        }

        $this->command?->info('FlightSeeder: ' . count($rows) . ' flights upserted, ' . DB::table('flights')->count() . ' total in DB');
    }

    private function makeRow(string $routeId, string $prefix, int $routeIdx, int $day, int $slotIdx, string $date, string $departure, int $duration, int $distance, string $status, $now): array
    {
        // Nomor unik per (rute, hari, slot): prefix + digit.
        // Tiap rute dapat pita 200 angka (butuh 37 hari × 4 = 148),
        // tiap hari dapat 4 slot (dipakai 0,1 masa depan & 3 riwayat).
        $dayIdx = $day + self::DAYS_BACK; // 0..36
        $number = 1000 + $routeIdx * 200 + $dayIdx * 4 + $slotIdx;
        $arrival = date('H:i', strtotime("{$date} {$departure}") + ($duration + 15) * 60);

        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'route_id' => $routeId,
            'aircraft_id' => null, // diisi FlightSeatSeeder per maskapai
            'flight_number' => $prefix . str_pad((string) $number, 5, '0', STR_PAD_LEFT),
            'departure_date' => $date,
            'departure_time' => $departure,
            'arrival_time' => $arrival,
            'base_price' => $this->price($distance, $routeIdx, $day, $slotIdx, $departure),
            'tax_surcharge' => 50000,
            'fuel_surcharge' => 40000,
            'status' => $status,
            'seats_available' => 60, // selaras B737-800 60 kursi di FlightSeatSeeder
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function price(int $distance, int $routeIdx, int $day, int $slotIdx, string $departure): int
    {
        $base = 350000 + $distance * 850;
        // Variasi deterministik ± biar harga antar hari/jam tidak kembar.
        $variance = (($routeIdx * 37 + ($day + 30) * 11 + $slotIdx * 7) % 9) * 25000;
        // Jam prime-time (pagi & sore) sedikit lebih mahal.
        $hour = (int) substr($departure, 0, 2);
        $prime = ($hour >= 6 && $hour <= 9) || ($hour >= 16 && $hour <= 20) ? 75000 : 0;

        return (int) (round(($base + $variance + $prime) / 1000) * 1000);
    }

    /**
     * Hari ini yang jamnya sudah dekat → boarding, sisanya scheduled.
     * Status valid sesuai ENUM DB (scheduled, boarding, departed, arrived, cancelled).
     */
    private function futureStatus(string $date, string $departure): string
    {
        if ($date !== now()->toDateString()) {
            return 'scheduled';
        }

        $dep = strtotime("{$date} {$departure}");
        $diff = $dep - time();

        return ($diff > -1800 && $diff < 5400) ? 'boarding' : 'scheduled';
    }
}
