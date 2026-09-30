<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRefundTest extends TestCase
{
    use RefreshDatabase;

    private function createRefundWithBooking(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $flight = Flight::factory()->create([
            'departure_date' => now()->addDays(3)->format('Y-m-d'),
            'departure_time' => now()->addDays(3)->format('H:i'),
        ]);

        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'flight_id' => $flight->id,
            'status' => 'refund_requested',
            'total_price' => 1500000,
        ]);

        $payment = Payment::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'success',
            'amount' => 1500000,
        ]);

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
            'user_id' => $customer->id,
            'refund_number' => 'REF-'.date('Ymd').'-TEST01',
            'reason' => 'Test refund',
            'refund_type' => 'full',
            'requested_amount' => 1500000,
            'status' => 'pending',
        ]);

        $booking->update(['status' => 'refund_requested']);
        $payment->refresh();
        $refund->refresh();

        return [$admin, $customer, $booking, $refund];
    }

    public function test_admin_can_view_refunds_list(): void
    {
        [$admin] = $this->createRefundWithBooking();

        $response = $this->actingAs($admin)->get(route('admin.refunds.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_refund_detail(): void
    {
        [$admin, , , $refund] = $this->createRefundWithBooking();

        $response = $this->actingAs($admin)->get(route('admin.refunds.show', $refund->id));

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_refund(): void
    {
        [$admin, , , $refund] = $this->createRefundWithBooking();

        $response = $this->actingAs($admin)->put(route('admin.refunds.approve', $refund->id), [
            'approved_amount' => $refund->requested_amount,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'status' => 'approved',
            'approved_amount' => $refund->requested_amount,
        ]);
    }

    public function test_admin_can_reject_refund(): void
    {
        [$admin, , $booking, $refund] = $this->createRefundWithBooking();

        $response = $this->actingAs($admin)->put(route('admin.refunds.reject', $refund->id), [
            'admin_notes' => 'Refund tidak memenuhi syarat',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_can_process_refund(): void
    {
        [$admin, , $booking, $refund] = $this->createRefundWithBooking();

        $refund->update(['status' => 'approved']);

        $response = $this->actingAs($admin)->put(route('admin.refunds.process', $refund->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('refunds', [
            'id' => $refund->id,
            'status' => 'processed',
            'processed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'refunded',
        ]);

        $payment = $refund->payment;
        $this->assertNotNull($payment);
        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);
    }

    public function test_non_admin_cannot_access_admin_refunds(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.refunds.index'));

        $response->assertStatus(403);
    }
}
