<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Customer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/admin/');
        $response->assertStatus(403);
    }

    public function test_driver_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => 'driver']);

        $response = $this->actingAs($user)->get('/admin/');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/admin/');
        $response->assertStatus(200);
    }

    public function test_customer_cannot_see_other_customer_order(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer1->id]);

        $response = $this->actingAs($customer2->user)
            ->get("/customer/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_customer_can_see_own_order(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer->user)
            ->get("/customer/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_driver_cannot_access_unassigned_order(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $otherDriver = User::factory()->create(['role' => 'driver']);
        $order = Order::factory()->create([
            'driver_id' => $otherDriver->id,
            'status' => 'ASSIGNED',
        ]);

        $response = $this->actingAs($driver)
            ->get("/driver/jobs/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_driver_can_access_assigned_order(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $order = Order::factory()->create([
            'driver_id' => $driver->id,
            'status' => 'ASSIGNED',
        ]);

        $response = $this->actingAs($driver)
            ->get("/driver/jobs/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_unauthenticated_cannot_access_customer_portal(): void
    {
        $response = $this->get('/customer/');
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_cannot_access_driver_portal(): void
    {
        $response = $this->get('/driver/');
        $response->assertRedirect('/login');
    }
}
