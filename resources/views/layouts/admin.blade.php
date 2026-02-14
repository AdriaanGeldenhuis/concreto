<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    <link rel="stylesheet" href="/css/app.css">
    @if(isset($siteSettings))
    <style>
        :root {
            @if(!empty($siteSettings['primary_color']))--primary: {{ $siteSettings['primary_color'] }};@endif
            @if(!empty($siteSettings['secondary_color']))--secondary: {{ $siteSettings['secondary_color'] }};@endif
        }
    </style>
    @endif
    @stack('styles')
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <img src="/assets/logo/concreto.webp" alt="Concreto"> Admin
            </div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">&#9632; Dashboard</a>
            <div class="sidebar-section">Orders</div>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">&#9744; Orders</a>
            <a href="{{ route('admin.quotes.index') }}" class="{{ request()->routeIs('admin.quotes*') ? 'active' : '' }}">&#9997; Quotes</a>
            <div class="sidebar-section">Catalog</div>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}">&#9733; Products</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">&#9776; Categories</a>
            <div class="sidebar-section">People</div>
            <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers*') ? 'active' : '' }}">&#9786; Customers</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">&#9998; Users</a>
            <div class="sidebar-section">System</div>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}">&#9881; Settings</a>
            <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}">&#9201; Audit Log</a>
            <div style="padding: 1rem 1.25rem; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:rgba(255,255,255,0.6);cursor:pointer;font-size:0.875rem;">&#8592; Logout</button>
                </form>
            </div>
        </aside>

        <main class="admin-content">
            <button class="admin-menu-toggle btn btn-secondary btn-sm mb-2" style="display:none;">&#9776; Menu</button>

            @if(session('success'))
                <div class="alert alert-success" data-dismiss>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger" data-dismiss>{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>
