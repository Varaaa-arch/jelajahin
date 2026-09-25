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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_can_be_rendered_with_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('summary')
                ->where('summary.total_bookings', 0)
                ->where('summary.upcoming', 0)
                ->has('upcoming', 0)
                ->has('history', 0));
    }

    public function test_dashboard_shows_upcoming_and_history_bookings(): void
    {
        $user = User::factory()->create();

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

        $upcomingFlight = Flight::create([
            'route_id' => $route->id,
            'flight_number' => 'GA101',
            'departure_date' => now()->addDays(5)->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '09:00:00',
            'base_price' => 850000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);

        $pastFlight = Flight::create([
            'route_id' => $route->id,
            'flight_number' => 'GA102',
            'departure_date' => now()->subDays(10)->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '09:00:00',
            'base_price' => 850000,
            'status' => 'arrived',
            'seats_available' => 50,
        ]);

        $upcomingBooking = Booking::create([
            'pnr_code' => 'JLN-ABC123',
            'user_id' => $user->id,
            'flight_id' => $upcomingFlight->id,
            'base_amount' => 850000,
            'tax_amount' => 105000,
            'total_price' => 955000,
            'passenger_count' => 1,
            'status' => 'confirmed',
        ]);

        $historyBooking = Booking::create([
            'pnr_code' => 'JLN-XYZ789',
            'user_id' => $user->id,
            'flight_id' => $pastFlight->id,
            'base_amount' => 850000,
            'tax_amount' => 105000,
            'total_price' => 955000,
            'passenger_count' => 2,
            'status' => 'completed',
        ]);

        Payment::create([
            'booking_id' => $upcomingBooking->id,
            'transaction_id' => 'TRX-ABC123',
            'payment_method' => 'fake_gateway',
            'amount' => 955000,
            'status' => 'success',
            'token' => 'tok_abc123',
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('summary.total_bookings', 2)
                ->where('summary.upcoming', 1)
                ->where('summary.completed', 1)
                ->has('upcoming', 1)
                ->where('upcoming.0.pnr_code', 'JLN-ABC123')
                ->has('history', 1)
                ->where('history.0.pnr_code', 'JLN-XYZ789'));
    }
}