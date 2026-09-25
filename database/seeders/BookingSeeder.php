<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\ETicket;
use App\Models\Flight;
use App\Models\Invoice;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        if ($user->bookings()->exists()) {
            return;
        }

        $upcoming = Flight::where('departure_date', '>=', now()->toDateString())
            ->orderBy('departure_date')
            ->get();

        $past = Flight::where('departure_date', '<', now()->toDateString())
            ->orderByDesc('departure_date')
            ->get();

        // 2 upcoming confirmed bookings
        if ($upcoming->count() >= 2) {
            $this->buildBooking($user, $upcoming[0], 'confirmed', 2);
            $this->buildBooking($user, $upcoming[1], 'confirmed', 1);
        }

        // 1 upcoming pending booking (belum bayar)
        if ($upcoming->count() >= 3) {
            $this->buildBooking($user, $upcoming[2], 'pending', 1);
        }

        // 2 completed bookings (riwayat)
        if ($past->count() >= 2) {
            $this->buildBooking($user, $past[0], 'completed', 2);
            $this->buildBooking($user, $past[1], 'completed', 1);
        }

        // 1 cancelled bayangan
        if ($upcoming->count() >= 4) {
            $this->buildBooking($user, $upcoming[3], 'cancelled', 1);
        }
    }

    private function buildBooking(User $user, Flight $flight, string $status, int $passengerCount): void
    {
        $base = (float) $flight->base_price;
        $tax = (float) $flight->tax_surcharge + (float) $flight->fuel_surcharge;
        $discount = $status === 'cancelled' ? 0 : min(50000, round($base * 0.05));
        $total = ($base - $discount) + $tax;

        $total = round($total * $passengerCount, 2);
        $base = $base * $passengerCount;
        $tax = $tax * $passengerCount;

        $booking = Booking::create([
            'pnr_code' => 'JLN-' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'promo_id' => null,
            'base_amount' => $base,
            'discount_amount' => $discount * $passengerCount,
            'tax_amount' => $tax,
            'total_price' => $total,
            'passenger_count' => $passengerCount,
            'status' => $status,
            'special_requests' => null,
        ]);

        $passengers = [];
        $seats = ['A1', 'A2', 'B1', 'B2', 'C1', 'E1', 'E2'];
        $names = [
            ['title' => 'Mr.', 'first' => 'Budi', 'last' => 'Santoso'],
            ['title' => 'Mrs.', 'first' => 'Sari', 'last' => 'Lestari'],
            ['title' => 'Mr.', 'first' => 'Andi', 'last' => 'Pratama'],
        ];

        for ($i = 0; $i < $passengerCount; $i++) {
            $name = $names[$i % count($names)];
            $passengers[] = Passenger::create([
                'booking_id' => $booking->id,
                'title' => $name['title'],
                'first_name' => $name['first'],
                'last_name' => $name['last'],
                'date_of_birth' => now()->subYears(25 + $i)->toDateString(),
                'gender' => $i === 1 ? 'female' : 'male',
                'identity_type' => 'ktp',
                'identity_number' => '3201' . random_int(100000, 999999),
                'nationality' => 'Indonesia',
                'passport_number' => null,
                'flight_seat_id' => null,
                'check_in_status' => $status === 'completed' ? 'checked_in' : 'not_checked_in',
            ]);
        }

        if ($status === 'pending') {
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => 'TRX-' . strtoupper(Str::random(10)),
                'payment_method' => 'fake_gateway',
                'amount' => $booking->total_price,
                'status' => 'pending',
                'token' => 'tok_' . Str::random(24),
                'expires_at' => now()->addMinutes(90),
                'paid_at' => null,
                'webhook_data' => null,
            ]);

            return;
        }

        $paid = in_array($status, ['confirmed', 'completed'], true);

        Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => 'TRX-' . strtoupper(Str::random(10)),
            'payment_method' => 'fake_gateway',
            'amount' => $booking->total_price,
            'status' => $paid ? 'success' : 'expired',
            'token' => 'tok_' . Str::random(24),
            'expires_at' => null,
            'paid_at' => $paid ? now() : null,
            'webhook_data' => null,
        ]);

        if (!$paid) {
            return;
        }

        Invoice::create([
            'booking_id' => $booking->id,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'issued_date' => $booking->created_at->toDateString(),
            'subtotal' => $booking->base_amount,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total_amount' => $booking->total_price,
            'status' => 'paid',
            'invoice_url' => null,
        ]);

        foreach ($passengers as $index => $passenger) {
            ETicket::create([
                'booking_id' => $booking->id,
                'eticket_number' => 'ETK-' . strtoupper(Str::random(8)),
                'passenger_name' => $passenger->title . ' ' . $passenger->first_name . ' ' . $passenger->last_name,
                'flight_number' => $flight->flight_number,
                'departure_date' => $flight->departure_date,
                'departure_time' => $flight->departure_time,
                'seat_number' => $seats[$index % count($seats)],
                'pdf_path' => null,
                'is_sent' => true,
                'sent_at' => $booking->created_at,
            ]);
        }
    }
}