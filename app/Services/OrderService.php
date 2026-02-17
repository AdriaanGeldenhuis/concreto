<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(private NotificationService $notificationService) {}

    public function createOrder(Customer $customer, array $data): Order
    {
        $order = DB::transaction(function () use ($customer, $data) {
            // Check idempotency key
            if (!empty($data['idempotency_key'])) {
                $existing = Order::where('idempotency_key', $data['idempotency_key'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            // Check credit limit for ACCOUNT customers
            if ($customer->isAccount() && $customer->credit_limit > 0) {
                // Estimate order total before creating
                $estimatedSubtotal = 0;
                foreach ($data['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $estimatedSubtotal += round($product->price * $item['qty'], 2);
                    }
                }
                $estimatedTotal = round($estimatedSubtotal * 1.15, 2); // Rough estimate with VAT

                $availableCredit = $customer->available_credit;
                if ($availableCredit !== null && $estimatedTotal > $availableCredit) {
                    throw new \InvalidArgumentException(
                        "Order exceeds credit limit. Available credit: R" . number_format($availableCredit, 2)
                        . ", estimated order total: R" . number_format($estimatedTotal, 2)
                        . ". Credit limit: R" . number_format($customer->credit_limit, 2)
                        . ", outstanding: R" . number_format($customer->outstanding_balance, 2) . "."
                    );
                }
            }

            // Calculate delivery fee server-side if address is provided
            $deliveryFee = $this->calculateDeliveryFee($data['delivery_address_id'] ?? null);

            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'DRAFT',
                'delivery_address_id' => $data['delivery_address_id'] ?? $customer->default_address_id,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'scheduled_time_window' => $data['scheduled_time_window'] ?? null,
                'notes' => $data['notes'] ?? null,
                'delivery_fee' => $deliveryFee,
                'order_number' => Order::generateOrderNumber(),
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'promo_code_id' => $data['promo_code_id'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitPrice = $item['unit_price'] ?? $product->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'unit_price' => $unitPrice,
                    'line_total' => round($unitPrice * $item['qty'], 2),
                ]);
            }

            $order->calculateTotals();

            // Determine initial status based on customer type
            if ($customer->isCod() || $customer->pay_before_dispatch) {
                $order->update(['status' => 'PENDING_PAYMENT']);
            } else {
                $order->update(['status' => 'PLACED']);
            }

            AuditLog::log('created', 'Order', $order->id, [
                'customer_id' => $customer->id,
                'total' => $order->fresh()->total,
            ]);

            return $order->fresh();
        });

        // Send order confirmation notification (outside transaction)
        try {
            $this->notificationService->orderConfirmed($order);
        } catch (\Exception $e) {
            Log::warning('Failed to send order confirmation notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    public function assignDriver(Order $order, int $driverId): Order
    {
        return DB::transaction(function () use ($order, $driverId) {
            $order = Order::lockForUpdate()->find($order->id);

            if (!$order->canTransitionTo('ASSIGNED')) {
                throw new \InvalidArgumentException(
                    "Cannot assign driver: order status '{$order->status}' cannot transition to ASSIGNED."
                );
            }

            $order->update([
                'driver_id' => $driverId,
                'status' => 'ASSIGNED',
            ]);

            AuditLog::log('assigned_driver', 'Order', $order->id, [
                'driver_id' => $driverId,
            ]);

            return $order->fresh();
        });
    }

    public function updateStatus(Order $order, string $status, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $status, $reason) {
            $order = Order::lockForUpdate()->find($order->id);
            $oldStatus = $order->status;

            if (!$order->canTransitionTo($status)) {
                throw new \InvalidArgumentException(
                    "Invalid status transition from '{$oldStatus}' to '{$status}'."
                );
            }

            $order->update(['status' => $status]);

            $meta = [
                'from' => $oldStatus,
                'to' => $status,
            ];
            if ($reason) {
                $meta['reason'] = $reason;
            }

            AuditLog::log('status_changed', 'Order', $order->id, $meta);

            return $order->fresh();
        });
    }

    public function forceStatus(Order $order, string $status, string $reason): Order
    {
        return DB::transaction(function () use ($order, $status, $reason) {
            $order = Order::lockForUpdate()->find($order->id);
            $oldStatus = $order->status;

            if (!in_array($status, Order::STATUSES, true)) {
                throw new \InvalidArgumentException("Invalid status: '{$status}'.");
            }

            $order->update(['status' => $status]);

            AuditLog::log('force_status_changed', 'Order', $order->id, [
                'from' => $oldStatus,
                'to' => $status,
                'reason' => $reason,
            ]);

            return $order->fresh();
        });
    }

    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            $order = Order::lockForUpdate()->find($order->id);

            if (!$order->canTransitionTo('CANCELLED')) {
                throw new \InvalidArgumentException(
                    "Cannot cancel order in status '{$order->status}'."
                );
            }

            $order->update(['status' => 'CANCELLED']);

            AuditLog::log('cancelled', 'Order', $order->id, [
                'reason' => $reason,
            ]);

            return $order->fresh();
        });
    }

    private function calculateDeliveryFee(?int $addressId): float
    {
        if (!$addressId) {
            return 0;
        }

        $address = \App\Models\Address::find($addressId);
        if (!$address || !$address->postal_code) {
            return 0;
        }

        $area = DeliveryArea::where('is_active', true)->get()->first(function ($area) use ($address) {
            $codes = array_map('trim', explode(',', $area->postal_codes));
            return in_array($address->postal_code, $codes, true);
        });

        return $area ? (float) $area->fee : 0;
    }
}
