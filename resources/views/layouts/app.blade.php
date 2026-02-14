<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#e67e22' }}">
    <meta name="description" content="{{ $siteSettings['hero_subtitle'] ?? 'Quality building materials delivered to your site.' }}">
    <title>@yield('title', 'Concreto') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/logo/concreto.webp">
    <link rel="apple-touch-icon" href="/assets/logo/concreto.webp">
    @if(isset($siteSettings))
    <style>
        :root {
            @if(!empty($siteSettings['primary_color']))--primary: {{ $siteSettings['primary_color'] }};@endif
            @if(!empty($siteSettings['secondary_color']))--secondary: {{ $siteSettings['secondary_color'] }};@endif
            @if(!empty($siteSettings['bg_color']))--bg: {{ $siteSettings['bg_color'] }};@endif
            @if(!empty($siteSettings['text_color']))--text: {{ $siteSettings['text_color'] }};@endif
            @if(!empty($siteSettings['card_color']))--card: {{ $siteSettings['card_color'] }};@endif
        }
    </style>
    @endif
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand">
                <img src="/assets/logo/concreto.webp" alt="Concreto">
                <span>{{ $siteSettings['company_name'] ?? 'Concreto' }}</span>
            </a>
            <button class="navbar-toggle" aria-label="Toggle menu">&#9776;</button>
            <div class="navbar-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('products') }}" class="{{ request()->routeIs('products*') ? 'active' : '' }}">Products</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm" style="border-color:rgba(255,255,255,0.4);color:#fff;">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                @else
                    @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @elseif(auth()->user()->isDriver())
                        <a href="{{ route('driver.dashboard') }}">My Jobs</a>
                    @else
                        <a href="{{ route('customer.dashboard') }}">My Account</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="container mt-1">
            <div class="alert alert-success" data-dismiss>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="container mt-1">
            <div class="alert alert-danger" data-dismiss>{{ session('error') }}</div>
        </div>
    @endif
    @if(session('info'))
        <div class="container mt-1">
            <div class="alert alert-info" data-dismiss>{{ session('info') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4>{{ $siteSettings['company_name'] ?? 'Concreto' }}</h4>
                    <p>{{ $siteSettings['footer_about'] ?? 'Quality building materials delivered to your site.' }}</p>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <p><a href="{{ route('products') }}">Products</a></p>
                    <p><a href="{{ route('request-quote') }}">Request Quote</a></p>
                    <p><a href="{{ route('contact') }}">Contact Us</a></p>
                    <p><a href="{{ route('terms') }}">Terms & Conditions</a></p>
                    <p><a href="{{ route('privacy') }}">Privacy Policy</a></p>
                </div>
                <div>
                    <h4>Contact</h4>
                    @if(!empty($siteSettings['contact_email']))<p>{{ $siteSettings['contact_email'] }}</p>@endif
                    @if(!empty($siteSettings['contact_phone']))<p>{{ $siteSettings['contact_phone'] }}</p>@endif
                    @if(!empty($siteSettings['contact_address']))<p>{{ $siteSettings['contact_address'] }}</p>@endif
                    @if(!empty($siteSettings['social_facebook']))<p><a href="{{ $siteSettings['social_facebook'] }}">Facebook</a></p>@endif
                    @if(!empty($siteSettings['social_instagram']))<p><a href="{{ $siteSettings['social_instagram'] }}">Instagram</a></p>@endif
                </div>
            </div>
            <div class="footer-bottom">
                <p>{!! $siteSettings['footer_text'] ?? '&copy; ' . date('Y') . ' Concreto. All rights reserved.' !!}</p>
            </div>
        </div>
    </footer>

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>
