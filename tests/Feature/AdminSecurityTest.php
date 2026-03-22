<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_active_user_can_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_admin_blocked_by_middleware(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin/');
        $response->assertRedirect('/login');
    }

    public function test_open_redirect_blocked(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'redirect' => 'https://evil.com',
        ]);

        // Should redirect to admin dashboard, not external URL
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_local_redirect_allowed(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
            'password' => 'password123',
        ]);
        Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'redirect' => '/checkout',
        ]);

        $response->assertRedirect('/checkout');
    }

    public function test_protocol_relative_redirect_blocked(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'redirect' => '//evil.com',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_without_permission_gets_403(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Staff should not have access to settings by default
        $response = $this->actingAs($user)->get('/admin/settings');
        $response->assertStatus(403);
    }

    public function test_staff_with_default_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/');
        $response->assertStatus(200);
    }

    public function test_security_headers_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_forgot_password_page_loads(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    public function test_password_reset_prevents_email_enumeration(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Should show success even for non-existent emails
        $response->assertSessionHas('success');
    }

    public function test_password_reset_creates_token(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_password_reset_with_valid_token(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword']);
        $token = \Illuminate\Support\Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_password_reset_with_expired_token(): void
    {
        $user = User::factory()->create();
        $token = \Illuminate\Support\Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now()->subMinutes(61),
        ]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_admin_export_requires_auth(): void
    {
        $response = $this->get('/admin/orders/export');
        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_rejects_invalid_dates(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
            ->from('/admin/')
            ->get('/admin/?range=custom&from=not-a-date&to=also-bad');
        $response->assertSessionHasErrors();
    }

    public function test_report_export_rejects_invalid_type(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
            ->from('/admin/reports')
            ->get('/admin/reports/export?type=invalid');
        $response->assertSessionHasErrors('type');
    }
}
