<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#e67e22' }}">
    <title>@yield('title', 'Portal') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    @if(!empty($siteSettings['site_favicon']))
        <link rel="icon" href="{{ url('media/' . $siteSettings['site_favicon']) }}">
    @endif
    @if(!empty($siteSettings['google_font_url']))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $siteSettings['google_font_url'] }}" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="/css/app.css">
    @if(empty($siteSettings['site_favicon']))
        <link rel="icon" type="image/png" href="/assets/logo/concreto.webp">
    @endif
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
            @if(!empty($siteSettings['font_family']) && $siteSettings['font_family'] !== 'system')--font-body: {{ $siteSettings['font_family'] }};@endif
            @if(!empty($siteSettings['heading_font_family']) && $siteSettings['heading_font_family'] !== 'inherit')--heading-font-family: {{ $siteSettings['heading_font_family'] }};@endif
            @if(!empty($siteSettings['font_size_base']))--font-size-base: {{ $siteSettings['font_size_base'] }}px;@endif
            @if(!empty($siteSettings['font_weight_body']))--font-weight-body: {{ $siteSettings['font_weight_body'] }};@endif
            @if(!empty($siteSettings['font_weight_heading']))--font-weight-heading: {{ $siteSettings['font_weight_heading'] }};@endif
            @if(!empty($siteSettings['line_height']))--line-height: {{ $siteSettings['line_height'] }};@endif
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
            <a href="@yield('home-url', '/')" class="navbar-brand">
                @php $portalLogoH = min(($siteSettings['logo_height'] ?? 36), 36) . 'px'; @endphp
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}" style="height:{{ $portalLogoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                @else
                    <img src="/assets/logo/concreto.webp" alt="Concreto" style="height:{{ $portalLogoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                @endif
                <span>@yield('portal-name', 'Portal')</span>
            </a>
            <button class="navbar-toggle" aria-label="Toggle menu">&#9776;</button>
            <div class="navbar-links">
                <a href="https://concreto.co.za">Home</a>
                <a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('customer.orders.index') }}" class="{{ request()->routeIs('customer.orders.*') ? 'active' : '' }}">Orders</a>
                <a href="{{ route('customer.invoices.index') }}" class="{{ request()->routeIs('customer.invoices.*') ? 'active' : '' }}">Invoices</a>
                <a href="{{ route('customer.account') }}" class="{{ request()->routeIs('customer.account') ? 'active' : '' }}">Account</a>
                @yield('nav-links')
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
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

    <main class="portal-body">
        <div class="container">
            @yield('content')
        </div>
    </main>

    @yield('bottom-nav')

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>
