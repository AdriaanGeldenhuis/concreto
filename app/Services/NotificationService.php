<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    private const MAX_ATTEMPTS = 3;

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
                    usleep(pow(2, $attempt - 1) * 1000000); // 1s, 2s backoff
                }
            }
        }

        // All attempts failed
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
