<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\NotificationService;
use App\Services\YocoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class YocoWebhookController extends Controller
{
    public function __construct(
        private YocoService $yocoService,
        private NotificationService $notificationService,
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Yoco-Signature', '');

        if (!$this->yocoService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Yoco webhook signature verification failed.', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        if (!$data) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $eventId = $data['id'] ?? $data['payload']['id'] ?? null;
        $eventType = $data['type'] ?? '';

        if (!$eventId) {
            Log::warning('Yoco webhook missing event ID.', ['data' => $data]);
            return response()->json(['error' => 'Missing event ID'], 400);
        }

        // Idempotency: check if this event was already processed
        $existingEvent = PaymentEvent::where('event_id', $eventId)->first();
        if ($existingEvent) {
            return response()->json(['status' => 'already_processed']);
        }

        if ($eventType === 'payment.succeeded') {
            $this->handlePaymentSuccess($data, $eventId);
        } elseif ($eventType === 'payment.failed') {
            $this->handlePaymentFailed($data, $eventId);
        } else {
            // Store unhandled events for debugging
            PaymentEvent::create([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => $data,
                'status' => 'ignored',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentSuccess(array $data, string $eventId): void
    {
        DB::transaction(function () use ($data, $eventId) {
            $checkoutId = $data['payload']['metadata']['checkoutId']
                ?? $data['payload']['checkoutId']
                ?? null;

            $payment = $checkoutId
                ? Payment::lockForUpdate()->where('checkout_id', $checkoutId)->first()
                : null;

            // Store the raw event
            PaymentEvent::create([
                'event_id' => $eventId,
                'event_type' => 'payment.succeeded',
                'payment_id' => $payment?->id,
                'payload' => $data,
                'status' => $payment ? 'processed' : 'orphaned',
            ]);

            if (!$payment) {
                Log::warning('Yoco payment.succeeded: no matching payment found.', [
                    'checkout_id' => $checkoutId,
                    'event_id' => $eventId,
                ]);
                return;
            }

            // Skip if already completed
            if ($payment->status === 'completed') {
                return;
            }

            // Verify payment amount matches order total
            if ($payment->order) {
                $webhookAmountCents = $data['payload']['amount'] ?? null;
                if ($webhookAmountCents !== null) {
                    $webhookAmount = $webhookAmountCents / 100;
                    if (abs($webhookAmount - (float) $payment->order->total) > 0.01) {
                        Log::error('Yoco payment amount mismatch', [
                            'event_id' => $eventId,
                            'webhook_amount' => $webhookAmount,
                            'order_total' => $payment->order->total,
                            'order_id' => $payment->order->id,
                        ]);
                        return;
                    }
                }
            }

            $payment->update([
                'status' => 'completed',
                'reference' => $data['payload']['id'] ?? null,
            ]);

            if ($payment->order && $payment->order->status === 'PENDING_PAYMENT') {
                $payment->order->update(['status' => 'PLACED']);

                AuditLog::log('payment_completed', 'Order', $payment->order->id, [
                    'amount' => $payment->amount,
                    'order_total' => $payment->order->total,
                    'provider' => 'yoco',
                    'event_id' => $eventId,
                ]);

                // Notify customer of payment receipt
                try {
                    $this->notificationService->paymentReceived($payment->order);
                } catch (\Exception $e) {
                    Log::warning('Failed to send payment notification', [
                        'order_id' => $payment->order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    private function handlePaymentFailed(array $data, string $eventId): void
    {
        DB::transaction(function () use ($data, $eventId) {
            $checkoutId = $data['payload']['metadata']['checkoutId']
                ?? $data['payload']['checkoutId']
                ?? null;

            $payment = $checkoutId
                ? Payment::lockForUpdate()->where('checkout_id', $checkoutId)->first()
                : null;

            PaymentEvent::create([
                'event_id' => $eventId,
                'event_type' => 'payment.failed',
                'payment_id' => $payment?->id,
                'payload' => $data,
                'status' => $payment ? 'processed' : 'orphaned',
            ]);

            if ($payment && $payment->status !== 'completed') {
                $payment->update(['status' => 'failed']);
            }
        });
    }
}
