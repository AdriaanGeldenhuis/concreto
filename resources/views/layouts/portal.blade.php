<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#e67e22' }}">
    <title>@yield('title', 'Portal') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="icon" type="image/png" href="/assets/logo/concreto.webp">
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
            <a href="@yield('home-url', '/')" class="navbar-brand">
                <img src="/assets/logo/concreto.webp" alt="Concreto">
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
