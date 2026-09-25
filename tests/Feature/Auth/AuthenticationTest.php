<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_verified_users_are_not_prompted_for_otp_after_login(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertNull(session('verify_otp'));
    }

    public function test_unverified_users_are_prompted_for_otp_after_login(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(session('verify_otp'));
        Notification::assertSentTo($user, OtpNotification::class);
        $this->assertCount(1, OtpCode::where('email', $user->email)->get());
    }

    public function test_json_login_returns_needs_otp_for_unverified_users(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertOk()
            ->assertJson([
                'needs_otp' => true,
                'email' => $user->email,
            ])
            ->assertJsonStructure(['debug_code']);
        Notification::assertSentTo($user, OtpNotification::class);
    }

    public function test_json_login_returns_needs_otp_false_for_verified_users(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertOk()
            ->assertJson([
                'needs_otp' => false,
                'email' => $user->email,
            ])
            ->assertJsonMissing(['debug_code']);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
