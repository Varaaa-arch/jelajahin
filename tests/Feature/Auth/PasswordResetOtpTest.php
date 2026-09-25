<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use App\Models\User;
use App\Models\OtpCode;
use App\Notifications\PasswordResetOtpNotification;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    public function test_otp_can_be_sent_for_password_reset(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/forgot-password', ['email' => 'test@example.com']);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Kode OTP pengaturan ulang password berhasil dikirim.']);
        Notification::assertSentTo($user, PasswordResetOtpNotification::class);
    }

    public function test_otp_send_returns_404_for_unknown_email(): void
    {
        $response = $this->postJson('/api/forgot-password', ['email' => 'unknown@example.com']);
        $response->assertStatus(404)
                 ->assertJson(['message' => 'Email tidak ditemukan.']);
    }

    public function test_otp_send_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        for ($i = 0; $i < 3; $i++) {
            OtpCode::generateFor('test@example.com');
        }

        $response = $this->postJson('/api/forgot-password', ['email' => 'test@example.com']);
        $response->assertStatus(429)
                 ->assertJson(['message' => 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.']);
    }

    public function test_otp_verification_mints_reset_token(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => null]);
        $otp = OtpCode::generateFor('test@example.com');

        $response = $this->postJson('/api/forgot-password/otp', [
            'email' => 'test@example.com',
            'code'  => $otp->code,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['verified', 'token', 'email']);
        $this->assertNotNull($response->json('token'));
    }

    public function test_otp_verification_fails_for_wrong_code(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        OtpCode::generateFor('test@example.com');

        $response = $this->postJson('/api/forgot-password/otp', [
            'email' => 'test@example.com',
            'code'  => '000000',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Kode tidak valid atau sudah kadaluarsa.']);
    }

    public function test_otp_verification_fails_for_expired_code(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        OtpCode::create([
            'email'      => 'test@example.com',
            'code'       => '123456',
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/forgot-password/otp', [
            'email' => 'test@example.com',
            'code'  => '123456',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Kode tidak valid atau sudah kadaluarsa.']);
    }

    public function test_otp_verification_does_not_set_email_verified_at(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => null]);
        $otp = OtpCode::generateFor('test@example.com');

        $this->postJson('/api/forgot-password/otp', [
            'email' => 'test@example.com',
            'code'  => $otp->code,
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_password_reset_using_token_from_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => null]);
        $otp = OtpCode::generateFor('test@example.com');

        $verifyResponse = $this->postJson('/api/forgot-password/otp', [
            'email' => 'test@example.com',
            'code'  => $otp->code,
        ]);

        $token = $verifyResponse->json('token');
        $this->assertNotNull($token);

        $resetResponse = $this->postJson('/api/password/reset', [
            'token'                  => $token,
            'email'                  => 'test@example.com',
            'password'               => 'newSecurePassword123!',
            'password_confirmation'  => 'newSecurePassword123!',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('newSecurePassword123!', $user->fresh()->password));
    }

    public function test_otp_code_is_6_digits(): void
    {
        $otp = OtpCode::generateFor('test@example.com');
        $this->assertEquals(6, strlen($otp->code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp->code);
    }
}
