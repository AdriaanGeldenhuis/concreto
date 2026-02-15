<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'status' => 'PLACED',
            'subtotal' => 1000.00,
            'delivery_fee' => 0,
            'vat' => 150.00,
            'total' => 1150.00,
            'locked_total' => 1150.00,
            'order_number' => Order::generateOrderNumber(),
        ];
    }
}
