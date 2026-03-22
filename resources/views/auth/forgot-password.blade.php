<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    <link rel="stylesheet" href="/css/app.css">
    @if(isset($siteSettings))
    <style>
        :root {
            @if(!empty($siteSettings['primary_color']))--primary: {{ $siteSettings['primary_color'] }};@endif
            @if(!empty($siteSettings['secondary_color']))--secondary: {{ $siteSettings['secondary_color'] }};@endif
        }
    </style>
    @endif
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="logo">
                <img src="/assets/logo/concreto.webp" alt="Concreto">
                <h2>Reset Password</h2>
                <p class="text-muted text-small">Enter your email and we'll send you a reset link</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Link</button>
            </form>

            <div class="divider"></div>

            <p class="text-center text-small text-muted mb-0">
                Remember your password? <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
