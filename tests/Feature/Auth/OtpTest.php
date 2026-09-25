<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Models\User;
use App\Models\OtpCode;
use App\Notifications\OtpNotification;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_otp_can_be_sent(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/otp/send', ['email' => 'test@example.com']);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Kode OTP berhasil dikirim.']);
        Notification::assertSentTo($user, OtpNotification::class);
        $this->assertCount(1, OtpCode::all());
    }

    public function test_otp_send_returns_404_for_unknown_email(): void
    {
        $response = $this->postJson('/api/otp/send', ['email' => 'unknown@example.com']);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Email tidak ditemukan.']);
    }

    public function test_otp_send_is_rate_limited(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        for ($i = 0; $i < 3; $i++) {
            OtpCode::generateFor('test@example.com');
        }

        $response = $this->postJson('/api/otp/send', ['email' => 'test@example.com']);

        $response->assertStatus(429)
                 ->assertJson(['message' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.']);
    }

    public function test_otp_can_be_verified(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => null]);
        $otp = OtpCode::generateFor('test@example.com');

        $response = $this->postJson('/api/otp/verify', [
            'email' => 'test@example.com',
            'code'  => $otp->code,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['verified' => true, 'message' => 'Email berhasil diverifikasi!']);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertTrue($otp->fresh()->used_at !== null);
    }

    public function test_otp_verification_fails_for_wrong_code(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        OtpCode::generateFor('test@example.com');

        $response = $this->postJson('/api/otp/verify', [
            'email' => 'test@example.com',
            'code'  => '000000',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Kode tidak valid atau sudah kadaluarsa.']);
    }

    public function test_otp_verification_fails_for_expired_code(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => null]);
        $otp = OtpCode::create([
            'email'      => 'test@example.com',
            'code'       => '123456',
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/otp/verify', [
            'email' => 'test@example.com',
            'code'  => '123456',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Kode tidak valid atau sudah kadaluarsa.']);
    }

    public function test_otp_is_deleted_on_resend(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $oldOtp = OtpCode::generateFor('test@example.com');

        $response = $this->postJson('/api/otp/send', ['email' => 'test@example.com']);

        $response->assertStatus(200);
        $this->assertCount(1, OtpCode::where('email', 'test@example.com')->get());
        $this->assertNotEquals($oldOtp->code, OtpCode::first()->code);
    }

    public function test_verify_sets_email_verified_at(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => null]);
        $otp = OtpCode::generateFor('test@example.com');

        $this->postJson('/api/otp/verify', [
            'email' => 'test@example.com',
            'code'  => $otp->code,
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_does_not_change_already_verified_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => now()]);
        $otp = OtpCode::generateFor('test@example.com');

        $this->postJson('/api/otp/verify', [
            'email' => 'test@example.com',
            'code'  => $otp->code,
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_otp_code_is_6_digits(): void
    {
        $otp = OtpCode::generateFor('test@example.com');

        $this->assertEquals(6, strlen($otp->code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp->code);
    }

    public function test_otp_send_includes_debug_code_in_local(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/otp/send', ['email' => 'test@example.com']);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'expires_at', 'debug_code']);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $response->json('debug_code'));
        Notification::assertSentTo($user, OtpNotification::class);
    }

    public function test_json_register_returns_otp_sent_and_debug_code(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertOk()
            ->assertJson([
                'otp_sent' => true,
                'email' => 'newuser@example.com',
            ])
            ->assertJsonStructure(['debug_code']);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $response->json('debug_code'));
        Notification::assertSentTo(
            User::where('email', 'newuser@example.com')->first(),
            OtpNotification::class
        );
    }
}
