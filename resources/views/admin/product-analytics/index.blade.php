@extends('layouts.admin')
@section('title', 'Product Analytics')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Product Analytics</h1>

    <!-- Date Range -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
                <div class="col-md-3"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
                <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-primary">Update</button><a href="{{ route('admin.product-analytics.index') }}" class="btn btn-secondary ms-2">Reset</a></div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-2"><div class="card"><div class="card-body text-center"><h6 class="text-muted mb-1">Revenue</h6><h4 class="mb-0">R {{ number_format($totalRevenue, 0) }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body text-center"><h6 class="text-muted mb-1">Cost</h6><h4 class="mb-0">R {{ number_format($totalCost, 0) }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body text-center"><h6 class="text-muted mb-1">Profit</h6><h4 class="mb-0 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">R {{ number_format($totalProfit, 0) }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body text-center"><h6 class="text-muted mb-1">Margin</h6><h4 class="mb-0">{{ $overallMargin }}%</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body text-center"><h6 class="text-muted mb-1">Units Sold</h6><h4 class="mb-0">{{ number_format($totalUnitsSold, 0) }}</h4></div></div></div>
        <div class="col-md-2"><div class="card"><div class="card-body text-center"><h6 class="text-muted mb-1">Products</h6><h4 class="mb-0">{{ $productPerformance->count() }}</h4></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card"><div class="card-header"><h5 class="mb-0">By Category</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Category</th><th class="text-end">Revenue</th><th class="text-center">Products</th></tr></thead><tbody>@foreach($categoryBreakdown as $cat)<tr><td>{{ $cat->category }}</td><td class="text-end">R {{ number_format($cat->revenue, 0) }}</td><td class="text-center">{{ $cat->product_count }}</td></tr>@endforeach</tbody></table></div></div></div>
        </div>
        <div class="col-md-4">
            <div class="card" style="{{ $lowStock->count() > 0 ? 'border-left:4px solid var(--warning,#e67e22);' : '' }}"><div class="card-header"><h5 class="mb-0">Low Stock ({{ $lowStock->count() }})</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Product</th><th class="text-center">Stock</th><th class="text-center">Threshold</th></tr></thead><tbody>@forelse($lowStock as $p)<tr class="{{ $p->stock_qty <= 0 ? 'table-danger' : 'table-warning' }}"><td>{{ $p->name }}</td><td class="text-center fw-bold">{{ $p->stock_qty }}</td><td class="text-center text-muted">{{ $p->low_stock_threshold }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-3">No low stock.</td></tr>@endforelse</tbody></table></div></div></div>
        </div>
        <div class="col-md-4">
            <div class="card" style="{{ $outOfStock->count() > 0 ? 'border-left:4px solid var(--danger,#e74c3c);' : '' }}"><div class="card-header"><h5 class="mb-0">Out of Stock ({{ $outOfStock->count() }})</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Product</th><th>Category</th></tr></thead><tbody>@forelse($outOfStock as $p)<tr><td>{{ $p->name }}</td><td class="text-muted">{{ $p->category?->name ?? '-' }}</td></tr>@empty<tr><td colspan="2" class="text-center text-muted py-3">All in stock.</td></tr>@endforelse</tbody></table></div></div></div>
        </div>
    </div>

    <!-- Product Performance Table -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Product Performance</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Product</th><th>Category</th><th class="text-center">Orders</th><th class="text-end">Qty</th><th class="text-end">Revenue</th><th class="text-end">Cost</th><th class="text-end">Profit</th><th class="text-center">Margin</th><th class="text-center">Stock</th></tr></thead>
            <tbody>
                @foreach($productPerformance as $p)
                <tr>
                    <td class="fw-bold">{{ $p->name }}</td>
                    <td class="text-muted">{{ $p->category_name ?? '-' }}</td>
                    <td class="text-center">{{ $p->order_count }}</td>
                    <td class="text-end">{{ number_format($p->total_qty, 1) }} {{ $p->unit }}</td>
                    <td class="text-end">R {{ number_format($p->total_revenue, 2) }}</td>
                    <td class="text-end">R {{ number_format($p->total_cost, 2) }}</td>
                    <td class="text-end {{ $p->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">R {{ number_format($p->gross_profit, 2) }}</td>
                    <td class="text-center"><span class="badge bg-{{ $p->margin_pct >= 30 ? 'success' : ($p->margin_pct >= 15 ? 'warning' : 'danger') }}">{{ $p->margin_pct }}%</span></td>
                    <td class="text-center">{{ $p->stock_qty ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div></div>
    </div>

    @if($noSales->count() > 0)
    <div class="card"><div class="card-header"><h5 class="mb-0">No Sales in Period ({{ $noSales->count() }})</h5></div><div class="card-body"><div style="display:flex; flex-wrap:wrap; gap:0.5rem;">@foreach($noSales->take(30) as $p)<span class="badge bg-light text-dark">{{ $p->name }}</span>@endforeach @if($noSales->count() > 30)<span class="text-muted">+{{ $noSales->count() - 30 }} more</span>@endif</div></div></div>
    @endif
</div>
@endsection
