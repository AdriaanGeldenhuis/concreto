<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
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
                <h2>Verify Your Identity</h2>
                <p class="text-muted text-small">A 6-digit code has been sent to your email</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <input type="text" name="code" class="form-control" required autofocus
                           placeholder="Enter 6-digit code"
                           maxlength="6" pattern="[0-9]{6}"
                           style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: bold;">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Verify</button>
            </form>

            <div class="divider"></div>

            <div class="text-center">
                <form method="POST" action="{{ route('two-factor.resend') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-small">Resend Code</button>
                </form>
                <span class="text-muted text-small">|</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-small">Logout</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
