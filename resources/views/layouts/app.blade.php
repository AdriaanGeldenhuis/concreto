<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Concreto') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    @if(!empty($siteSettings['site_favicon']))
        <link rel="icon" href="{{ asset('storage/' . $siteSettings['site_favicon']) }}">
    @endif
    @if(!empty($siteSettings['google_font_url']))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $siteSettings['google_font_url'] }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="/css/app.css">
    @if(isset($siteSettings))
    <style>
        :root {
            @if(!empty($siteSettings['primary_color']))--primary: {{ $siteSettings['primary_color'] }};@endif
            @if(!empty($siteSettings['primary_dark_color']))--primary-dark: {{ $siteSettings['primary_dark_color'] }};@endif
            @if(!empty($siteSettings['secondary_color']))--secondary: {{ $siteSettings['secondary_color'] }};@endif
            @if(!empty($siteSettings['bg_color']))--bg: {{ $siteSettings['bg_color'] }};@endif
            @if(!empty($siteSettings['text_color']))--text: {{ $siteSettings['text_color'] }};@endif
            @if(!empty($siteSettings['text_light_color']))--text-light: {{ $siteSettings['text_light_color'] }};@endif
            @if(!empty($siteSettings['card_color']))--card: {{ $siteSettings['card_color'] }};@endif
            @if(!empty($siteSettings['border_color']))--border: {{ $siteSettings['border_color'] }};@endif
            @if(!empty($siteSettings['success_color']))--success: {{ $siteSettings['success_color'] }};@endif
            @if(!empty($siteSettings['danger_color']))--danger: {{ $siteSettings['danger_color'] }};@endif
            @if(!empty($siteSettings['warning_color']))--warning: {{ $siteSettings['warning_color'] }};@endif
            @if(!empty($siteSettings['info_color']))--info: {{ $siteSettings['info_color'] }};@endif
            @if(!empty($siteSettings['border_radius']))--radius: {{ $siteSettings['border_radius'] }}px;@endif
            @if(!empty($siteSettings['container_max_width']))--container-max-width: {{ $siteSettings['container_max_width'] }}px;@endif
            @if(!empty($siteSettings['font_family']) && $siteSettings['font_family'] !== 'system')--font-family: {{ $siteSettings['font_family'] }};@endif
            @if(!empty($siteSettings['heading_font_family']) && $siteSettings['heading_font_family'] !== 'inherit')--heading-font-family: {{ $siteSettings['heading_font_family'] }};@endif
            @if(!empty($siteSettings['font_size_base']))--font-size-base: {{ $siteSettings['font_size_base'] }}px;@endif
            @if(!empty($siteSettings['font_size_h1']))--font-size-h1: {{ $siteSettings['font_size_h1'] }}rem;@endif
            @if(!empty($siteSettings['font_size_h2']))--font-size-h2: {{ $siteSettings['font_size_h2'] }}rem;@endif
            @if(!empty($siteSettings['font_size_h3']))--font-size-h3: {{ $siteSettings['font_size_h3'] }}rem;@endif
            @if(!empty($siteSettings['font_weight_body']))--font-weight-body: {{ $siteSettings['font_weight_body'] }};@endif
            @if(!empty($siteSettings['font_weight_heading']))--font-weight-heading: {{ $siteSettings['font_weight_heading'] }};@endif
            @if(!empty($siteSettings['line_height']))--line-height: {{ $siteSettings['line_height'] }};@endif
            @if(!empty($siteSettings['letter_spacing']))--letter-spacing: {{ $siteSettings['letter_spacing'] }}px;@endif
            @if(!empty($siteSettings['btn_border_radius']))--btn-radius: {{ $siteSettings['btn_border_radius'] }}px;@endif
            @if(!empty($siteSettings['btn_text_transform']))--btn-text-transform: {{ $siteSettings['btn_text_transform'] }};@endif
            @if(!empty($siteSettings['navbar_bg_color']))--navbar-bg: {{ $siteSettings['navbar_bg_color'] }};@endif
            @if(!empty($siteSettings['navbar_text_color']))--navbar-text: {{ $siteSettings['navbar_text_color'] }};@endif
            @if(!empty($siteSettings['footer_bg_color']))--footer-bg: {{ $siteSettings['footer_bg_color'] }};@endif
            @if(!empty($siteSettings['footer_text_color']))--footer-text: {{ $siteSettings['footer_text_color'] }};@endif
        }
        @if(!empty($siteSettings['card_shadow']))
        @php $shadowMap = ['none'=>'none','light'=>'0 1px 4px rgba(0,0,0,0.04)','medium'=>'0 2px 8px rgba(0,0,0,0.08)','heavy'=>'0 4px 20px rgba(0,0,0,0.15)']; @endphp
        :root { --shadow: {{ $shadowMap[$siteSettings['card_shadow']] ?? '0 2px 8px rgba(0,0,0,0.08)' }}; }
        @endif
        @if(!empty($siteSettings['bg_image_desktop']))
        .hero { background-image: url('{{ asset('storage/' . $siteSettings['bg_image_desktop']) }}'); background-size: {{ $siteSettings['bg_size'] ?? 'cover' }}; background-position: {{ $siteSettings['bg_position'] ?? 'center center' }}; background-repeat: no-repeat; }
        .hero::before { content:''; position:absolute; inset:0; background: {{ $siteSettings['bg_overlay_color'] ?? '#000000' }}; opacity: {{ ($siteSettings['bg_overlay_opacity'] ?? 40) / 100 }}; }
        .hero { position:relative; }
        .hero > * { position:relative; z-index:1; }
        @endif
        @if(!empty($siteSettings['bg_image_tablet']))
        @media (max-width: 991px) {
            .hero { background-image: url('{{ asset('storage/' . $siteSettings['bg_image_tablet']) }}'); }
        }
        @endif
        @if(!empty($siteSettings['bg_image_mobile']))
        @media (max-width: 767px) {
            .hero { background-image: url('{{ asset('storage/' . $siteSettings['bg_image_mobile']) }}'); }
        }
        @endif
    </style>
    @endif
    @if(!empty($siteSettings['custom_css']))
    <style>{{ $siteSettings['custom_css'] }}</style>
    @endif
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="{{ route('home') }}" class="navbar-brand">
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ asset('storage/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}">
                @else
                    <img src="/assets/logo/concreto.webp" alt="Concreto">
                @endif
                <span>{{ $siteSettings['company_name'] ?? 'Concreto' }}</span>
            </a>
            <button class="navbar-toggle" aria-label="Toggle menu">&#9776;</button>
            <div class="navbar-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('products') }}" class="{{ request()->routeIs('products*') ? 'active' : '' }}">Products</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Login</a>
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
                    <p><a href="{{ route('contact') }}">Contact Us</a></p>
                    <p><a href="{{ route('terms') }}">Terms & Conditions</a></p>
                    <p><a href="{{ route('privacy') }}">Privacy Policy</a></p>
                </div>
                <div>
                    <h4>Contact</h4>
                    <p>{{ $siteSettings['contact_email'] ?? 'orders@concreto.co.za' }}</p>
                    <p>{{ $siteSettings['contact_phone'] ?? '' }}</p>
                    <p>{{ $siteSettings['contact_address'] ?? '' }}</p>
                    @if(!empty($siteSettings['social_facebook']))<p><a href="{{ $siteSettings['social_facebook'] }}">Facebook</a></p>@endif
                    @if(!empty($siteSettings['social_instagram']))<p><a href="{{ $siteSettings['social_instagram'] }}">Instagram</a></p>@endif
                    @if(!empty($siteSettings['social_twitter']))<p><a href="{{ $siteSettings['social_twitter'] }}">Twitter / X</a></p>@endif
                    @if(!empty($siteSettings['social_whatsapp']))<p><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['social_whatsapp']) }}">WhatsApp</a></p>@endif
                </div>
            </div>
            <div class="footer-bottom">
                <p>{{ $siteSettings['footer_text'] ?? '&copy; ' . date('Y') . ' Concreto. All rights reserved.' }}</p>
            </div>
        </div>
    </footer>

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>
