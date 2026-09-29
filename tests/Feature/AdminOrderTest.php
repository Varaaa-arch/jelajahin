<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\AircraftSeat;
use App\Models\AircraftType;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\FlightSeat;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Route;
use App\Models\SeatClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoute(string $prefix = 'GA'): array
    {
        $airline = Airline::create(['name' => 'Garuda Indonesia', 'code' => 'GA']);
        $origin = Airport::create(['code' => 'CGK', 'name' => 'Soekarno-Hatta', 'city' => 'Jakarta', 'country' => 'Indonesia']);
        $destination = Airport::create(['code' => 'DPS', 'name' => 'Ngurah Rai', 'city' => 'Denpasar', 'country' => 'Indonesia']);
        $route = Route::create([
            'airline_id' => $airline->id,
            'origin_airport_id' => $origin->id,
            'destination_airport_id' => $destination->id,
            'flight_number_prefix' => $prefix,
            'is_active' => true,
        ]);

        return compact('airline', 'origin', 'destination', 'route');
    }

    private function makeFlight(string $routeId, string $number, string $date, int $seats = 10): Flight
    {
        return Flight::create([
            'route_id' => $routeId,
            'flight_number' => $number,
            'departure_date' => $date,
            'departure_time' => '07:00:00',
            'arrival_time' => '10:00:00',
            'base_price' => 1000000,
            'status' => 'scheduled',
            'seats_available' => $seats,
        ]);
    }

    private function makeBooking(User $user, Flight $flight, string $pnr, string $status = 'pending'): Booking
    {
        $booking = Booking::create([
            'pnr_code' => $pnr,
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'base_amount' => 1000000,
            'tax_amount' => 100000,
            'total_price' => 1100000,
            'passenger_count' => 1,
            'status' => $status,
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'title' => 'Mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'identity_type' => 'KTP',
            'identity_number' => '1234567890',
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => 'TRX-'.$pnr,
            'payment_method' => 'fake_gateway',
            'amount' => 1100000,
            'status' => 'success',
            'token' => 'tok_'.$pnr,
            'paid_at' => now(),
        ]);

        return $booking;
    }

    private function makeSeat(Flight $flight, bool $available, ?string $bookingId = null): FlightSeat
    {
        $type = AircraftType::create([
            'name' => 'B737-'.$flight->flight_number,
            'manufacturer' => 'Boeing',
            'model' => '737-800',
            'total_seats' => 189,
        ]);
        $class = SeatClass::where('name', 'economy')->firstOrFail();
        $aircraftSeat = AircraftSeat::create([
            'aircraft_type_id' => $type->id,
            'seat_class_id' => $class->id,
            'seat_number' => '1A',
            'row_number' => 1,
            'column_letter' => 'A',
        ]);

        return FlightSeat::create([
            'flight_id' => $flight->id,
            'aircraft_seat_id' => $aircraftSeat->id,
            'current_price' => 1000000,
            'is_available' => $available,
            'booking_id' => $bookingId,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/orders')->assertForbidden();
    }

    public function test_admin_can_view_orders_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $this->makeBooking($user, $flight, 'ORDT01', 'confirmed');

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Orders/Index')
                ->has('orders.data', 1)
                ->where('orders.data.0.pnr', 'ORDT01')
                ->where('orders.data.0.order_status', 'confirmed')
                ->where('orders.data.0.payment_status', 'success')
                ->has('filters')
                ->has('airlines', 1)
                ->has('routes', 1));
    }

    public function test_admin_can_filter_by_order_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $this->makeBooking($user, $flight, 'ORDT02', 'pending');
        $this->makeBooking($user, $flight, 'ORDT03', 'confirmed');

        $this->actingAs($admin)
            ->get('/admin/orders?order_status=confirmed')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('orders.data', 1)
                ->where('orders.data.0.pnr', 'ORDT03'));
    }

    public function test_admin_can_confirm_pending_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $booking = $this->makeBooking($user, $flight, 'ORDT04', 'pending');

        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}/status", ['status' => 'confirmed'])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_admin_cannot_skip_transition(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $booking = $this->makeBooking($user, $flight, 'ORDT05', 'pending');

        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}/status", ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_admin_cancel_releases_seats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $booking = $this->makeBooking($user, $flight, 'ORDT06', 'confirmed');

        $seat = $this->makeSeat($flight, false, $booking->id);
        $booking->passengers()->first()->update(['flight_seat_id' => $seat->id]);

        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}/status", ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertTrue($seat->fresh()->is_available);
    }

    public function test_admin_can_modify_addons_and_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $booking = $this->makeBooking($user, $flight, 'ORDT07', 'confirmed');

        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}", [
                'special_requests' => 'Kursi jendela',
                'addons' => ['baggage' => '20kg', 'insurance' => 'premium', 'meals' => ['Standard Meals']],
            ])
            ->assertRedirect(route('admin.orders.index'));

        $fresh = $booking->fresh();
        $this->assertSame('Kursi jendela', $fresh->special_requests);
        $this->assertSame('20kg', $fresh->addons['baggage']);
        $this->assertSame(['Standard Meals'], $fresh->addons['meals']);
    }

    public function test_admin_can_reschedule_to_same_route_flight(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $oldFlight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $newFlight = $this->makeFlight($route->id, 'GA102', now()->addDays(6)->toDateString());
        $this->makeSeat($newFlight, true);
        $booking = $this->makeBooking($user, $oldFlight, 'ORDT08', 'confirmed');

        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}/reschedule", ['new_flight_id' => $newFlight->id])
            ->assertRedirect(route('admin.orders.index'));

        $fresh = $booking->fresh();
        $this->assertSame($newFlight->id, $fresh->flight_id);
        $this->assertNotNull($fresh->passengers()->first()->fresh()->flight_seat_id);
    }

    public function test_admin_reschedule_fails_gracefully_when_seats_insufficient(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $oldFlight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        // Flight baru tanpa kursi available sama sekali.
        $newFlight = $this->makeFlight($route->id, 'GA102', now()->addDays(6)->toDateString());
        $booking = $this->makeBooking($user, $oldFlight, 'ORDT10', 'confirmed');

        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}/reschedule", ['new_flight_id' => $newFlight->id])
            ->assertSessionHasErrors('new_flight_id');

        // Pesanan tidak berpindah flight.
        $this->assertSame($oldFlight->id, $booking->fresh()->flight_id);
    }

    public function test_admin_can_download_receipt_without_ownership(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        ['route' => $route] = $this->seedRoute();
        $flight = $this->makeFlight($route->id, 'GA101', now()->addDays(5)->toDateString());
        $booking = $this->makeBooking($user, $flight, 'ORDT09', 'confirmed');

        $this->actingAs($admin)
            ->get("/admin/orders/{$booking->id}/receipt?doc=eticket")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
