<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function seedFlight(string $number = 'GA201'): Flight
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

        return Flight::create([
            'route_id' => $route->id,
            'flight_number' => $number,
            'departure_date' => now()->addDays(5)->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '10:00:00',
            'base_price' => 1000000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);
    }

    private function makePayment(User $user, Flight $flight, string $trx, string $status, float $amount = 1100000, string $bookingStatus = 'confirmed'): Payment
    {
        $booking = Booking::create([
            'pnr_code' => 'PAY-'.$trx,
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'base_amount' => 1000000,
            'tax_amount' => 100000,
            'total_price' => $amount,
            'passenger_count' => 1,
            'status' => $bookingStatus,
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'title' => 'Mr',
            'first_name' => 'Pay',
            'last_name' => 'User',
            'identity_type' => 'KTP',
            'identity_number' => '999',
        ]);

        return Payment::create([
            'booking_id' => $booking->id,
            'transaction_id' => $trx,
            'payment_method' => 'fake_gateway',
            'amount' => $amount,
            'status' => $status,
            'token' => 'tok_'.$trx,
            'paid_at' => $status === 'success' ? now() : null,
        ]);
    }

    public function test_guest_and_non_admin_cannot_access_payments(): void
    {
        $this->get('/admin/payments')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/payments')->assertForbidden();
    }

    public function test_admin_can_view_payments_index_with_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $flight = $this->seedFlight();
        $this->makePayment($user, $flight, 'TRX-PAY01', 'success');
        $this->makePayment($user, $flight, 'TRX-PAY02', 'pending', 1100000, 'pending');

        $this->actingAs($admin)
            ->get('/admin/payments')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Payments/Index')
                ->has('payments.data', 2)
                ->where('stats.pending_count', 1)
                ->has('methods', 1)
                ->where('methods.0.method', 'fake_gateway')
                ->has('recon'));
    }

    public function test_admin_can_filter_by_paid_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $flight = $this->seedFlight();
        $this->makePayment($user, $flight, 'TRX-PAY03', 'success');
        $this->makePayment($user, $flight, 'TRX-PAY04', 'pending', 1100000, 'pending');

        $this->actingAs($admin)
            ->get('/admin/payments?status=paid')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('payments.data', 1)
                ->where('payments.data.0.transaction_id', 'TRX-PAY03'));
    }

    public function test_admin_can_expire_pending_payment_and_cancel_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $flight = $this->seedFlight();
        $payment = $this->makePayment($user, $flight, 'TRX-PAY05', 'pending', 1100000, 'pending');

        $this->actingAs($admin)
            ->put("/admin/payments/{$payment->id}", ['status' => 'expired'])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertSame('expired', $payment->fresh()->status);
        $this->assertSame('cancelled', $payment->fresh()->booking->status);
    }

    public function test_admin_can_refund_success_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $flight = $this->seedFlight();
        $payment = $this->makePayment($user, $flight, 'TRX-PAY06', 'success');

        $this->actingAs($admin)
            ->put("/admin/payments/{$payment->id}", ['status' => 'refunded'])
            ->assertRedirect(route('admin.payments.index'));

        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertSame('refunded', $payment->fresh()->booking->status);
    }

    public function test_admin_payment_transitions_are_guarded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $flight = $this->seedFlight();
        $pending = $this->makePayment($user, $flight, 'TRX-PAY07', 'pending', 1100000, 'pending');
        $success = $this->makePayment($user, $flight, 'TRX-PAY08', 'success');

        // Pending tidak bisa langsung refunded.
        $this->actingAs($admin)
            ->put("/admin/payments/{$pending->id}", ['status' => 'refunded'])
            ->assertSessionHasErrors('status');
        $this->assertSame('pending', $pending->fresh()->status);

        // Success tidak bisa di-expire.
        $this->actingAs($admin)
            ->put("/admin/payments/{$success->id}", ['status' => 'expired'])
            ->assertSessionHasErrors('status');
        $this->assertSame('success', $success->fresh()->status);
    }

    public function test_admin_reconciliation_math_for_today(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $flight = $this->seedFlight();
        $this->makePayment($user, $flight, 'TRX-PAY09', 'success', 1100000);

        $today = now()->toDateString();

        $this->actingAs($admin)
            ->get("/admin/payments?recon_date={$today}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('recon.date', $today)
                ->where('recon.expected', 1100000)
                ->where('recon.actual', 1100000)
                ->where('recon.discrepancy', 0));
    }
}
