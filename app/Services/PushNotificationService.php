<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private ?string $serverKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
    }

    /**
     * Send push notification to a single device token.
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->serverKey) {
            Log::debug('FCM server key not configured, skipping push notification.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // Handle invalid tokens
                if (isset($result['results'][0]['error'])) {
                    $error = $result['results'][0]['error'];
                    if (in_array($error, ['NotRegistered', 'InvalidRegistration'])) {
                        DeviceToken::where('token', $token)->update(['is_active' => false]);
                        Log::info('Deactivated invalid FCM token', ['token' => substr($token, 0, 20) . '...']);
                    }
                    return false;
                }

                return ($result['success'] ?? 0) > 0;
            }

            Log::warning('FCM request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM push notification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send push notification to all active devices of a user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        $tokens = $user->activeDeviceTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $sent = 0;
        foreach ($tokens as $token) {
            if ($this->send($token, $title, $body, $data)) {
                DeviceToken::where('token', $token)->update(['last_used_at' => now()]);
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Send push notification to multiple users.
     */
    public function sendToUsers(array $users, string $title, string $body, array $data = []): int
    {
        $totalSent = 0;
        foreach ($users as $user) {
            if ($user instanceof User) {
                $totalSent += $this->sendToUser($user, $title, $body, $data);
            }
        }
        return $totalSent;
    }
}
