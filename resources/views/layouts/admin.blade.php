<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $siteSettings['company_name'] ?? 'Concreto' }}</title>
    @if(!empty($siteSettings['site_favicon']))
        <link rel="icon" href="{{ url('media/' . $siteSettings['site_favicon']) }}">
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
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                @php $logoH = ($siteSettings['logo_height'] ?? 36) . 'px'; @endphp
                @if(!empty($siteSettings['site_logo']))
                    <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="{{ $siteSettings['company_name'] ?? 'Concreto' }}" style="height:{{ $logoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                @else
                    <img src="/assets/logo/concreto.webp" alt="Concreto" style="height:{{ $logoH }} !important; width:auto !important; max-width:none !important; max-height:none !important;">
                @endif
                Admin
            </div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">&#9632; Dashboard</a>
            <div class="sidebar-section">Orders</div>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">&#9744; Orders</a>
            <a href="{{ route('admin.quotes.index') }}" class="{{ request()->routeIs('admin.quotes*') ? 'active' : '' }}">&#9997; Quotes</a>
            <div class="sidebar-section">Catalog</div>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}">&#9733; Products</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">&#9776; Categories</a>
            <a href="{{ route('admin.product-analytics.index') }}" class="{{ request()->routeIs('admin.product-analytics*') ? 'active' : '' }}">&#128200; Product Analytics</a>
            <div class="sidebar-section">People</div>
            <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers*') ? 'active' : '' }}">&#9786; Customers</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">&#9998; Users</a>
            <div class="sidebar-section">Tracking & Ops</div>
            <a href="{{ route('admin.tracking.drivers') }}" class="{{ request()->routeIs('admin.tracking*') ? 'active' : '' }}">&#9737; Track Drivers</a>
            <a href="{{ route('admin.ops.index') }}" class="{{ request()->routeIs('admin.ops*') ? 'active' : '' }}">&#9888; Operations Board</a>
            <a href="{{ route('admin.delivery-areas.index') }}" class="{{ request()->routeIs('admin.delivery-areas*') ? 'active' : '' }}">&#9873; Delivery Areas</a>
            <a href="{{ route('admin.drivers.index') }}" class="{{ request()->routeIs('admin.drivers*') ? 'active' : '' }}">&#128664; Driver Management</a>
            <div class="sidebar-section">Marketing</div>
            <a href="{{ route('admin.promo-codes.index') }}" class="{{ request()->routeIs('admin.promo-codes*') ? 'active' : '' }}">&#127991; Promo Codes</a>
            <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">&#9733; Reviews</a>
            <div class="sidebar-section">Communication</div>
            <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages*') ? 'active' : '' }}">&#9993; Messages</a>
            <a href="{{ route('admin.email-templates.index') }}" class="{{ request()->routeIs('admin.email-templates*') ? 'active' : '' }}">&#9993; Email Templates</a>
            <div class="sidebar-section">Banking</div>
            <a href="{{ route('admin.bank-accounts.index') }}" class="{{ request()->routeIs('admin.bank-accounts*') ? 'active' : '' }}">&#127974; Bank Accounts</a>
            <a href="{{ route('admin.bank-reconciliation.index') }}" class="{{ request()->routeIs('admin.bank-reconciliation.index') || request()->routeIs('admin.bank-reconciliation.match*') || request()->routeIs('admin.bank-reconciliation.auto*') || request()->routeIs('admin.bank-reconciliation.search*') ? 'active' : '' }}">&#128260; Reconciliation</a>
            <a href="{{ route('admin.bank-reconciliation.statement') }}" class="{{ request()->routeIs('admin.bank-reconciliation.statement*') ? 'active' : '' }}">&#128209; Recon Statement</a>
            <a href="{{ route('admin.bank-reconciliation.rules.index') }}" class="{{ request()->routeIs('admin.bank-reconciliation.rules*') ? 'active' : '' }}">&#128295; Recon Rules</a>
            <div class="sidebar-section">Finance</div>
            <a href="{{ route('admin.accounts-receivable.index') }}" class="{{ request()->routeIs('admin.accounts-receivable*') ? 'active' : '' }}">&#128176; Accounts Receivable</a>
            <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">&#128196; Invoice Register</a>
            <a href="{{ route('admin.payment-register.index') }}" class="{{ request()->routeIs('admin.payment-register*') ? 'active' : '' }}">&#128179; Payment Register</a>
            <a href="{{ route('admin.vat-report.index') }}" class="{{ request()->routeIs('admin.vat-report*') ? 'active' : '' }}">&#128200; VAT Report</a>
            <a href="{{ route('admin.profit-loss.index') }}" class="{{ request()->routeIs('admin.profit-loss*') ? 'active' : '' }}">&#128178; Profit & Loss</a>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">&#128202; Reports</a>
            <div class="sidebar-section">Automation</div>
            <a href="{{ route('admin.recurring-orders.index') }}" class="{{ request()->routeIs('admin.recurring-orders*') ? 'active' : '' }}">&#128260; Recurring Orders</a>
            <a href="{{ route('admin.order-templates.index') }}" class="{{ request()->routeIs('admin.order-templates*') ? 'active' : '' }}">&#128203; Order Templates</a>
            <a href="{{ route('admin.invoice-reminders.index') }}" class="{{ request()->routeIs('admin.invoice-reminders*') ? 'active' : '' }}">&#128276; Invoice Reminders</a>
            <div class="sidebar-section">System</div>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}">&#9881; Settings</a>
            <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}">&#9201; Audit Log</a>
            <a href="{{ route('admin.notification-logs.index') }}" class="{{ request()->routeIs('admin.notification-logs*') ? 'active' : '' }}">&#128172; Notification Logs</a>
            <a href="{{ route('admin.staff-permissions.index') }}" class="{{ request()->routeIs('admin.staff-permissions*') ? 'active' : '' }}">&#128274; Staff Permissions</a>
            <div style="padding: 1rem 1.25rem; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.08);">
                <a href="{{ route('home') }}" style="margin-bottom: 0.25rem;">&#8592; View Site</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:rgba(255,255,255,0.5);cursor:pointer;font-size:0.875rem;padding:0.5rem 0;">&#10005; Logout</button>
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
            @if(session('info'))
                <div class="alert alert-info" data-dismiss>{{ session('info') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>
