<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YocoService
{
    private string $secretKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->secretKey = config('yoco.secret_key');
        $this->apiUrl = config('yoco.api_url');
    }

    public function createCheckout(array $data): array
    {
        $response = Http::withToken($this->secretKey)
            ->post($this->apiUrl . 'checkouts', [
                'amount' => (int) ($data['amount'] * 100), // Yoco uses cents
                'currency' => 'ZAR',
                'successUrl' => $data['success_url'],
                'cancelUrl' => $data['cancel_url'],
                'failureUrl' => $data['failure_url'] ?? $data['cancel_url'],
                'metadata' => $data['metadata'] ?? [],
            ]);

        if ($response->failed()) {
            Log::error('Yoco checkout creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to create Yoco checkout session.');
        }

        return $response->json();
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('yoco.webhook_secret');
        if (!$secret) {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }
}
