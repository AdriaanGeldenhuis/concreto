<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#e67e22' }}">
    <meta name="description" content="{{ $siteSettings['hero_subtitle'] ?? 'Quality building materials delivered to your site.' }}">
    <title>@yield('title', 'Concreto') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    @if(!empty($siteSettings['site_favicon']))
        <link rel="icon" href="{{ url('media/' . $siteSettings['site_favicon']) }}">
    @endif
    @if(!empty($siteSettings['google_font_url']))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $siteSettings['google_font_url'] }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="/css/app.css">
    <link rel="icon" type="image/png" href="/assets/logo/concreto.webp">
    @if(isset($siteSettings))
    <style>
        :root {
            @if(!empty($siteSettings['primary_color']))--primary: {{ $siteSettings['primary_color'] }};@endif
            @if(!empty($siteSettings['primary_dark_color']))--primary-dark: {{ $siteSettings['primary_dark_color'] }};@endif
            @if(!empty($siteSettings['success_color']))--success: {{ $siteSettings['success_color'] }};@endif
            @if(!empty($siteSettings['danger_color']))--danger: {{ $siteSettings['danger_color'] }};@endif
            @if(!empty($siteSettings['warning_color']))--warning: {{ $siteSettings['warning_color'] }};@endif
            @if(!empty($siteSettings['info_color']))--info: {{ $siteSettings['info_color'] }};@endif
            @if(!empty($siteSettings['border_radius']))--radius: {{ $siteSettings['border_radius'] }}px;@endif
            @if(!empty($siteSettings['container_max_width']))--container-max-width: {{ $siteSettings['container_max_width'] }}px;@endif
            @if(!empty($siteSettings['font_family']) && $siteSettings['font_family'] !== 'system')--font-body: {{ $siteSettings['font_family'] }};@endif
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
            @if(!empty($siteSettings['primary_color']))
            @php
                $pc = $siteSettings['primary_color'];
                $r = hexdec(substr($pc,1,2)); $g = hexdec(substr($pc,3,2)); $b = hexdec(substr($pc,5,2));
            @endphp
            --primary-glow: rgba({{ $r }},{{ $g }},{{ $b }},0.25);
            --primary-subtle: rgba({{ $r }},{{ $g }},{{ $b }},0.10);
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            --shadow-glow: 0 0 30px rgba({{ $r }},{{ $g }},{{ $b }},0.25);
            @endif
        }
        @if(!empty($siteSettings['card_shadow']))
        @php $shadowMap = ['none'=>'none','light'=>'0 2px 8px rgba(0,0,0,0.3)','medium'=>'0 4px 20px rgba(0,0,0,0.4)','heavy'=>'0 8px 32px rgba(0,0,0,0.5)']; @endphp
        :root { --shadow: {{ $shadowMap[$siteSettings['card_shadow']] ?? '0 4px 20px rgba(0,0,0,0.4)' }}; }
        @endif
        @if(!empty($siteSettings['bg_image_desktop']))
        .hero { position:relative; padding:0; overflow:hidden; }
        .hero-bg { display:block; width:100%; height:auto; }
        .hero-bg--tablet, .hero-bg--mobile { display:none; }
        .hero-overlay { position:absolute; inset:0; background: {{ $siteSettings['bg_overlay_color'] ?? '#000000' }}; opacity: {{ ($siteSettings['bg_overlay_opacity'] ?? 70) / 100 }}; }
        .hero-content { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:1; padding:2rem; text-align:center; color:#fff; }
        .hero-content h1, .hero-content p { text-shadow:0 2px 8px rgba(0,0,0,0.5); }
        @if(!empty($siteSettings['bg_image_tablet']))
        @media (max-width: 991px) {
            .hero-bg--desktop { display:none; }
            .hero-bg--tablet { display:block; }
        }
        @endif
        @if(!empty($siteSettings['bg_image_mobile']))
        @media (max-width: 767px) {
            .hero-bg--desktop, .hero-bg--tablet { display:none; }
            .hero-bg--mobile { display:block; }
        }
        @endif
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
                @php $logoH = ($siteSettings['logo_height'] ?? 36) . 'px'; @endphp
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}" style="height:{{ $logoH }} !important; width:auto !important;">
                @else
                    <img src="/assets/logo/concreto.webp" alt="Concreto" style="height:{{ $logoH }} !important; width:auto !important;">
                @endif
            </a>
            <button class="navbar-toggle" aria-label="Toggle menu">&#9776;</button>
            <div class="navbar-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('products') }}" class="{{ request()->routeIs('products*') ? 'active' : '' }}">Products</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                <a href="{{ route('cart.index') }}" class="cart-link {{ request()->routeIs('cart*') ? 'active' : '' }}">
                    &#128722; Order <span class="cart-badge" id="cart-badge" style="display:none;">0</span>
                </a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-white btn-sm">Login</a>
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
        <div class="footer-accent"></div>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        @if(!empty($siteSettings['site_logo']))
                            <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}" style="height:{{ $logoH }} !important; width:auto !important;">
                        @else
                            <img src="/assets/logo/concreto.webp" alt="Concreto" style="height:{{ $logoH }} !important; width:auto !important;">
                        @endif
                    </div>
                    <p>{{ $siteSettings['footer_about'] ?? 'Quality building materials delivered to your site.' }}</p>
                    <div class="footer-social">
                        @if(!empty($siteSettings['social_facebook']))<a href="{{ $siteSettings['social_facebook'] }}" class="social-icon" aria-label="Facebook">f</a>@endif
                        @if(!empty($siteSettings['social_instagram']))<a href="{{ $siteSettings['social_instagram'] }}" class="social-icon" aria-label="Instagram">&#9634;</a>@endif
                        @if(!empty($siteSettings['social_twitter']))<a href="{{ $siteSettings['social_twitter'] }}" class="social-icon" aria-label="Twitter">X</a>@endif
                        @if(!empty($siteSettings['social_whatsapp']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['social_whatsapp']) }}" class="social-icon" aria-label="WhatsApp">W</a>@endif
                    </div>
                </div>
                <div>
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('products') }}">Products</a></li>
                        <li><a href="{{ route('request-quote') }}">Request Quote</a></li>
                        <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Support</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('terms') }}">Terms & Conditions</a></li>
                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        @if(!empty($siteSettings['business_hours']))<li>{{ $siteSettings['business_hours'] }}</li>@endif
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul class="footer-links">
                        @if(!empty($siteSettings['contact_phone']))<li>{{ $siteSettings['contact_phone'] }}</li>@endif
                        @if(!empty($siteSettings['contact_email']))<li>{{ $siteSettings['contact_email'] }}</li>@endif
                        @if(!empty($siteSettings['contact_address']))<li>{{ $siteSettings['contact_address'] }}</li>@endif
                    </ul>
                </div>
            </div>
            <div class="footer-trust">
                <div class="trust-badge"><span class="trust-icon">&#128274;</span> Secure Payments</div>
                <div class="trust-badge"><span class="trust-icon">&#9989;</span> Quality Guaranteed</div>
                <div class="trust-badge"><span class="trust-icon">&#128666;</span> Fast Delivery</div>
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
