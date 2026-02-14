<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Services\YocoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YocoWebhookController extends Controller
{
    public function __construct(private YocoService $yocoService) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Yoco-Signature', '');

        if (!$this->yocoService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Yoco webhook signature verification failed.');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        $event = $data['type'] ?? '';

        if ($event === 'payment.succeeded') {
            $this->handlePaymentSuccess($data);
        } elseif ($event === 'payment.failed') {
            $this->handlePaymentFailed($data);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handlePaymentSuccess(array $data): void
    {
        $checkoutId = $data['payload']['metadata']['checkoutId'] ?? null;
        $orderId = $data['payload']['metadata']['order_id'] ?? null;

        $payment = Payment::where('checkout_id', $checkoutId)->first();
        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'reference' => $data['payload']['id'] ?? null,
            ]);

            if ($payment->order) {
                $payment->order->update(['status' => 'PLACED']);
                AuditLog::log('payment_completed', 'Order', $payment->order->id, [
                    'amount' => $payment->amount,
                    'provider' => 'yoco',
                ]);
            }
        }
    }

    private function handlePaymentFailed(array $data): void
    {
        $checkoutId = $data['payload']['metadata']['checkoutId'] ?? null;

        $payment = Payment::where('checkout_id', $checkoutId)->first();
        if ($payment) {
            $payment->update(['status' => 'failed']);
        }
    }
}
