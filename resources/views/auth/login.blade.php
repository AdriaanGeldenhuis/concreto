<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
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
                <h2>Welcome Back</h2>
                <p class="text-muted text-small">Sign in to your account</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}{{ request('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember" class="text-small">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
            </form>

            <div class="divider"></div>

            <p class="text-center text-small text-muted mb-0">
                Don't have an account? <a href="{{ route('register') }}">Create one</a>
            </p>
        </div>
    </div>
</body>
</html>
