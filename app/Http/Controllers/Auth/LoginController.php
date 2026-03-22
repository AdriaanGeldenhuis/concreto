<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->withInput($request->only('email'));
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // Block inactive users
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact your administrator.',
                ])->withInput($request->only('email'));
            }

            RateLimiter::clear($key);
            $request->session()->regenerate();

            // Redirect to intended URL if set (e.g. from cart checkout)
            // Only allow local redirects to prevent open redirect attacks
            if ($request->has('redirect')) {
                $redirect = $request->input('redirect');
                if (str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
                    return redirect($redirect);
                }
            }

            $user = Auth::user();
            return match ($user->role) {
                'admin', 'staff' => redirect()->route('admin.dashboard'),
                'driver' => redirect()->route('driver.dashboard'),
                'customer' => redirect()->route('customer.dashboard'),
                default => redirect('/'),
            };
        }

        RateLimiter::hit($key, 300);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
