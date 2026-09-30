<?php

namespace Tests\Feature\Api;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_verified_user(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_login_returns_needs_otp_for_unverified_user(): void
    {
        $user = User::factory()->create([
            'password' => 'password123',
            'email_verified_at' => null,
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['needs_otp' => true, 'email' => $user->email]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_register_creates_user_and_returns_otp(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson(['needs_otp' => true, 'email' => 'test@example.com']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'customer',
            'status' => 'inactive',
        ]);
    }

    public function test_verify_otp_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'otp@example.com',
            'email_verified_at' => null,
            'status' => 'inactive',
        ]);

        $otp = OtpCode::generateFor($user->email);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'email' => $user->email,
            'code' => $otp->code,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('active', $user->status);
    }

    public function test_verify_otp_fails_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'email' => 'otp2@example.com',
            'email_verified_at' => null,
        ]);

        OtpCode::generateFor($user->email);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'email' => $user->email,
            'code' => '000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_protected_routes_reject_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);

        $response = $this->postJson('/api/bookings');
        $response->assertStatus(401);

        $response = $this->postJson('/api/payments/initiate');
        $response->assertStatus(401);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_public_routes_are_accessible(): void
    {
        $response = $this->postJson('/api/otp/send', ['email' => 'test@example.com']);
        $response->assertStatus(404);
    }
}
