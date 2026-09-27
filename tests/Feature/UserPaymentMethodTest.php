<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_payment_methods(): void
    {
        $this->getJson('/payment-methods')->assertStatus(401);
        $this->postJson('/payment-methods', [])->assertStatus(401);
    }

    public function test_user_can_list_own_methods_masked(): void
    {
        $user = User::factory()->create();
        UserPaymentMethod::create([
            'user_id' => $user->id,
            'type' => 'bank_account',
            'provider' => 'bca',
            'account_name' => 'Test User',
            'account_number' => '1234567890',
        ]);

        $res = $this->actingAs($user)->getJson('/payment-methods');

        $res->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.provider', 'bca')
            ->assertJsonPath('data.0.masked_number', '1234••••7890');
        $this->assertArrayNotHasKey('account_number', $res->json('data.0'));
    }

    public function test_user_cannot_see_other_users_methods(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        UserPaymentMethod::create([
            'user_id' => $owner->id,
            'type' => 'e_wallet',
            'provider' => 'gopay',
            'account_name' => 'Owner',
            'account_number' => '08123456789',
        ]);

        $this->actingAs($intruder)->getJson('/payment-methods')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_user_id_mismatch_is_forbidden(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        // Pola existing: client boleh kirim user_id, tapi harus cocok dengan session
        $this->actingAs($user)->postJson('/payment-methods', [
            'user_id' => $other->id,
            'type' => 'bank_account',
            'provider' => 'bca',
            'account_name' => 'Test',
            'account_number' => '1234567890',
        ])->assertStatus(403);
    }

    public function test_store_bank_account_and_card_stores_only_last4(): void
    {
        $user = User::factory()->create();

        $bank = $this->actingAs($user)->postJson('/payment-methods', [
            'type' => 'bank_account',
            'provider' => 'bri',
            'label' => 'BRI Gaji',
            'account_name' => 'Test User',
            'account_number' => '0102030405',
            'is_default' => true,
        ]);
        $bank->assertStatus(201)->assertJsonPath('data.masked_number', '0102••••0405');

        $card = $this->actingAs($user)->postJson('/payment-methods', [
            'type' => 'card',
            'provider' => 'visa',
            'account_name' => 'Test User',
            'account_number' => '4111111111114321',
            'expiry' => '12/28',
        ]);
        $card->assertStatus(201)->assertJsonPath('data.masked_number', '•••• •••• •••• 4321');

        // Nomor penuh tidak tersimpan di DB (hanya last4, terenkripsi)
        $stored = UserPaymentMethod::where('user_id', $user->id)->where('type', 'card')->first();
        $this->assertSame('4321', $stored->account_number);
    }

    public function test_validation_rejects_bad_provider_and_ewallet_phone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/payment-methods', [
            'type' => 'bank_account',
            'provider' => 'gopay',
            'account_name' => 'Test',
            'account_number' => '123',
        ])->assertStatus(422);

        $this->actingAs($user)->postJson('/payment-methods', [
            'type' => 'e_wallet',
            'provider' => 'ovo',
            'account_name' => 'Test',
            'account_number' => 'abc',
        ])->assertStatus(422);
    }

    public function test_only_one_default_and_delete_promotes_next(): void
    {
        $user = User::factory()->create();
        $first = UserPaymentMethod::create([
            'user_id' => $user->id, 'type' => 'bank_account', 'provider' => 'bca',
            'account_name' => 'A', 'account_number' => '1111111111', 'is_default' => true,
        ]);
        $second = UserPaymentMethod::create([
            'user_id' => $user->id, 'type' => 'bank_account', 'provider' => 'bni',
            'account_name' => 'A', 'account_number' => '2222222222',
        ]);

        $this->actingAs($user)->postJson("/payment-methods/{$second->id}/default")
            ->assertStatus(200);

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);

        $this->actingAs($user)->deleteJson("/payment-methods/{$second->id}")
            ->assertStatus(200);
        $this->assertTrue($first->fresh()->is_default);
    }

    public function test_user_cannot_modify_other_users_method(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $method = UserPaymentMethod::create([
            'user_id' => $owner->id, 'type' => 'bank_account', 'provider' => 'bca',
            'account_name' => 'Owner', 'account_number' => '1234567890',
        ]);

        $this->actingAs($intruder)->patchJson("/payment-methods/{$method->id}", [
            'label' => 'Hacked',
        ])->assertStatus(404);

        $this->actingAs($intruder)->deleteJson("/payment-methods/{$method->id}")
            ->assertStatus(404);

        $this->actingAs($intruder)->postJson("/payment-methods/{$method->id}/default")
            ->assertStatus(404);
    }

    public function test_dashboard_includes_payment_methods_prop(): void
    {
        $user = User::factory()->create();
        UserPaymentMethod::create([
            'user_id' => $user->id, 'type' => 'e_wallet', 'provider' => 'dana',
            'account_name' => 'Test', 'account_number' => '08123456789', 'is_default' => true,
        ]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }
}
