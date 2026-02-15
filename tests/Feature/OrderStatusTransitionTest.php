<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
    }

    public function test_valid_transition_placed_to_assigned(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $order = Order::factory()->create(['status' => 'PLACED']);

        $result = $this->orderService->assignDriver($order, $driver->id);

        $this->assertEquals('ASSIGNED', $result->status);
        $this->assertEquals($driver->id, $result->driver_id);
    }

    public function test_invalid_transition_draft_to_assigned_fails(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $order = Order::factory()->create(['status' => 'DRAFT']);

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->assignDriver($order, $driver->id);
    }

    public function test_cannot_deliver_before_loaded(): void
    {
        $order = Order::factory()->create(['status' => 'ACCEPTED']);

        $this->assertFalse($order->canTransitionTo('DELIVERED'));
        $this->assertTrue($order->canTransitionTo('LOADED'));
    }

    public function test_valid_full_lifecycle(): void
    {
        $order = Order::factory()->create(['status' => 'PLACED']);
        $driver = User::factory()->create(['role' => 'driver']);

        // PLACED -> ASSIGNED
        $order = $this->orderService->assignDriver($order, $driver->id);
        $this->assertEquals('ASSIGNED', $order->status);

        // ASSIGNED -> ACCEPTED
        $order = $this->orderService->updateStatus($order, 'ACCEPTED');
        $this->assertEquals('ACCEPTED', $order->status);

        // ACCEPTED -> LOADED
        $order = $this->orderService->updateStatus($order, 'LOADED');
        $this->assertEquals('LOADED', $order->status);

        // LOADED -> IN_TRANSIT
        $order = $this->orderService->updateStatus($order, 'IN_TRANSIT');
        $this->assertEquals('IN_TRANSIT', $order->status);

        // IN_TRANSIT -> ARRIVED
        $order = $this->orderService->updateStatus($order, 'ARRIVED');
        $this->assertEquals('ARRIVED', $order->status);

        // ARRIVED -> DELIVERED
        $order = $this->orderService->updateStatus($order, 'DELIVERED');
        $this->assertEquals('DELIVERED', $order->status);
    }

    public function test_delivered_is_terminal(): void
    {
        $order = Order::factory()->create(['status' => 'DELIVERED']);

        $this->assertFalse($order->canTransitionTo('CANCELLED'));
        $this->assertFalse($order->canTransitionTo('PLACED'));
        $this->assertEmpty(Order::ALLOWED_TRANSITIONS['DELIVERED']);
    }

    public function test_cancel_from_valid_status(): void
    {
        $order = Order::factory()->create(['status' => 'PLACED']);

        $result = $this->orderService->cancelOrder($order, 'Customer requested');
        $this->assertEquals('CANCELLED', $result->status);
    }

    public function test_cancel_from_delivered_fails(): void
    {
        $order = Order::factory()->create(['status' => 'DELIVERED']);

        $this->expectException(\InvalidArgumentException::class);
        $this->orderService->cancelOrder($order);
    }

    public function test_force_status_works_for_admin(): void
    {
        $order = Order::factory()->create(['status' => 'DELIVERED']);

        $result = $this->orderService->forceStatus($order, 'CANCELLED', 'Admin override - wrong delivery');
        $this->assertEquals('CANCELLED', $result->status);
    }
}
