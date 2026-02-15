<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
    }

    public function test_duplicate_order_with_same_idempotency_key_returns_same_order(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $address = Address::factory()->create(['customer_id' => $customer->id]);

        $data = [
            'delivery_address_id' => $address->id,
            'items' => [
                ['product_id' => $product->id, 'qty' => 5],
            ],
            'idempotency_key' => 'test-key-123',
        ];

        $order1 = $this->orderService->createOrder($customer, $data);
        $order2 = $this->orderService->createOrder($customer, $data);

        $this->assertEquals($order1->id, $order2->id);
        $this->assertEquals(1, Order::where('idempotency_key', 'test-key-123')->count());
    }

    public function test_different_idempotency_keys_create_different_orders(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $address = Address::factory()->create(['customer_id' => $customer->id]);

        $data1 = [
            'delivery_address_id' => $address->id,
            'items' => [['product_id' => $product->id, 'qty' => 5]],
            'idempotency_key' => 'key-1',
        ];
        $data2 = [
            'delivery_address_id' => $address->id,
            'items' => [['product_id' => $product->id, 'qty' => 3]],
            'idempotency_key' => 'key-2',
        ];

        $order1 = $this->orderService->createOrder($customer, $data1);
        $order2 = $this->orderService->createOrder($customer, $data2);

        $this->assertNotEquals($order1->id, $order2->id);
    }

    public function test_order_totals_calculated_server_side(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);
        $address = Address::factory()->create(['customer_id' => $customer->id]);

        $order = $this->orderService->createOrder($customer, [
            'delivery_address_id' => $address->id,
            'items' => [
                ['product_id' => $product->id, 'qty' => 10],
            ],
        ]);

        $order->refresh();
        $this->assertEquals('1000.00', $order->subtotal);
        $this->assertEquals('150.00', $order->vat); // 15% VAT
        $this->assertEquals('1150.00', $order->total);
        $this->assertEquals('1150.00', $order->locked_total);
    }
}
