<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    private function createEligibleBooking(User $user): Booking
    {
        $flight = Flight::factory()->create([
            'departure_date' => now()->addDays(3)->format('Y-m-d'),
            'departure_time' => now()->addDays(3)->format('H:i:s'),
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'status' => 'confirmed',
            'total_price' => 1500000,
        ]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'success',
            'amount' => 1500000,
        ]);

        return $booking;
    }

    public function test_user_can_view_their_refunds(): void
    {
        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        Refund::factory()->create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('refunds.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_request_refund(): void
    {
        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $response = $this->actingAs($user)->post(route('refunds.store'), [
            'booking_id' => $booking->id,
            'reason' => 'Perubahan jadwal perjalanan',
            'refund_type' => 'full',
        ]);

        $response->assertRedirect(route('refunds.index'));
        $this->assertDatabaseHas('refunds', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_request_refund_for_non_confirmed_booking(): void
    {
        $user = User::factory()->create();
        $flight = Flight::factory()->create(['departure_time' => now()->addDays(3)]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($user)->post(route('refunds.store'), [
            'booking_id' => $booking->id,
            'reason' => 'Test',
            'refund_type' => 'full',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_user_cannot_request_refund_within_24_hours_of_departure(): void
    {
        $user = User::factory()->create();
        $flight = Flight::factory()->create([
            'departure_time' => now()->addHours(12),
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'status' => 'confirmed',
        ]);

        Payment::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'success',
        ]);

        $response = $this->actingAs($user)->post(route('refunds.store'), [
            'booking_id' => $booking->id,
            'reason' => 'Test',
            'refund_type' => 'full',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_user_can_cancel_pending_refund(): void
    {
        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::factory()->create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $booking->update(['status' => 'refund_requested']);

        $response = $this->actingAs($user)->delete(route('refunds.cancel', $refund->id));

        $response->assertRedirect(route('refunds.index'));
        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_user_cannot_cancel_non_pending_refund(): void
    {
        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::factory()->create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'status' => 'processed',
        ]);

        $response = $this->actingAs($user)->delete(route('refunds.cancel', $refund->id));

        $response->assertSessionHasErrors(['error']);
    }

    public function test_user_can_view_refund_detail(): void
    {
        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::factory()->create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('refunds.show', $refund->id));

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_users_refund(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $booking = $this->createEligibleBooking($otherUser);

        $refund = Refund::factory()->create([
            'booking_id' => $booking->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->get(route('refunds.show', $refund->id));

        $response->assertStatus(403);
    }
}
