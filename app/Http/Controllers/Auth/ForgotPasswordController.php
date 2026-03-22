<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Always show success to prevent email enumeration
        if (!$user) {
            return back()->with('success', 'If an account with that email exists, we have sent a password reset link.');
        }

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = url("/reset-password/{$token}?email=" . urlencode($request->email));

        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Reset Your Password - Concreto');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send password reset email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'If an account with that email exists, we have sent a password reset link.');
    }
}
