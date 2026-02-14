<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderService
{
    public function createOrder(Customer $customer, array $data): Order
    {
        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'DRAFT',
            'delivery_address_id' => $data['delivery_address_id'] ?? $customer->default_address_id,
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'scheduled_time_window' => $data['scheduled_time_window'] ?? null,
            'notes' => $data['notes'] ?? null,
            'delivery_fee' => $data['delivery_fee'] ?? 0,
            'order_number' => Order::generateOrderNumber(),
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'qty' => $item['qty'],
                'unit_price' => $product->price,
                'line_total' => round($product->price * $item['qty'], 2),
            ]);
        }

        $order->calculateTotals();

        // For COD customers, set to PENDING_PAYMENT
        if ($customer->isCod()) {
            $order->update(['status' => 'PENDING_PAYMENT']);
        } else {
            // Account customers - if pay_before_dispatch is required, set to PENDING_PAYMENT
            if ($customer->pay_before_dispatch) {
                $order->update(['status' => 'PENDING_PAYMENT']);
            } else {
                $order->update(['status' => 'PLACED']);
            }
        }

        AuditLog::log('created', 'Order', $order->id, [
            'customer_id' => $customer->id,
            'total' => $order->total,
        ]);

        return $order->fresh();
    }

    public function assignDriver(Order $order, int $driverId): Order
    {
        $order->update([
            'driver_id' => $driverId,
            'status' => 'ASSIGNED',
        ]);

        AuditLog::log('assigned_driver', 'Order', $order->id, [
            'driver_id' => $driverId,
        ]);

        return $order->fresh();
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $oldStatus = $order->status;
        $order->update(['status' => $status]);

        AuditLog::log('status_changed', 'Order', $order->id, [
            'from' => $oldStatus,
            'to' => $status,
        ]);

        return $order->fresh();
    }

    public function cancelOrder(Order $order): Order
    {
        $order->update(['status' => 'CANCELLED']);

        AuditLog::log('cancelled', 'Order', $order->id);

        return $order->fresh();
    }
}
