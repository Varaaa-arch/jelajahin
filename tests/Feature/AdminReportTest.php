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

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_reports(): void
    {
        $this->get('/admin/reports')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/reports')->assertForbidden();
    }

    public function test_admin_can_view_reports_with_aggregates(): void
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
            'flight_number' => 'GA401',
            'departure_date' => now()->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '10:00:00',
            'base_price' => 1000000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);
        $booking = Booking::create([
            'pnr_code' => 'RPT-001',
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
            'transaction_id' => 'TRX-RPT1',
            'payment_method' => 'fake_gateway',
            'amount' => 1100000,
            'status' => 'success',
            'token' => 'tok_rpt1',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/reports')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Index')
                ->where('summary.revenue', 1100000)
                ->where('summary.bookings', 1)
                ->has('revenueTrend.labels')
                ->has('bookingsByStatus', 1)
                ->where('bookingsByStatus.0.status', 'confirmed')
                ->has('topRoutes', 1)
                ->where('topRoutes.0.airline', 'GA')
                ->has('topFlights', 1)
                ->has('paymentSummary')
                ->has('occupancy'));
    }
}
