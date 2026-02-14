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
    <link rel="icon" type="image/png" href="/assets/logo/concreto.webp">
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
            @if(!empty($siteSettings['font_family']) && $siteSettings['font_family'] !== 'system')--font-family: {{ $siteSettings['font_family'] }};@endif
            @if(!empty($siteSettings['heading_font_family']) && $siteSettings['heading_font_family'] !== 'inherit')--heading-font-family: {{ $siteSettings['heading_font_family'] }};@endif
            @if(!empty($siteSettings['font_size_base']))--font-size-base: {{ $siteSettings['font_size_base'] }}px;@endif
            @if(!empty($siteSettings['font_weight_body']))--font-weight-body: {{ $siteSettings['font_weight_body'] }};@endif
            @if(!empty($siteSettings['font_weight_heading']))--font-weight-heading: {{ $siteSettings['font_weight_heading'] }};@endif
            @if(!empty($siteSettings['line_height']))--line-height: {{ $siteSettings['line_height'] }};@endif
            @if(!empty($siteSettings['btn_border_radius']))--btn-radius: {{ $siteSettings['btn_border_radius'] }}px;@endif
            @if(!empty($siteSettings['btn_text_transform']))--btn-text-transform: {{ $siteSettings['btn_text_transform'] }};@endif
            @if(!empty($siteSettings['navbar_bg_color']))--navbar-bg: {{ $siteSettings['navbar_bg_color'] }};@endif
            @if(!empty($siteSettings['navbar_text_color']))--navbar-text: {{ $siteSettings['navbar_text_color'] }};@endif
        }
        @if(!empty($siteSettings['card_shadow']))
        @php $shadowMap = ['none'=>'none','light'=>'0 1px 4px rgba(0,0,0,0.04)','medium'=>'0 2px 8px rgba(0,0,0,0.08)','heavy'=>'0 4px 20px rgba(0,0,0,0.15)']; @endphp
        :root { --shadow: {{ $shadowMap[$siteSettings['card_shadow']] ?? '0 2px 8px rgba(0,0,0,0.08)' }}; }
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
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}">
                @else
                    <img src="/assets/logo/concreto.webp" alt="Concreto">
                @endif
                <span>@yield('portal-name', 'Portal')</span>
            </a>
            <div class="navbar-links">
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
