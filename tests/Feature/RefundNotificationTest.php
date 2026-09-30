<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\RefundApprovedNotification;
use App\Notifications\RefundProcessedNotification;
use App\Notifications\RefundRejectedNotification;
use App\Notifications\RefundRequestedNotification;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RefundNotificationTest extends TestCase
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

    public function test_notification_sent_when_refund_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $service = app(RefundService::class);
        $service->requestRefund($booking, $user, [
            'reason' => 'Perubahan jadwal',
            'refund_type' => 'full',
        ]);

        Notification::assertSentTo($user, RefundRequestedNotification::class);
    }

    public function test_notification_sent_when_refund_approved(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'refund_number' => 'REF-'.date('Ymd').'-TEST01',
            'reason' => 'Test',
            'refund_type' => 'full',
            'requested_amount' => 1500000,
            'status' => 'pending',
        ]);

        $service = app(RefundService::class);
        $service->approveRefund($refund);

        Notification::assertSentTo($user, RefundApprovedNotification::class);
    }

    public function test_notification_sent_when_refund_rejected(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'refund_number' => 'REF-'.date('Ymd').'-TEST02',
            'reason' => 'Test',
            'refund_type' => 'full',
            'requested_amount' => 1500000,
            'status' => 'pending',
        ]);

        $service = app(RefundService::class);
        $service->rejectRefund($refund, 'Tidak memenuhi syarat');

        Notification::assertSentTo($user, RefundRejectedNotification::class);
    }

    public function test_notification_sent_when_refund_processed(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'refund_number' => 'REF-'.date('Ymd').'-TEST03',
            'reason' => 'Test',
            'refund_type' => 'full',
            'requested_amount' => 1500000,
            'status' => 'approved',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $service = app(RefundService::class);
        $service->processRefund($refund, $admin);

        Notification::assertSentTo($user, RefundProcessedNotification::class);
    }

    public function test_no_notification_sent_for_invalid_state(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $booking = $this->createEligibleBooking($user);

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'refund_number' => 'REF-'.date('Ymd').'-TEST04',
            'reason' => 'Test',
            'refund_type' => 'full',
            'requested_amount' => 1500000,
            'status' => 'processed',
        ]);

        $service = app(RefundService::class);

        try {
            $service->approveRefund($refund);
        } catch (\InvalidArgumentException $e) {
            // Expected
        }

        Notification::assertNotSentTo($user, RefundApprovedNotification::class);
    }
}
