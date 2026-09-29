<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function seedFlight(): Flight
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
            'flight_number' => 'GA301',
            'departure_date' => now()->addDays(5)->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '10:00:00',
            'base_price' => 1000000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);
    }

    public function test_guest_and_non_admin_cannot_access_users(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        User::factory()->create(['name' => 'Budi Santoso', 'status' => 'active']);
        User::factory()->create(['status' => 'suspended']);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 3)
                ->has('filters'));
    }

    public function test_admin_can_filter_by_role_status_and_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        User::factory()->create(['name' => 'Budi Santoso', 'role' => 'user', 'status' => 'active']);

        $this->actingAs($admin)
            ->get('/admin/users?role=admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('users.data', 1));

        $this->actingAs($admin)
            ->get('/admin/users?q=budi')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Budi Santoso'));
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Andi Pratama',
                'email' => 'andi@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'andi@example.com')->firstOrFail();
        $this->assertSame('active', $user->status);
        $this->assertSame('user', $user->role);
    }

    public function test_admin_can_update_user_role_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}", [
                'name' => $user->name,
                'role' => 'admin',
                'status' => 'suspended',
            ])
            ->assertRedirect(route('admin.users.index'));

        $fresh = $user->fresh();
        $this->assertSame('admin', $fresh->role);
        $this->assertSame('suspended', $fresh->status);
    }

    public function test_admin_cannot_change_own_role_or_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'role' => 'user',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect(route('admin.users.index'));

        // Password baru bisa dipakai login.
        auth()->logout();
        $this->post('/login', ['email' => $user->email, 'password' => 'newpassword123'])
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_cannot_delete_user_with_bookings_or_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);

        Booking::create([
            'pnr_code' => 'USR-DEL01',
            'user_id' => $user->id,
            'flight_id' => $this->seedFlight()->id,
            'base_amount' => 1000000,
            'tax_amount' => 100000,
            'total_price' => 1100000,
            'passenger_count' => 1,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertSessionHasErrors('user');
        $this->assertNotNull(User::find($user->id));

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertSessionHasErrors('user');
        $this->assertNotNull(User::find($admin->id));
    }

    public function test_admin_can_delete_user_without_bookings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect(route('admin.users.index'));

        $this->assertNull(User::find($user->id));
    }

    public function test_suspended_user_is_blocked_from_login_and_kicked_out(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);

        // Login langsung ditolak.
        $this->post('/login', ['email' => $suspended->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // Sesi yang masih aktif ikut ditendang middleware.
        $this->actingAs($suspended)
            ->get('/dashboard')
            ->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_suspended_user_is_rejected_on_social_login(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);

        $socialUser = \Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('soc-999');
        $socialUser->shouldReceive('getEmail')->andReturn($suspended->email);
        $socialUser->shouldReceive('getAvatar')->andReturn(null);

        $driver = \Mockery::mock();
        $driver->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $this->get('/auth/google/callback')
            ->assertRedirect('/')
            ->assertSessionHas('error');

        $this->assertGuest();
    }
}
