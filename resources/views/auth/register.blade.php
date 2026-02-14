<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
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
                <h2>Create Account</h2>
                <p class="text-muted text-small">Sign up to start ordering</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="John Smith">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="082 123 4567">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required placeholder="Repeat password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-1">Create Account</button>
            </form>

            <div class="divider"></div>

            <p class="text-center text-small text-muted mb-0">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
