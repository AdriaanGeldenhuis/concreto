<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    public function challenge(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return redirect('/');
        }

        if ($request->session()->get('two_factor_verified')) {
            return redirect('/');
        }

        // Send code if not already sent in this session
        if (!$request->session()->has('two_factor_code')) {
            $this->sendCode($request);
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $sessionCode = $request->session()->get('two_factor_code');
        $expiresAt = $request->session()->get('two_factor_expires_at');

        if (!$sessionCode || !$expiresAt) {
            return back()->with('error', 'No verification code found. Please request a new one.');
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget(['two_factor_code', 'two_factor_expires_at']);
            return back()->with('error', 'Code has expired. A new code has been sent.');
        }

        if ($request->code !== $sessionCode) {
            return back()->with('error', 'Invalid verification code.');
        }

        // Code is valid
        $request->session()->put('two_factor_verified', true);
        $request->session()->forget(['two_factor_code', 'two_factor_expires_at']);

        AuditLog::log('two_factor_verified', 'User', $request->user()->id);

        $user = $request->user();
        return match ($user->role) {
            'admin', 'staff' => redirect()->route('admin.dashboard'),
            'driver' => redirect()->route('driver.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default => redirect('/'),
        };
    }

    public function resend(Request $request)
    {
        $this->sendCode($request);
        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    private function sendCode(Request $request): void
    {
        $user = $request->user();
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('two_factor_code', $code);
        $request->session()->put('two_factor_expires_at', now()->addMinutes(10)->timestamp);

        try {
            Mail::send('emails.two-factor-code', [
                'user' => $user,
                'code' => $code,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Your Login Verification Code - Concreto');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send 2FA code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
