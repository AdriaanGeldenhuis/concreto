@extends('layouts.portal')
@section('title', 'Order Templates & Recurring Orders')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))
@section('nav-links')
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a href="{{ route('customer.orders.index') }}">Orders</a>
    <a href="{{ route('customer.invoices.index') }}">Invoices</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>Order Templates & Recurring Orders</h1>
        <p class="text-muted">Save and reuse your frequent orders</p>
    </div>

    <!-- Order Templates Section -->
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <span>Order Templates</span>
            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm">Create New Order</a>
        </div>
        <div class="card-body">
            @if($templates->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p style="margin-bottom:1rem;">No order templates saved yet.</p>
                    <p style="font-size:0.875rem;">Create a template by placing an order and saving it as a template for future use.</p>
                </div>
            @else
                <div style="display:grid; grid-template-columns:1fr; gap:1rem;">
                    @foreach($templates as $template)
                        <div class="card" style="margin-bottom:0; background:rgba(255,255,255,0.02);">
                            <div class="card-body">
                                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">
                                    <div style="flex:1;">
                                        <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:0.5rem;">{{ $template->name }}</h3>
                                        @if($template->notes)
                                            <p class="text-muted" style="font-size:0.875rem; margin-bottom:0.5rem;">{{ $template->notes }}</p>
                                        @endif
                                        <div style="font-size:0.8125rem; color:var(--text-muted);">
                                            Created {{ $template->created_at->diffForHumans() }}
                                            &middot; {{ is_array($template->items) ? count($template->items) : 0 }} item(s)
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:0.5rem; flex-shrink:0;">
                                        <form method="POST" action="{{ route('customer.templates.destroy', $template) }}" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if(is_array($template->items) && count($template->items) > 0)
                                    <div style="border-top:1px solid var(--glass-border); padding-top:0.75rem;">
                                        <div style="font-size:0.8125rem; font-weight:600; color:var(--text-light); margin-bottom:0.5rem;">Template Items:</div>
                                        <div style="display:flex; flex-direction:column; gap:0.5rem;">
                                            @foreach($template->items as $item)
                                                <div style="display:flex; justify-content:space-between; font-size:0.875rem; padding:0.5rem; background:rgba(0,0,0,0.2); border-radius:var(--radius-sm);">
                                                    <span>Product #{{ $item['product_id'] ?? '-' }}</span>
                                                    <span class="text-muted">Qty: {{ $item['qty'] ?? 0 }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Recurring Orders Section -->
    <div class="card">
        <div class="card-header">
            <span>Recurring Orders</span>
        </div>
        <div class="card-body">
            @if($recurringOrders->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p style="margin-bottom:1rem;">No recurring orders scheduled.</p>
                    <p style="font-size:0.875rem;">Set up automatic recurring orders based on your templates for regular deliveries.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Frequency</th>
                                <th>Next Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recurringOrders as $recurring)
                                <tr>
                                    <td>
                                        <strong>{{ $recurring->template->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ ucfirst(str_replace('_', ' ', $recurring->frequency)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($recurring->next_run_date)
                                            <span class="text-muted">{{ $recurring->next_run_date->format('d M Y') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recurring->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($recurring->is_active)
                                            <form method="POST" action="{{ route('customer.recurring.cancel', $recurring) }}" onsubmit="return confirm('Are you sure you want to cancel this recurring order?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($templates->isEmpty() && $recurringOrders->isEmpty())
        <div style="text-align:center; margin-top:2rem;">
            <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-lg">
                Place Your First Order
            </a>
        </div>
    @endif
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}">
        <span class="nav-icon">&#127968;</span>
        <span>Home</span>
    </a>
    <a href="{{ route('customer.orders.index') }}">
        <span class="nav-icon">&#128230;</span>
        <span>Orders</span>
    </a>
    <a href="{{ route('customer.templates.index') }}" class="active">
        <span class="nav-icon">&#128220;</span>
        <span>Templates</span>
    </a>
    <a href="{{ route('products') }}">
        <span class="nav-icon">&#128722;</span>
        <span>Shop</span>
    </a>
</nav>
@endsection

@push('styles')
<style>
@@media (min-width: 768px) {
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
@@media (max-width: 767px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    table {
        font-size: 0.8125rem;
    }
    td, th {
        padding: 0.5rem;
    }
}
</style>
@endpush
