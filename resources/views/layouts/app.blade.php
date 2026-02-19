<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#e67e22' }}">
    <meta name="description" content="{{ $siteSettings['hero_subtitle'] ?? 'Quality building materials delivered to your site.' }}">
    <title>@yield('title', 'Concreto') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="@yield('title', 'Concreto') - {{ $siteSettings['company_name'] ?? 'Concreto' }}">
    <meta property="og:description" content="{{ $siteSettings['hero_subtitle'] ?? 'Quality building materials delivered to your site.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($siteSettings['site_logo']))
    <meta property="og:image" content="{{ url('media/' . $siteSettings['site_logo']) }}">
    @endif
    <meta property="og:site_name" content="{{ $siteSettings['company_name'] ?? 'Concreto' }}">

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "LocalBusiness",
        "name": "{{ $siteSettings['company_name'] ?? 'Concreto' }}",
        "description": "{{ $siteSettings['hero_subtitle'] ?? 'Quality building materials delivered to your site.' }}",
        @if(!empty($siteSettings['site_logo']))
        "image": "{{ url('media/' . $siteSettings['site_logo']) }}",
        @endif
        @if(!empty($siteSettings['contact_phone']))
        "telephone": "{{ $siteSettings['contact_phone'] }}",
        @endif
        @if(!empty($siteSettings['contact_email']))
        "email": "{{ $siteSettings['contact_email'] }}",
        @endif
        @if(!empty($siteSettings['contact_address']))
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "{{ $siteSettings['contact_address'] }}"
        },
        @endif
        "url": "{{ url('/') }}",
        "priceRange": "$$",
        @if(!empty($siteSettings['business_hours']))
        "openingHours": "{{ $siteSettings['business_hours'] }}"
        @endif
    }
    </script>
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
        @@media (max-width: 991px) {
            .hero-bg--desktop { display:none; }
            .hero-bg--tablet { display:block; }
        }
        @endif
        @if(!empty($siteSettings['bg_image_mobile']))
        @@media (max-width: 767px) {
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
                    <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}" style="height:{{ $logoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                @else
                    <img src="/assets/logo/concreto.webp" alt="Concreto" style="height:{{ $logoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                @endif
            </a>
            <button class="navbar-toggle" aria-label="Toggle menu">&#9776;</button>
            <div class="navbar-links">
                @auth
                <div class="notification-bell-wrap">
                    <button class="notification-bell" id="notification-bell" aria-label="Notifications">
                        <svg class="bell-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notification-count" id="notification-count" style="display:none;">0</span>
                    </button>
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-dropdown-header">
                            <strong>Notifications</strong>
                            <button onclick="markAllNotificationsRead()" class="notification-mark-all">Mark all read</button>
                        </div>
                        <div class="notification-dropdown-body" id="notification-list">
                            <div class="notification-empty">No notifications</div>
                        </div>
                    </div>
                </div>
                @endauth
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
                            <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}" style="height:{{ $logoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                        @else
                            <img src="/assets/logo/concreto.webp" alt="Concreto" style="height:{{ $logoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
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

    {{-- Cookie Consent Banner (POPIA Compliant) --}}
    <div id="cookie-consent" class="cookie-consent" style="display:none;">
        <div class="cookie-content">
            <p>We use cookies to improve your experience on our website. By continuing to browse, you agree to our use of cookies. <a href="{{ route('privacy') }}" style="color: inherit; text-decoration: underline;">Privacy Policy</a></p>
            <div class="cookie-buttons">
                <button onclick="acceptCookies()" class="btn btn-primary btn-sm">Accept</button>
                <button onclick="declineCookies()" class="btn btn-ghost btn-sm">Decline</button>
            </div>
        </div>
    </div>

    {{-- WhatsApp Floating Chat Widget --}}
    @if(!empty($siteSettings['whatsapp']))
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp']) }}" target="_blank" class="whatsapp-float" aria-label="Chat on WhatsApp">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 0C7.164 0 0 7.164 0 16c0 2.828.736 5.484 2.028 7.78L0 32l8.44-2.016A15.917 15.917 0 0016 32c8.836 0 16-7.164 16-16S24.836 0 16 0zm0 29.333c-2.464 0-4.84-.68-6.92-1.96l-.496-.296-5.144 1.232 1.248-5.012-.328-.516A13.276 13.276 0 012.667 16c0-7.364 5.969-13.333 13.333-13.333S29.333 8.636 29.333 16 23.364 29.333 16 29.333z" fill="white"/>
            <path d="M12.227 9.547c-.264-.587-.542-.598-.792-.608-.206-.008-.44-.008-.676-.008-.234 0-.616.088-.938.44-.322.352-1.23 1.202-1.23 2.93 0 1.73 1.258 3.402 1.434 3.636.176.235 2.46 3.933 6.074 5.377 3.012 1.204 3.614.965 4.268.904.654-.06 2.112-.864 2.41-1.698.298-.834.298-1.548.208-1.698-.088-.15-.322-.238-.676-.416-.352-.176-2.084-1.03-2.408-1.146-.322-.118-.558-.176-.792.176-.235.352-.91 1.146-1.116 1.382-.206.234-.412.264-.764.088-.352-.177-1.486-.548-2.832-1.748-1.046-.933-1.752-2.085-1.958-2.437-.206-.352-.022-.543.154-.718.158-.158.352-.412.528-.618.176-.206.234-.352.352-.587.118-.234.06-.44-.03-.617-.088-.176-.792-1.908-1.085-2.613z" fill="white"/>
        </svg>
    </a>
    @endif

    <script src="/js/app.js"></script>
    @stack('scripts')

    {{-- Cookie Consent Script --}}
    <script>
        // Check if user has already made a choice
        window.addEventListener('DOMContentLoaded', function() {
            const cookieConsent = localStorage.getItem('cookieConsent');
            if (!cookieConsent) {
                document.getElementById('cookie-consent').style.display = 'block';
            }
        });

        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            document.getElementById('cookie-consent').style.display = 'none';
        }

        function declineCookies() {
            localStorage.setItem('cookieConsent', 'declined');
            document.getElementById('cookie-consent').style.display = 'none';
        }
    </script>

    {{-- Notification Bell Script --}}
    @auth
    <script>
    (function(){
        var bell = document.getElementById('notification-bell');
        var dropdown = document.getElementById('notification-dropdown');
        var countEl = document.getElementById('notification-count');
        var listEl = document.getElementById('notification-list');
        var isOpen = false;
        var lastCount = 0;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!bell) return;

        // Toggle dropdown
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            isOpen = !isOpen;
            dropdown.classList.toggle('open', isOpen);
            if (isOpen) {
                bell.classList.remove('bell-ring');
                loadNotifications();
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (isOpen && !dropdown.contains(e.target) && !bell.contains(e.target)) {
                isOpen = false;
                dropdown.classList.remove('open');
            }
        });

        // Fetch unread count
        function fetchUnreadCount() {
            fetch('/notifications/unread-count', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (!data) return;
                var count = data.unread_count || 0;
                if (count > 0) {
                    countEl.textContent = count > 99 ? '99+' : count;
                    countEl.style.display = '';
                    // Ring the bell when count increases
                    if (count > lastCount) {
                        bell.classList.add('has-notifications', 'bell-ring');
                        // Remove ring class after animation so it can re-trigger
                        setTimeout(function(){ bell.classList.remove('bell-ring'); }, 900);
                    } else {
                        bell.classList.add('has-notifications');
                    }
                } else {
                    countEl.style.display = 'none';
                    bell.classList.remove('has-notifications');
                }
                lastCount = count;
            })
            .catch(function(){});
        }

        // Load notification list
        function loadNotifications() {
            fetch('/notifications/list', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(resp) {
                if (!resp || !resp.data || resp.data.length === 0) {
                    listEl.innerHTML = '<div class="notification-empty">No notifications yet</div>';
                    return;
                }
                var html = '';
                resp.data.forEach(function(n) {
                    var d = n.data || {};
                    var isUnread = !n.read_at;
                    var timeAgo = formatTimeAgo(n.created_at);
                    html += '<div class="notification-item' + (isUnread ? ' unread' : '') + '" onclick="readNotification(\'' + n.id + '\')">';
                    html += '<div class="notification-item-title">' + escHtml(d.title || 'Notification') + '</div>';
                    html += '<div class="notification-item-body">' + escHtml(d.body || '') + '</div>';
                    html += '<div class="notification-item-time">' + timeAgo + '</div>';
                    html += '</div>';
                });
                listEl.innerHTML = html;
            })
            .catch(function() {
                listEl.innerHTML = '<div class="notification-empty">Could not load notifications</div>';
            });
        }

        // Mark single as read
        window.readNotification = function(id) {
            fetch('/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function() {
                fetchUnreadCount();
                loadNotifications();
            });
        };

        // Mark all read
        window.markAllNotificationsRead = function() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function() {
                fetchUnreadCount();
                loadNotifications();
            });
        };

        function escHtml(s) {
            var el = document.createElement('div');
            el.textContent = s;
            return el.innerHTML;
        }

        function formatTimeAgo(dateStr) {
            var d = new Date(dateStr);
            var now = new Date();
            var diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
            return d.toLocaleDateString();
        }

        // Initial fetch + poll every 30 seconds
        fetchUnreadCount();
        setInterval(fetchUnreadCount, 30000);
    })();
    </script>
    @endauth

</body>
</html>
