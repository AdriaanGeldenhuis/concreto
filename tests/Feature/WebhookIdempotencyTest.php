<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set webhook secret for testing
        config(['yoco.webhook_secret' => 'test-secret']);
    }

    private function webhookPayload(string $eventType, string $eventId, string $checkoutId): array
    {
        return [
            'id' => $eventId,
            'type' => $eventType,
            'payload' => [
                'id' => 'pay_' . $eventId,
                'checkoutId' => $checkoutId,
                'metadata' => [
                    'checkoutId' => $checkoutId,
                ],
            ],
        ];
    }

    private function signPayload(array $payload): string
    {
        $json = json_encode($payload);
        return hash_hmac('sha256', $json, 'test-secret');
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'PENDING_PAYMENT',
        ]);
        Payment::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'checkout_id' => 'checkout_123',
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $payload = $this->webhookPayload('payment.succeeded', 'evt_001', 'checkout_123');
        $signature = $this->signPayload($payload);
        $json = json_encode($payload);

        // First call
        $response1 = $this->postJson('/webhooks/yoco', $payload, [
            'X-Yoco-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]);
        $response1->assertOk();

        // Second call (same event)
        $response2 = $this->call('POST', '/webhooks/yoco', [], [], [], [
            'HTTP_X-Yoco-Signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $json);
        $response2->assertOk();
        $response2->assertJson(['status' => 'already_processed']);

        // Only one event recorded
        $this->assertEquals(1, PaymentEvent::where('event_id', 'evt_001')->count());

        // Payment completed only once
        $payment = Payment::where('checkout_id', 'checkout_123')->first();
        $this->assertEquals('completed', $payment->status);
    }

    public function test_invalid_signature_rejected(): void
    {
        $payload = $this->webhookPayload('payment.succeeded', 'evt_bad', 'checkout_bad');

        $response = $this->postJson('/webhooks/yoco', $payload, [
            'X-Yoco-Signature' => 'invalid-signature',
        ]);

        $response->assertStatus(401);
    }

    public function test_payment_success_updates_order_to_placed(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'PENDING_PAYMENT',
        ]);
        Payment::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'checkout_id' => 'checkout_456',
            'amount' => $order->total,
            'status' => 'pending',
        ]);

        $payload = $this->webhookPayload('payment.succeeded', 'evt_002', 'checkout_456');
        $signature = $this->signPayload($payload);

        $response = $this->postJson('/webhooks/yoco', $payload, [
            'X-Yoco-Signature' => $signature,
        ]);

        $response->assertOk();
        $order->refresh();
        $this->assertEquals('PLACED', $order->status);
    }
}
