@extends('layouts.admin')
@section('title', 'Product Analytics')

@section('content')
    <div class="page-header">
        <h1>Product Analytics</h1>
    </div>

    {{-- Date Range Filter --}}
    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET" style="flex-wrap:wrap;">
                <div class="form-group">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="form-group">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    <a href="{{ route('admin.product-analytics.index') }}" class="btn btn-ghost btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; margin-bottom:1rem;">
        <div class="card">
            <div class="card-body" style="text-align:center; padding:1rem;">
                <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Revenue</div>
                <div class="font-semibold" style="font-size:1.25rem;">R {{ number_format($totalRevenue, 0) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:1rem;">
                <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Cost</div>
                <div class="font-semibold" style="font-size:1.25rem;">R {{ number_format($totalCost, 0) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:1rem;">
                <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Profit</div>
                <div class="font-semibold" style="font-size:1.25rem; color:var({{ $totalProfit >= 0 ? '--success, #22c55e' : '--danger, #ef4444' }});">R {{ number_format($totalProfit, 0) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:1rem;">
                <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Margin</div>
                <div class="font-semibold" style="font-size:1.25rem;">{{ $overallMargin }}%</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:1rem;">
                <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Units Sold</div>
                <div class="font-semibold" style="font-size:1.25rem;">{{ number_format($totalUnitsSold, 0) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:1rem;">
                <div class="text-muted" style="font-size:0.8rem; margin-bottom:0.25rem;">Products</div>
                <div class="font-semibold" style="font-size:1.25rem;">{{ $productPerformance->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Category / Low Stock / Out of Stock --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1rem; margin-bottom:1rem;">
        {{-- By Category --}}
        <div class="card">
            <div class="card-header">By Category</div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-center">Products</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($categoryBreakdown as $cat)
                        <tr>
                            <td>{{ $cat->category }}</td>
                            <td class="text-right font-semibold">R {{ number_format($cat->revenue, 0) }}</td>
                            <td class="text-center">{{ $cat->product_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted" style="text-align:center; padding:1rem;">No data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Low Stock --}}
        <div class="card" style="{{ $lowStock->count() > 0 ? 'border-left:4px solid var(--warning, #e67e22);' : '' }}">
            <div class="card-header">
                <span>Low Stock</span>
                <span class="badge badge-warning">{{ $lowStock->count() }}</span>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Threshold</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($lowStock as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $p->stock_qty <= 0 ? 'danger' : 'warning' }}">{{ $p->stock_qty }}</span>
                            </td>
                            <td class="text-center text-muted">{{ $p->low_stock_threshold }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted" style="text-align:center; padding:1rem;">No low stock.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Out of Stock --}}
        <div class="card" style="{{ $outOfStock->count() > 0 ? 'border-left:4px solid var(--danger, #e74c3c);' : '' }}">
            <div class="card-header">
                <span>Out of Stock</span>
                <span class="badge badge-danger">{{ $outOfStock->count() }}</span>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($outOfStock as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td class="text-muted">{{ $p->category?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-muted" style="text-align:center; padding:1rem;">All in stock.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Product Performance Table --}}
    <div class="card">
        <div class="card-header">
            <span>Product Performance</span>
            <span class="badge badge-secondary">{{ $productPerformance->count() }} products</span>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center">Orders</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Profit</th>
                        <th class="text-center">Margin</th>
                        <th class="text-center">Stock</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($productPerformance as $p)
                    <tr>
                        <td class="font-semibold">{{ $p->name }}</td>
                        <td class="text-muted">{{ $p->category_name ?? '-' }}</td>
                        <td class="text-center">{{ $p->order_count }}</td>
                        <td class="text-right">{{ number_format($p->total_qty, 1) }} {{ $p->unit }}</td>
                        <td class="text-right font-semibold">R {{ number_format($p->total_revenue, 2) }}</td>
                        <td class="text-right">R {{ number_format($p->total_cost, 2) }}</td>
                        <td class="text-right" style="color:var({{ $p->gross_profit >= 0 ? '--success, #22c55e' : '--danger, #ef4444' }});">R {{ number_format($p->gross_profit, 2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $p->margin_pct >= 30 ? 'success' : ($p->margin_pct >= 15 ? 'warning' : 'danger') }}">{{ $p->margin_pct }}%</span>
                        </td>
                        <td class="text-center">{{ $p->stock_qty ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state"><div class="icon">&#128200;</div><h3>No sales data</h3><p>No delivered orders in this period.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- No Sales --}}
    @if($noSales->count() > 0)
    <div class="card">
        <div class="card-header">
            <span>No Sales in Period</span>
            <span class="badge badge-secondary">{{ $noSales->count() }}</span>
        </div>
        <div class="card-body">
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                @foreach($noSales->take(30) as $p)
                    <span class="badge badge-secondary">{{ $p->name }}</span>
                @endforeach
                @if($noSales->count() > 30)
                    <span class="text-muted">+{{ $noSales->count() - 30 }} more</span>
                @endif
            </div>
        </div>
    </div>
    @endif
@endsection
