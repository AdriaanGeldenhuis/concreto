<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private PushNotificationService $pushService,
    ) {}

    // ─── Order Lifecycle: Customer Notifications ──────────────────────

    public function orderConfirmed(Order $order): void
    {
        $order->load('customer.user', 'items.product', 'deliveryAddress');

        // Email customer
        $this->sendOrderEmail($order, 'order_confirmed', 'Order Confirmed', 'emails.order-placed');

        // Email + push admin
        $this->notifyAdmin(
            $order,
            'admin_order_confirmed',
            "New Order {$order->order_number}",
            'emails.admin-new-order'
        );

        $this->pushToAdmins(
            'New Order Received',
            "Order {$order->order_number} — R" . number_format($order->total, 2),
            ['type' => 'order_confirmed', 'order_id' => $order->id]
        );

        // In-app notification for admins
        $this->storeAdminNotification('order_confirmed', [
            'title' => 'New Order Received',
            'body' => "Order {$order->order_number} from {$order->customer->user->name} — R" . number_format($order->total, 2),
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    public function paymentReceived(Order $order): void
    {
        $order->load('customer.user', 'items.product', 'deliveryAddress');

        // Email customer
        $this->sendOrderEmail($order, 'payment_received', 'Payment Received', 'emails.payment-received');

        // Email + push admin
        $this->notifyAdmin(
            $order,
            'admin_payment_received',
            "Payment Received — {$order->order_number}",
            'emails.admin-new-order'
        );

        $this->pushToAdmins(
            'Payment Received',
            "Order {$order->order_number} — R" . number_format($order->total, 2) . ' paid',
            ['type' => 'payment_received', 'order_id' => $order->id]
        );

        // In-app notification for admins
        $this->storeAdminNotification('payment_received', [
            'title' => 'Payment Received',
            'body' => "Payment for order {$order->order_number} — R" . number_format($order->total, 2),
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        // Notify vendors — order is ready to process
        $this->notifyVendors($order);
    }

    /**
     * Called when an ACCOUNT order goes straight to PLACED
     * or when a manual payment transitions an order to PLACED.
     */
    public function orderPlacedForProcessing(Order $order): void
    {
        $order->load('customer.user', 'items.product', 'deliveryAddress');

        // Email + push admin
        $this->notifyAdmin(
            $order,
            'admin_order_placed',
            "Order Ready for Processing — {$order->order_number}",
            'emails.admin-new-order'
        );

        $this->pushToAdmins(
            'Order Ready for Processing',
            "Order {$order->order_number} (Account) — R" . number_format($order->total, 2),
            ['type' => 'order_placed', 'order_id' => $order->id]
        );

        // In-app notification for admins
        $this->storeAdminNotification('order_placed', [
            'title' => 'Order Ready for Processing',
            'body' => "Account order {$order->order_number} from {$order->customer->user->name} — R" . number_format($order->total, 2),
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        // Notify vendors
        $this->notifyVendors($order);
    }

    public function orderLoaded(Order $order): void
    {
        $order->load('customer.user');

        $this->sendOrderEmail($order, 'order_loaded', 'Order Loaded', 'emails.order-loaded');

        // Push to customer
        $this->pushToCustomer($order, 'Order Loaded', "Your order {$order->order_number} has been loaded and will be dispatched soon.");

        $this->storeCustomerNotification($order, 'order_loaded', [
            'title' => 'Order Loaded',
            'body' => "Your order {$order->order_number} has been loaded.",
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    public function orderEnRoute(Order $order): void
    {
        $order->load('customer.user');

        $this->sendOrderEmail($order, 'order_en_route', 'Your Order is On the Way', 'emails.order-en-route');

        // Push to customer
        $this->pushToCustomer($order, 'Order On the Way', "Your order {$order->order_number} is on the way!");

        $this->storeCustomerNotification($order, 'order_en_route', [
            'title' => 'Order On the Way',
            'body' => "Your order {$order->order_number} is on the way!",
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    public function orderDelivered(Order $order): void
    {
        $order->load('customer.user');

        $this->sendOrderEmail($order, 'order_delivered', 'Order Delivered', 'emails.order-delivered');

        // Push to customer
        $this->pushToCustomer($order, 'Order Delivered', "Your order {$order->order_number} has been delivered.");

        $this->storeCustomerNotification($order, 'order_delivered', [
            'title' => 'Order Delivered',
            'body' => "Your order {$order->order_number} has been delivered.",
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        // Notify admin of delivery
        $this->pushToAdmins(
            'Order Delivered',
            "Order {$order->order_number} delivered to {$order->customer->user->name}",
            ['type' => 'order_delivered', 'order_id' => $order->id]
        );

        $this->storeAdminNotification('order_delivered', [
            'title' => 'Order Delivered',
            'body' => "Order {$order->order_number} delivered to {$order->customer->user->name}",
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    public function driverAssigned(Order $order): void
    {
        $order->load('customer.user', 'deliveryAddress', 'driver');
        $driver = $order->driver;

        if (!$driver) {
            return;
        }

        // Push notification to driver
        try {
            $this->pushService->sendToUser(
                $driver,
                'New Delivery Assigned',
                'Order ' . $order->order_number . ' — ' . ($order->deliveryAddress?->city ?? 'N/A'),
                ['type' => 'driver_assigned', 'order_id' => $order->id]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to push to driver', [
                'driver_id' => $driver->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        // In-app notification for driver
        $driver->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'driver_assigned',
            'data' => json_encode([
                'title' => 'New Delivery Assigned',
                'body' => "Order {$order->order_number} assigned to you.",
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ]);
    }

    // ─── Vendor Notifications ─────────────────────────────────────────

    private function notifyVendors(Order $order): void
    {
        $order->load('items.product.vendor');

        // Collect unique active vendors from order items
        $vendors = $order->items
            ->map(fn($item) => $item->product->vendor)
            ->filter(fn($vendor) => $vendor && $vendor->is_active)
            ->unique('id');

        // If no products are linked to vendors, notify ALL active vendors
        if ($vendors->isEmpty()) {
            $vendors = Vendor::where('is_active', true)->get();
        }

        foreach ($vendors as $vendor) {
            $this->sendVendorOrderEmail($order, $vendor);
            $this->pushToVendor($order, $vendor);
            $this->storeVendorNotification($order, $vendor);
        }
    }

    private function sendVendorOrderEmail(Order $order, Vendor $vendor): void
    {
        $settings = Setting::getAll();
        $subject = "New Order to Process — {$order->order_number}";

        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_ATTEMPTS) {
            $attempt++;
            try {
                Mail::send('emails.vendor-order', [
                    'order' => $order,
                    'vendor' => $vendor,
                    'settings' => $settings,
                ], function ($message) use ($vendor, $subject) {
                    $message->to($vendor->email)->subject($subject);
                });

                NotificationLog::create([
                    'channel' => 'email',
                    'recipient' => $vendor->email,
                    'template_key' => 'vendor_order',
                    'subject' => $subject,
                    'related_type' => 'Order',
                    'related_id' => $order->id,
                    'status' => 'sent',
                    'attempts' => $attempt,
                    'sent_at' => now(),
                ]);
                return;
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Vendor notification attempt {$attempt} failed", [
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                if ($attempt < self::MAX_ATTEMPTS) {
                    usleep(pow(2, $attempt - 1) * 1000000);
                }
            }
        }

        NotificationLog::create([
            'channel' => 'email',
            'recipient' => $vendor->email,
            'template_key' => 'vendor_order',
            'subject' => $subject,
            'related_type' => 'Order',
            'related_id' => $order->id,
            'status' => 'failed',
            'attempts' => $attempt,
            'error_message' => $lastError?->getMessage(),
        ]);
    }

    private function pushToVendor(Order $order, Vendor $vendor): void
    {
        if (!$vendor->user_id) {
            return;
        }

        try {
            $user = $vendor->user;
            if ($user) {
                $this->pushService->sendToUser(
                    $user,
                    'New Order to Process',
                    "Order {$order->order_number} — R" . number_format($order->total, 2),
                    ['type' => 'vendor_order', 'order_id' => $order->id]
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to push to vendor', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function storeVendorNotification(Order $order, Vendor $vendor): void
    {
        if (!$vendor->user_id || !$vendor->user) {
            return;
        }

        $vendor->user->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'vendor_order',
            'data' => json_encode([
                'title' => 'New Order to Process',
                'body' => "Order {$order->order_number} — R" . number_format($order->total, 2),
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]),
        ]);
    }

    // ─── Admin Notifications ──────────────────────────────────────────

    private function notifyAdmin(Order $order, string $templateKey, string $subject, string $view): void
    {
        $adminEmail = Setting::get('admin_order_notification_email');
        if (!$adminEmail) {
            $admin = User::where('role', 'admin')->where('is_active', true)->first();
            $adminEmail = $admin?->email;
        }

        if (!$adminEmail) {
            return;
        }

        $settings = Setting::getAll();

        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_ATTEMPTS) {
            $attempt++;
            try {
                Mail::send($view, [
                    'order' => $order,
                    'settings' => $settings,
                ], function ($message) use ($adminEmail, $subject) {
                    $message->to($adminEmail)->subject($subject);
                });

                NotificationLog::create([
                    'channel' => 'email',
                    'recipient' => $adminEmail,
                    'template_key' => $templateKey,
                    'subject' => $subject,
                    'related_type' => 'Order',
                    'related_id' => $order->id,
                    'status' => 'sent',
                    'attempts' => $attempt,
                    'sent_at' => now(),
                ]);
                return;
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Admin notification attempt {$attempt} failed for {$templateKey}", [
                    'error' => $e->getMessage(),
                ]);
                if ($attempt < self::MAX_ATTEMPTS) {
                    usleep(pow(2, $attempt - 1) * 1000000);
                }
            }
        }

        NotificationLog::create([
            'channel' => 'email',
            'recipient' => $adminEmail,
            'template_key' => $templateKey,
            'subject' => $subject,
            'related_type' => 'Order',
            'related_id' => $order->id,
            'status' => 'failed',
            'attempts' => $attempt,
            'error_message' => $lastError?->getMessage(),
        ]);
    }

    private function pushToAdmins(string $title, string $body, array $data = []): void
    {
        try {
            $admins = User::where('role', 'admin')
                ->where('is_active', true)
                ->whereHas('deviceTokens', fn($q) => $q->where('is_active', true))
                ->get();

            foreach ($admins as $admin) {
                $this->pushService->sendToUser($admin, $title, $body, $data);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to push to admins', ['error' => $e->getMessage()]);
        }
    }

    private function storeAdminNotification(string $type, array $data): void
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        foreach ($admins as $admin) {
            $admin->notifications()->create([
                'id' => Str::uuid(),
                'type' => $type,
                'data' => json_encode($data),
            ]);
        }
    }

    // ─── Customer Push + In-App ───────────────────────────────────────

    private function pushToCustomer(Order $order, string $title, string $body): void
    {
        try {
            $user = $order->customer->user;
            if ($user) {
                $this->pushService->sendToUser($user, $title, $body, [
                    'type' => 'order_update',
                    'order_id' => $order->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to push to customer', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function storeCustomerNotification(Order $order, string $type, array $data): void
    {
        $user = $order->customer->user ?? null;
        if (!$user) {
            return;
        }

        $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => $type,
            'data' => json_encode($data),
        ]);
    }

    // ─── Core Email Sender (customer order emails) ───────────────────

    private function sendOrderEmail(Order $order, string $templateKey, string $subject, string $view): void
    {
        $order->load('customer.user');
        $email = $order->customer->user->email;
        $settings = Setting::getAll();
        $fullSubject = "{$subject} - {$order->order_number}";

        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_ATTEMPTS) {
            $attempt++;
            try {
                Mail::send($view, [
                    'order' => $order,
                    'settings' => $settings,
                ], function ($message) use ($email, $fullSubject) {
                    $message->to($email)->subject($fullSubject);
                });

                NotificationLog::create([
                    'channel' => 'email',
                    'recipient' => $email,
                    'template_key' => $templateKey,
                    'subject' => $fullSubject,
                    'related_type' => 'Order',
                    'related_id' => $order->id,
                    'status' => 'sent',
                    'attempts' => $attempt,
                    'sent_at' => now(),
                ]);
                return;
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("Notification send attempt {$attempt} failed for {$templateKey}", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                if ($attempt < self::MAX_ATTEMPTS) {
                    usleep(pow(2, $attempt - 1) * 1000000);
                }
            }
        }

        Log::error("Failed to send {$templateKey} notification after " . self::MAX_ATTEMPTS . " attempts", [
            'order_id' => $order->id,
            'error' => $lastError?->getMessage(),
        ]);

        NotificationLog::create([
            'channel' => 'email',
            'recipient' => $email ?? '',
            'template_key' => $templateKey,
            'subject' => $fullSubject,
            'related_type' => 'Order',
            'related_id' => $order->id,
            'status' => 'failed',
            'attempts' => $attempt,
            'error_message' => $lastError?->getMessage(),
        ]);
    }
}
