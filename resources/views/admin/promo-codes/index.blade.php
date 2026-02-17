@extends('layouts.admin')
@section('title', 'Promo Codes')
@section('content')
    <div class="page-header">
        <h1>Promo Codes</h1>
        <span class="badge badge-secondary">{{ $stats['total_codes'] }} total</span>
    </div>

    {{-- Analytics Cards --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Active Codes</h6><h4 class="mb-0 text-success">{{ $stats['active_codes'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Total Uses</h6><h4 class="mb-0">{{ $stats['total_uses'] }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Total Discount Given</h6><h4 class="mb-0">R {{ number_format($stats['total_discount'], 0) }}</h4></div></div>
        <div class="card"><div class="card-body" style="padding:1rem; text-align:center;"><h6 class="text-muted mb-1" style="font-size:0.75rem;">Avg Discount</h6><h4 class="mb-0">R {{ number_format($stats['avg_discount'], 2) }}</h4></div></div>
    </div>

    {{-- Top Codes & Monthly Trend --}}
    @if($topCodes->count() > 0 || $monthlyUsage->count() > 0)
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
        @if($topCodes->count() > 0)
        <div class="card">
            <div class="card-header">Top Promo Codes</div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Code</th><th class="text-center">Uses</th><th class="text-right">Discount</th></tr></thead>
                    <tbody>
                    @foreach($topCodes as $tc)
                        <tr>
                            <td><a href="{{ route('admin.promo-codes.show', $tc) }}" class="font-semibold">{{ $tc->code }}</a></td>
                            <td class="text-center">{{ $tc->usage_count }}</td>
                            <td class="text-right">R {{ number_format($tc->usage_sum_discount_applied ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @if($monthlyUsage->count() > 0)
        <div class="card">
            <div class="card-header">Monthly Usage (6 months)</div>
            <div class="card-body">
                <div style="display:flex; align-items:flex-end; gap:4px; height:100px;">
                    @php $maxUses = $monthlyUsage->max('uses') ?: 1; @endphp
                    @foreach($monthlyUsage as $m)
                        <div style="flex:1; display:flex; flex-direction:column; align-items:center;">
                            <small style="font-size:0.65rem;" class="text-muted">{{ $m->uses }}</small>
                            <div style="width:100%; background:var(--primary, #3498db); border-radius:3px 3px 0 0; min-height:3px; height:{{ ($m->uses / $maxUses) * 80 }}px;" title="R {{ number_format($m->total_discount, 2) }}"></div>
                            <small class="text-muted" style="font-size:0.6rem; margin-top:2px;">{{ \Carbon\Carbon::parse($m->month . '-01')->format('M') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Create Form --}}
    <div class="card mb-2">
        <div class="card-header">Create New Promo Code</div>
        <div class="card-body">
            <form action="{{ route('admin.promo-codes.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Code</label><input type="text" class="form-control" name="code" required placeholder="e.g. SUMMER20"></div>
                    <div class="form-group"><label class="form-label">Type</label><select class="form-control" name="type" required><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></div>
                    <div class="form-group"><label class="form-label">Value</label><input type="number" step="0.01" class="form-control" name="value" required></div>
                    <div class="form-group"><label class="form-label">Min Order</label><input type="number" step="0.01" class="form-control" name="min_order_amount"></div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:2"><label class="form-label">Description</label><input type="text" class="form-control" name="description"></div>
                    <div class="form-group"><label class="form-label">Max Discount</label><input type="number" step="0.01" class="form-control" name="max_discount"></div>
                    <div class="form-group"><label class="form-label">Max Uses</label><input type="number" class="form-control" name="max_uses"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Per Customer</label><input type="number" class="form-control" name="max_uses_per_customer"></div>
                    <div class="form-group"><label class="form-label">Starts At</label><input type="datetime-local" class="form-control" name="starts_at"></div>
                    <div class="form-group"><label class="form-label">Expires At</label><input type="datetime-local" class="form-control" name="expires_at"></div>
                    <div class="form-group" style="flex:0.5;"><label class="form-label">Active</label><div class="form-check mt-1"><input type="checkbox" name="is_active" value="1" checked> Active</div></div>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>

    {{-- Promo Codes Table --}}
    <div class="card">
        <div class="card-header">All Promo Codes</div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th class="text-center">Uses</th>
                        <th class="text-right">Discount Given</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($promoCodes as $pc)
                    <tr>
                        <td><a href="{{ route('admin.promo-codes.show', $pc) }}" class="font-semibold">{{ $pc->code }}</a><br><small class="text-muted">{{ Str::limit($pc->description, 30) }}</small></td>
                        <td><span class="badge badge-info">{{ ucfirst($pc->type) }}</span></td>
                        <td class="font-semibold">{{ $pc->type === 'percentage' ? $pc->value . '%' : 'R ' . number_format($pc->value, 2) }}</td>
                        <td class="text-center">{{ $pc->usage_count }}<span class="text-muted">/{{ $pc->max_uses ?? '&infin;' }}</span></td>
                        <td class="text-right">R {{ number_format($pc->usage_sum_discount_applied ?? 0, 2) }}</td>
                        <td class="text-muted">{{ $pc->expires_at?->format('d M Y') ?? 'Never' }}</td>
                        <td>
                            @if($pc->is_active)
                                @if($pc->expires_at && $pc->expires_at->isPast())
                                    <span class="badge badge-warning">Expired</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right" style="white-space:nowrap;">
                            <a href="{{ route('admin.promo-codes.show', $pc) }}" class="btn btn-sm btn-outline">Usage</a>
                            <button type="button" class="btn btn-sm btn-warning" onclick="document.getElementById('edit-{{ $pc->id }}').style.display='flex'">Edit</button>
                            <form action="{{ route('admin.promo-codes.destroy', $pc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this promo code?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state"><h3>No promo codes</h3><p>Create one above.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($promoCodes->hasPages())
        <div class="pagination">{!! $promoCodes->links('pagination::simple-default') !!}</div>
    @endif

    {{-- Edit Modals --}}
    @foreach($promoCodes as $pc)
    <div id="edit-{{ $pc->id }}" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
        <div style="background:var(--card,#1a1d23); border:1px solid var(--border-color); border-radius:var(--radius,8px); padding:1.5rem; max-width:650px; width:95%; max-height:90vh; overflow-y:auto;" onclick="event.stopPropagation()">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="margin:0;">Edit: {{ $pc->code }}</h3>
                <button onclick="this.closest('[id^=edit]').style.display='none'" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form action="{{ route('admin.promo-codes.update', $pc->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Code</label><input type="text" class="form-control" name="code" value="{{ $pc->code }}" required></div>
                    <div class="form-group"><label class="form-label">Type</label><select class="form-control" name="type" required><option value="percentage" {{ $pc->type === 'percentage' ? 'selected' : '' }}>Percentage</option><option value="fixed" {{ $pc->type === 'fixed' ? 'selected' : '' }}>Fixed</option></select></div>
                    <div class="form-group"><label class="form-label">Value</label><input type="number" step="0.01" class="form-control" name="value" value="{{ $pc->value }}" required></div>
                </div>
                <div class="form-group"><label class="form-label">Description</label><input type="text" class="form-control" name="description" value="{{ $pc->description }}"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Min Order</label><input type="number" step="0.01" class="form-control" name="min_order_amount" value="{{ $pc->min_order_amount }}"></div>
                    <div class="form-group"><label class="form-label">Max Discount</label><input type="number" step="0.01" class="form-control" name="max_discount" value="{{ $pc->max_discount }}"></div>
                    <div class="form-group"><label class="form-label">Max Uses</label><input type="number" class="form-control" name="max_uses" value="{{ $pc->max_uses }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Per Customer</label><input type="number" class="form-control" name="max_uses_per_customer" value="{{ $pc->max_uses_per_customer }}"></div>
                    <div class="form-group"><label class="form-label">Starts</label><input type="datetime-local" class="form-control" name="starts_at" value="{{ $pc->starts_at ? $pc->starts_at->format('Y-m-d\TH:i') : '' }}"></div>
                    <div class="form-group"><label class="form-label">Expires</label><input type="datetime-local" class="form-control" name="expires_at" value="{{ $pc->expires_at ? $pc->expires_at->format('Y-m-d\TH:i') : '' }}"></div>
                </div>
                <div class="form-check mb-2"><input type="checkbox" name="is_active" value="1" {{ $pc->is_active ? 'checked' : '' }}> Active</div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
    @endforeach
@endsection
