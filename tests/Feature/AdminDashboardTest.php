<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Payment;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_admin_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_dashboard_shows_real_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);

        $airline = Airline::create(['name' => 'Garuda Indonesia', 'code' => 'GA']);
        $origin = Airport::create(['code' => 'CGK', 'name' => 'Soekarno-Hatta', 'city' => 'Jakarta', 'country' => 'Indonesia']);
        $destination = Airport::create(['code' => 'DPS', 'name' => 'Ngurah Rai', 'city' => 'Denpasar', 'country' => 'Indonesia']);
        $route = Route::create([
            'airline_id' => $airline->id,
            'origin_airport_id' => $origin->id,
            'destination_airport_id' => $destination->id,
            'flight_number_prefix' => 'GA',
            'is_active' => true,
        ]);
        $flight = Flight::create([
            'route_id' => $route->id,
            'flight_number' => 'GA101',
            'departure_date' => now()->addDays(5)->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '10:00:00',
            'base_price' => 1000000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);
        $booking = Booking::create([
            'pnr_code' => 'DSH-REAL1',
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'base_amount' => 1000000,
            'tax_amount' => 100000,
            'total_price' => 1100000,
            'passenger_count' => 1,
            'status' => 'confirmed',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => 'TRX-DSH1',
            'payment_method' => 'fake_gateway',
            'amount' => 1100000,
            'status' => 'success',
            'token' => 'tok_dsh1',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('stats', 5)
                ->where('stats.0.key', 'revenue')
                ->where('stats.0.value', 'Rp 1.1M')
                ->where('stats.1.value', '1')
                ->has('revenueTrend.labels', 12)
                ->has('bookingsByStatus', 1)
                ->where('bookingsByStatus.0.label', 'Confirmed')
                ->has('topFlights', 1)
                ->where('topFlights.0.flightNo', 'GA101')
                ->has('recentTransactions', 1)
                ->where('recentTransactions.0.id', 'TRX-DSH1')
                ->has('alerts'));
    }
}
