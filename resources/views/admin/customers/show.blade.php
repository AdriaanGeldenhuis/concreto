@extends('layouts.admin')
@section('title', 'Customer: ' . $customer->user->name)
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.customers.index') }}">Customers</a> / {{ $customer->user->name }}
        </div>
        <h1>{{ $customer->company->display_name ?? $customer->user->name }}</h1>
        <div style="display:flex; gap:0.5rem; align-items:center;">
            <span class="badge badge-{{ $customer->type == 'COD' ? 'warning' : 'info' }}">{{ $customer->type }}</span>
            <span class="badge badge-{{ ($customer->user->is_active ?? false) ? 'success' : 'danger' }}">{{ ($customer->user->is_active ?? false) ? 'Active' : 'Inactive' }}</span>
            @if($customer->pay_before_dispatch)
                <span class="badge badge-warning">Pay Before Dispatch</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-2">{{ session('success') }}</div>
    @endif

    {{-- Quick Actions Bar --}}
    <div class="card mb-2">
        <div class="card-body" style="padding:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('admin.orders.create') }}?customer_id={{ $customer->id }}" class="btn btn-primary btn-sm">+ New Order</a>
            <a href="{{ route('admin.quotes.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline btn-sm">+ New Quote</a>
            <a href="{{ route('admin.accounts-receivable.statement', $customer) }}" class="btn btn-outline btn-sm">View Statement</a>
            <a href="{{ route('admin.users.edit', $customer->user) }}" class="btn btn-outline btn-sm">Edit User Account</a>
            @if($customer->user->is_active && $customer->user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle-active', $customer->user) }}" style="margin-left:auto;">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Deactivate this customer account?')">Deactivate Account</button>
                </form>
            @elseif(!$customer->user->is_active)
                <form method="POST" action="{{ route('admin.users.toggle-active', $customer->user) }}" style="margin-left:auto;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Activate this customer account?')">Activate Account</button>
                </form>
            @endif
        </div>
    </div>

    {{-- 360 Customer Summary Cards --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom:1.5rem;">
        <div class="card">
            <div class="card-body" style="padding:1rem;">
                <h6 class="text-muted mb-1" style="font-size:0.75rem;">Lifetime Value</h6>
                <h4 class="mb-0">R {{ number_format($stats['total_spent'], 2) }}</h4>
                <small class="text-muted">{{ $stats['delivered_count'] }} delivered</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:1rem;">
                <h6 class="text-muted mb-1" style="font-size:0.75rem;">Avg Order</h6>
                <h4 class="mb-0">R {{ number_format($stats['avg_order_value'], 2) }}</h4>
                <small class="text-muted">{{ $stats['total_orders'] }} total orders</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:1rem;">
                <h6 class="text-muted mb-1" style="font-size:0.75rem;">Outstanding</h6>
                <h4 class="mb-0 {{ $stats['outstanding_balance'] > 0 ? 'text-danger' : '' }}">R {{ number_format($stats['outstanding_balance'], 2) }}</h4>
                @if($stats['available_credit'] !== null)
                    <small class="text-muted">Available: R {{ number_format($stats['available_credit'], 2) }}</small>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:1rem;">
                <h6 class="text-muted mb-1" style="font-size:0.75rem;">Customer Since</h6>
                <h4 class="mb-0">{{ $stats['first_order'] ? $stats['first_order']->format('M Y') : 'N/A' }}</h4>
                <small class="text-muted">Last: {{ $stats['last_order'] ? $stats['last_order']->diffForHumans() : 'N/A' }}</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body" style="padding:1rem;">
                <h6 class="text-muted mb-1" style="font-size:0.75rem;">Activity</h6>
                <div>
                    <span class="badge badge-info">{{ $templates->count() }} templates</span>
                    <span class="badge badge-info">{{ $recurringOrders->count() }} recurring</span>
                    <span class="badge badge-secondary">{{ $wishlistCount }} wishlist</span>
                    <span class="badge badge-secondary">{{ $reviewCount }} reviews</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Spend Trend --}}
    @if($monthlySpend->count() > 1)
    <div class="card mb-3">
        <div class="card-header">Monthly Spend (12 months)</div>
        <div class="card-body">
            <div style="display:flex; align-items:flex-end; gap:4px; height:100px;">
                @php $maxSpend = $monthlySpend->max('total') ?: 1; @endphp
                @foreach($monthlySpend as $m)
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center;">
                        <small class="text-muted" style="font-size:0.55rem; margin-bottom:2px;">R{{ number_format($m->total, 0) }}</small>
                        <div style="width:100%; background:var(--primary, #3498db); border-radius:3px 3px 0 0; min-height:3px; height:{{ ($m->total / $maxSpend) * 80 }}px;" title="R {{ number_format($m->total, 2) }} ({{ $m->count }} orders)"></div>
                        <small class="text-muted" style="font-size:0.6rem; margin-top:2px;">{{ \Carbon\Carbon::parse($m->month . '-01')->format('M') }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Top Products & Status Breakdown --}}
    <div class="form-row" style="margin-bottom:1rem;">
        @if($topProducts->count() > 0)
        <div style="flex:1;">
            <div class="card">
                <div class="card-header">Top Products</div>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Value</th></tr></thead>
                        <tbody>
                        @foreach($topProducts->take(5) as $p)
                            <tr><td>{{ $p->name }}</td><td class="text-right">{{ number_format($p->total_qty, 1) }}</td><td class="text-right">R{{ number_format($p->total_value, 2) }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        @if($statusBreakdown->count() > 0)
        <div style="flex:1;">
            <div class="card">
                <div class="card-header">Order Status Breakdown</div>
                <div class="card-body">
                    @foreach($statusBreakdown as $status => $count)
                        <span class="badge badge-{{ match($status) { 'DELIVERED' => 'success', 'CANCELLED', 'REFUNDED' => 'danger', default => 'info' } }}" style="margin:2px; padding:0.4rem 0.8rem;">
                            {{ ucwords(str_replace('_', ' ', $status)) }}: {{ $count }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="form-row">
        <div>
            {{-- Company Details --}}
            <div class="card">
                <div class="card-header">Company / Business Details</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update-company', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group" style="flex:1"><label class="form-label">Company Name *</label><input type="text" name="name" class="form-control" required value="{{ old('name', $customer->company->name ?? '') }}"></div>
                            <div class="form-group" style="flex:1"><label class="form-label">Trading As</label><input type="text" name="trading_as" class="form-control" value="{{ old('trading_as', $customer->company->trading_as ?? '') }}"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1"><label class="form-label">Reg Number</label><input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $customer->company->registration_number ?? '') }}"></div>
                            <div class="form-group" style="flex:1"><label class="form-label">VAT Number</label><input type="text" name="vat_number" class="form-control" value="{{ old('vat_number', $customer->company->vat_number ?? '') }}"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $customer->company->email ?? '') }}"></div>
                            <div class="form-group" style="flex:1"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->company->phone ?? '') }}"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $customer->company->contact_person ?? '') }}"></div>
                        <div class="form-group"><label class="form-label">Search Address</label><input type="text" id="cust-company-address-search" class="form-control" placeholder="Start typing an address..." autocomplete="off"><small class="text-muted">Type to search. Fields below will auto-fill.</small></div>
                        <div class="form-group"><label class="form-label">Address Line 1</label><input type="text" name="address_line1" id="cust-company-line1" class="form-control" value="{{ old('address_line1', $customer->company->address_line1 ?? '') }}"></div>
                        <div class="form-group"><label class="form-label">Address Line 2</label><input type="text" name="address_line2" id="cust-company-line2" class="form-control" value="{{ old('address_line2', $customer->company->address_line2 ?? '') }}"></div>
                        <div class="form-row">
                            <div class="form-group" style="flex:1"><label class="form-label">City</label><input type="text" name="city" id="cust-company-city" class="form-control" value="{{ old('city', $customer->company->city ?? '') }}"></div>
                            <div class="form-group" style="flex:1"><label class="form-label">Province</label><select name="province" id="cust-company-province" class="form-control"><option value="">Select</option>@foreach(['Eastern Cape','Free State','Gauteng','KwaZulu-Natal','Limpopo','Mpumalanga','North West','Northern Cape','Western Cape'] as $prov)<option value="{{ $prov }}" {{ old('province', $customer->company->province ?? '') == $prov ? 'selected' : '' }}>{{ $prov }}</option>@endforeach</select></div>
                            <div class="form-group" style="flex:0.5"><label class="form-label">Postal Code</label><input type="text" name="postal_code" id="cust-company-postal" class="form-control" value="{{ old('postal_code', $customer->company->postal_code ?? '') }}"></div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-1">Save Company Details</button>
                    </form>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="card">
                <div class="card-header">Contact Information</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update-contact', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-row">
                            <div class="form-group" style="flex:1"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="{{ old('name', $customer->user->name) }}"></div>
                            <div class="form-group" style="flex:1"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="{{ old('email', $customer->user->email) }}"></div>
                        </div>
                        <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->user->phone ?? '') }}"></div>
                        <button type="submit" class="btn btn-primary mt-1">Save Contact Info</button>
                    </form>
                </div>
            </div>

            {{-- Settings --}}
            <div class="card">
                <div class="card-header">Customer Settings</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label class="form-label">Account Type</label>
                            <select name="type" class="form-control">
                                <option value="COD" {{ $customer->type == 'COD' ? 'selected' : '' }}>COD - Cash On Delivery</option>
                                <option value="ACCOUNT" {{ $customer->type == 'ACCOUNT' ? 'selected' : '' }}>Account - Credit Terms</option>
                            </select>
                            <div class="form-hint">COD = pay on delivery/before dispatch. Account = pay on credit terms.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Credit Limit (R)</label>
                            <input type="number" name="credit_limit" class="form-control" step="0.01" value="{{ $customer->credit_limit }}">
                            @if($stats['outstanding_balance'] > 0)
                                <div class="form-hint" style="color:var(--danger, #e74c3c);">Current outstanding: R {{ number_format($stats['outstanding_balance'], 2) }}</div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Terms</label>
                            <input type="text" name="payment_terms" class="form-control" value="{{ $customer->payment_terms }}" placeholder="e.g. 30 days, 7 days, COD">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="pay_before_dispatch" value="1" id="pay_before_dispatch" {{ $customer->pay_before_dispatch ? 'checked' : '' }}>
                            <label for="pay_before_dispatch">Pay before dispatch</label>
                        </div>
                        <div class="form-hint">When checked, orders must be paid before they can be dispatched.</div>
                        <button type="submit" class="btn btn-primary mt-2">Save Settings</button>
                    </form>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="card">
                <div class="card-header"><span>Delivery Addresses</span> <span class="badge badge-secondary">{{ $customer->addresses->count() }}</span></div>
                <div class="card-body">
                    @forelse($customer->addresses as $addr)
                        <div class="info-row">
                            <span class="label">{{ $addr->label ?? 'Address' }}</span>
                            <span class="value">{{ $addr->full_address }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No addresses on file.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            {{-- Recent Orders --}}
            <div class="card">
                <div class="card-header">
                    <span>Recent Orders</span>
                    <span class="badge badge-secondary">{{ $stats['total_orders'] }}</span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Order #</th><th>Status</th><th>Payment</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                        @forelse($customer->orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold">{{ $order->order_number }}</a>
                                    <br><small class="text-muted">{{ $order->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ match($order->status) {
                                        'DELIVERED' => 'success',
                                        'CANCELLED', 'REFUNDED' => 'danger',
                                        'IN_TRANSIT', 'LOADED' => 'warning',
                                        default => 'info'
                                    } }}">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td>
                                    @if($order->payments->where('status', 'completed')->count() > 0)
                                        <span class="badge badge-success">Paid</span>
                                    @else
                                        <span class="badge badge-warning">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-right font-semibold">R{{ number_format($order->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted p-3">No orders yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($templates->count() > 0)
            <div class="card">
                <div class="card-header"><span>Saved Templates</span> <span class="badge badge-secondary">{{ $templates->count() }}</span></div>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Name</th><th>Items</th></tr></thead>
                        <tbody>
                        @foreach($templates as $t)
                            <tr><td><a href="{{ route('admin.order-templates.show', $t) }}">{{ $t->name }}</a></td><td>{{ count($t->items ?? []) }} items</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if($recurringOrders->count() > 0)
            <div class="card">
                <div class="card-header"><span>Recurring Orders</span> <span class="badge badge-secondary">{{ $recurringOrders->count() }}</span></div>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Template</th><th>Frequency</th><th>Next Run</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($recurringOrders as $ro)
                            <tr>
                                <td><a href="{{ route('admin.recurring-orders.show', $ro) }}">{{ $ro->template?->name ?? '#'.$ro->id }}</a></td>
                                <td>{{ ucfirst($ro->frequency) }}</td>
                                <td>{{ $ro->next_run_date?->format('d M Y') ?? '-' }}</td>
                                <td><span class="badge badge-{{ $ro->is_active ? 'success' : 'warning' }}">{{ $ro->is_active ? 'Active' : 'Paused' }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@include('partials.address-autocomplete')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initMapboxAutocomplete('cust-company-address-search', {
        line1: 'cust-company-line1',
        line2: 'cust-company-line2',
        city: 'cust-company-city',
        province: 'cust-company-province',
        postal: 'cust-company-postal'
    });
});
</script>
@endpush
