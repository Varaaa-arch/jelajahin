<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(User $user): Booking
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

        $booking = Booking::create([
            'pnr_code' => 'JLN-PAY001',
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'base_amount' => 850000,
            'tax_amount' => 85000,
            'total_price' => 935000,
            'passenger_count' => 1,
            'status' => 'pending',
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'title' => 'Mr',
            'first_name' => 'Test',
            'last_name' => 'User',
            'date_of_birth' => '1990-01-01',
            'gender' => 'M',
            'identity_type' => 'id_card',
            'identity_number' => '3174000101900001',
            'nationality' => 'Indonesia',
        ]);

        return $booking;
    }

    /**
     * API /api/payments/* dijaga auth:sanctum (Bearer token).
     * Helper ini create token untuk user dan ikut kiri Authorization header.
     */
    private function withBearer(User $user)
    {
        $token = $user->createToken('api-test')->plainTextToken;
        return $this->withHeader('Authorization', 'Bearer '.$token);
    }

    public function test_full_payment_flow_initiate_and_process(): void
    {
        $user = User::factory()->create();
        $booking = $this->makeBooking($user);

        $initiate = $this->withBearer($user)->postJson('/api/payments/initiate', [
            'booking_id' => $booking->id,
        ]);

        $initiate->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'transaction_id', 'amount', 'status']]);

        $token = $initiate->json('data.token');
        $this->assertNotEmpty($token);

        $process = $this->withBearer($user)->postJson('/api/payments/process', [
            'token' => $token,
        ]);

        $process->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseHas('payments', [
            'token' => $token,
            'status' => 'success',
        ]);

        // Pembayaran sukses tapi booking MENUNGGU persetujuan admin — belum confirmed.
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'awaiting_confirmation',
        ]);

        // Dokumen belak tergenerate sampai admin persetujui.
        $this->assertDatabaseMissing('etickets', ['booking_id' => $booking->id]);
        $this->assertDatabaseMissing('invoices', ['booking_id' => $booking->id]);
    }

    public function test_admin_approval_finalizes_awaiting_booking(): void
    {
        $user = User::factory()->create();
        $booking = $this->makeBooking($user);

        $initiate = $this->withBearer($user)->postJson('/api/payments/initiate', [
            'booking_id' => $booking->id,
        ]);
        $token = $initiate->json('data.token');

        $this->withBearer($user)->postJson('/api/payments/process', [
            'token' => $token,
        ])->assertStatus(200);

        $this->assertSame('awaiting_confirmation', $booking->fresh()->status);

        // Admin persetujui order dari panel.
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->put("/admin/orders/{$booking->id}/status", ['status' => 'confirmed'])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame('confirmed', $booking->fresh()->status);

        // Kursi + dokumen finalize hanya setelah persetujuan admin.
        $this->assertDatabaseHas('etickets', ['booking_id' => $booking->id]);
        $this->assertDatabaseHas('invoices', ['booking_id' => $booking->id, 'status' => 'paid']);
    }

    public function test_initiate_fails_for_unknown_booking(): void
    {
        $user = User::factory()->create();

        $this->withBearer($user)->postJson('/api/payments/initiate', [
            'booking_id' => '00000000-0000-0000-0000-000000000000',
        ])->assertStatus(404);
    }

    public function test_process_fails_for_unknown_token(): void
    {
        $user = User::factory()->create();

        $this->withBearer($user)->postJson('/api/payments/process', [
            'token' => 'FAKE_TOKEN_UNKNOWN',
        ])->assertStatus(400);
    }
}
