<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\Route;
use App\Models\Setting;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/settings')->assertForbidden();
    }

    public function test_admin_can_view_and_update_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Index')
                ->where('settings.site_name', 'Jelajahi')
                ->where('settings.tax_rate', '10')
                ->has('profile'));

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'site_name' => 'Jelajahi Air',
                'support_email' => 'help@jelajahin.id',
                'tax_rate' => 11,
                'announcement' => 'Promo akhir tahun!',
            ])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertSame('Jelajahi Air', Setting::get('site_name'));
        $this->assertSame('11', Setting::get('tax_rate'));
    }

    public function test_settings_validation_rejects_bad_tax_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'site_name' => 'Jelajahi',
                'support_email' => 'not-an-email',
                'tax_rate' => 150,
                'announcement' => '',
            ])
            ->assertSessionHasErrors(['support_email', 'tax_rate']);

        $this->assertSame('10', Setting::get('tax_rate'));
    }

    public function test_tax_rate_setting_is_used_in_booking_totals(): void
    {
        User::factory()->create(['role' => 'admin', 'status' => 'active']);
        Setting::set('tax_rate', '5');

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
        $flight = Flight::create([
            'route_id' => $route->id,
            'flight_number' => 'GA501',
            'departure_date' => now()->addDays(5)->toDateString(),
            'departure_time' => '07:00:00',
            'arrival_time' => '10:00:00',
            'base_price' => 1000000,
            'status' => 'scheduled',
            'seats_available' => 50,
        ]);

        $total = app(BookingService::class)->calculateTotal($flight->id, 1, null, null);

        $this->assertSame(50000.0, $total['tax']);
        $this->assertSame(1050000.0, $total['total']);
    }

    public function test_admin_can_update_profile_and_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->put('/admin/settings/profile', ['name' => 'Admin Baru', 'email' => $admin->email])
            ->assertRedirect(route('admin.settings.index'));
        $this->assertSame('Admin Baru', $admin->fresh()->name);

        $this->actingAs($admin)
            ->put('/admin/settings/password', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect(route('admin.settings.index'));
        $this->assertTrue(Hash::check('newpassword123', $admin->fresh()->password));
    }

    public function test_admin_password_change_requires_current_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->put('/admin/settings/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertSessionHasErrors('current_password');
    }
}
