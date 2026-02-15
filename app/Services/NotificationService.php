<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function orderConfirmed(Order $order): void
    {
        $this->sendOrderEmail($order, 'order_confirmed', 'Order Confirmed', 'emails.order-placed');
    }

    public function paymentReceived(Order $order): void
    {
        $this->sendOrderEmail($order, 'payment_received', 'Payment Received', 'emails.payment-received');
    }

    public function orderLoaded(Order $order): void
    {
        $this->sendOrderEmail($order, 'order_loaded', 'Order Loaded', 'emails.order-loaded');
    }

    public function orderEnRoute(Order $order): void
    {
        $this->sendOrderEmail($order, 'order_en_route', 'Your Order is On the Way', 'emails.order-en-route');
    }

    public function orderDelivered(Order $order): void
    {
        $this->sendOrderEmail($order, 'order_delivered', 'Order Delivered', 'emails.order-delivered');
    }

    private function sendOrderEmail(Order $order, string $templateKey, string $subject, string $view): void
    {
        try {
            $order->load('customer.user');
            $email = $order->customer->user->email;
            $settings = Setting::getAll();

            $fullSubject = "{$subject} - {$order->order_number}";

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
                'attempts' => 1,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send {$templateKey} notification", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            NotificationLog::create([
                'channel' => 'email',
                'recipient' => $order->customer?->user?->email ?? '',
                'template_key' => $templateKey,
                'subject' => "{$subject} - {$order->order_number}",
                'related_type' => 'Order',
                'related_id' => $order->id,
                'status' => 'failed',
                'attempts' => 1,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
