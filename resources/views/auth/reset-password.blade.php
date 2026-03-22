<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
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
                <h2>Set New Password</h2>
                <p class="text-muted text-small">Enter your new password below</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="{{ $email }}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required autofocus placeholder="Minimum 8 characters">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required placeholder="Repeat your password">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
            </form>

            <div class="divider"></div>

            <p class="text-center text-small text-muted mb-0">
                Remember your password? <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
