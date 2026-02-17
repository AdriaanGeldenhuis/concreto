@extends('layouts.admin')
@section('title', 'Promo Code: ' . $promoCode->code)
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.promo-codes.index') }}">Promo Codes</a> / {{ $promoCode->code }}
        </div>
        <h1>{{ $promoCode->code }}</h1>
        @if($promoCode->is_active)
            <span class="badge badge-success">Active</span>
        @else
            <span class="badge badge-secondary">Inactive</span>
        @endif
        <span class="badge badge-info">{{ ucfirst($promoCode->type) }}: {{ $promoCode->type === 'percentage' ? $promoCode->value . '%' : 'R ' . number_format($promoCode->value, 2) }}</span>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Total Uses</h6><h4 class="mb-0">{{ $usageStats['total_uses'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Total Discount Given</h6><h4 class="mb-0">R {{ number_format($usageStats['total_discount'], 2) }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Unique Customers</h6><h4 class="mb-0">{{ $usageStats['unique_customers'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Avg Discount</h6><h4 class="mb-0">R {{ number_format($usageStats['avg_discount'], 2) }}</h4></div></div>
    </div>

    {{-- Code Details --}}
    <div class="card mb-2">
        <div class="card-header">Code Details</div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div><strong>Description:</strong> {{ $promoCode->description ?: '-' }}</div>
                <div><strong>Min Order:</strong> {{ $promoCode->min_order_amount ? 'R ' . number_format($promoCode->min_order_amount, 2) : 'None' }}</div>
                <div><strong>Max Discount:</strong> {{ $promoCode->max_discount ? 'R ' . number_format($promoCode->max_discount, 2) : 'Unlimited' }}</div>
                <div><strong>Max Uses:</strong> {{ $promoCode->max_uses ?? 'Unlimited' }} (used {{ $promoCode->times_used }})</div>
                <div><strong>Per Customer:</strong> {{ $promoCode->max_uses_per_customer ?? 'Unlimited' }}</div>
                <div><strong>Starts:</strong> {{ $promoCode->starts_at?->format('d M Y') ?? '-' }}</div>
                <div><strong>Expires:</strong> {{ $promoCode->expires_at?->format('d M Y') ?? 'Never' }}</div>
                <div><strong>Created:</strong> {{ $promoCode->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Usage History --}}
    <div class="card">
        <div class="card-header"><span>Usage History</span> <span class="badge badge-secondary">{{ $usageStats['total_uses'] }}</span></div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th class="text-right">Discount Applied</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($usage as $u)
                    <tr>
                        <td>
                            @if($u->order)
                                <a href="{{ route('admin.orders.show', $u->order) }}" class="font-semibold">{{ $u->order->order_number }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $u->customer->user->name ?? '-' }}</td>
                        <td class="text-right font-semibold">R {{ number_format($u->discount_applied, 2) }}</td>
                        <td class="text-muted">{{ $u->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted p-3">No usage recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($usage->hasPages())
        <div class="pagination">{!! $usage->links('pagination::simple-default') !!}</div>
    @endif
@endsection
