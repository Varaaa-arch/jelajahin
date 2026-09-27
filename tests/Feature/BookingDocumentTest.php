<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(User $user, string $pnr = 'JLN-DOC001'): Booking
    {
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
            'arrival_time' => '09:00:00',
            'base_price' => 850000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);

        return Booking::create([
            'pnr_code' => $pnr,
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'base_amount' => 850000,
            'tax_amount' => 85000,
            'discount_amount' => 0,
            'total_price' => 935000,
            'passenger_count' => 1,
            'status' => 'confirmed',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/booking/documents/JLN-DOC001')->assertRedirect('/login');
    }

    public function test_owner_can_download_eticket_pdf(): void
    {
        $user = User::factory()->create();
        $booking = $this->makeBooking($user);

        $response = $this->actingAs($user)
            ->get("/booking/documents/{$booking->pnr_code}?doc=eticket");

        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', $response->headers->get('Content-Type'));
    }

    public function test_owner_can_download_invoice_pdf(): void
    {
        $user = User::factory()->create();
        $booking = $this->makeBooking($user);

        $response = $this->actingAs($user)
            ->get("/booking/documents/{$booking->pnr_code}?doc=invoice");

        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', $response->headers->get('Content-Type'));
        $this->assertDatabaseHas('invoices', ['booking_id' => $booking->id]);
    }

    public function test_other_user_is_forbidden(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $booking = $this->makeBooking($owner);

        $this->actingAs($intruder)
            ->get("/booking/documents/{$booking->pnr_code}")
            ->assertStatus(403);
    }

    public function test_unknown_pnr_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/booking/documents/UNKNOWN')
            ->assertStatus(404);
    }

    public function test_unknown_doc_type_returns_404(): void
    {
        $user = User::factory()->create();
        $booking = $this->makeBooking($user);

        $this->actingAs($user)
            ->get("/booking/documents/{$booking->pnr_code}?doc=boardingpass")
            ->assertStatus(404);
    }
}
