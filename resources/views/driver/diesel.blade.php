@extends('layouts.portal')
@section('title', 'Diesel Log')
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))
@section('nav-links')
    <a href="{{ route('driver.dashboard') }}">Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}">All Jobs</a>
    <a href="{{ route('driver.diesel.index') }}" class="active">Diesel</a>
@endsection

@section('content')
    <div class="page-header">
        <h1>Diesel Log</h1>
    </div>

    {{-- Month Stats --}}
    <div class="card mb-2">
        <div class="card-header">This Month</div>
        <div class="card-body">
            <div class="d-flex gap-2">
                <div style="flex: 1;">
                    <div class="stat-value">R {{ number_format($monthTotal, 2) }}</div>
                    <div class="stat-label">Total Cost</div>
                </div>
                <div style="flex: 1;">
                    <div class="stat-value">{{ number_format($monthLitres, 1) }}</div>
                    <div class="stat-label">Litres</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Log Form --}}
    <div class="card mb-2">
        <div class="card-header">Log Fill-up</div>
        <div class="card-body">
            <form method="POST" action="{{ route('driver.diesel.store') }}">
                @csrf
                <div class="mb-2">
                    <label class="text-small text-muted">Date</label>
                    <input type="date" name="fill_date" class="form-control" value="{{ date('Y-m-d') }}" required max="{{ date('Y-m-d') }}">
                </div>
                <div class="d-flex gap-2 mb-2">
                    <div style="flex: 1;">
                        <label class="text-small text-muted">Litres</label>
                        <input type="number" name="litres" class="form-control" required min="0.01" step="0.01" id="d-litres" oninput="dCalc()">
                    </div>
                    <div style="flex: 1;">
                        <label class="text-small text-muted">Cost/L (R)</label>
                        <input type="number" name="cost_per_litre" class="form-control" required min="0.01" step="0.01" id="d-cpl" oninput="dCalc()">
                    </div>
                </div>
                <div class="mb-2 p-2" style="background: rgba(255,193,7,0.1); border-radius: 6px; text-align: center;">
                    <strong>Total: R <span id="d-total">0.00</span></strong>
                </div>
                <div class="d-flex gap-2 mb-2">
                    <div style="flex: 1;">
                        <label class="text-small text-muted">Odometer (km)</label>
                        <input type="number" name="odometer" class="form-control" min="0" step="0.1">
                    </div>
                    <div style="flex: 1;">
                        <label class="text-small text-muted">Station</label>
                        <input type="text" name="station" class="form-control" placeholder="e.g. Shell N1">
                    </div>
                </div>
                @php $vehicles = \App\Models\Vehicle::where('is_active', true)->orderBy('registration')->get(); @endphp
                @if($vehicles->count())
                <div class="mb-2">
                    <label class="text-small text-muted">Vehicle</label>
                    <select name="vehicle_id" class="form-control">
                        <option value="">Select vehicle</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->registration }} - {{ $v->make }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="mb-2">
                    <label style="display:flex;align-items:center;gap:6px;">
                        <input type="checkbox" name="full_tank" value="1" checked> Full tank
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Log Diesel</button>
            </form>
        </div>
    </div>

    {{-- Recent Logs --}}
    <h3 class="mb-2">Recent Fill-ups</h3>
    @forelse($logs as $log)
        <div class="card mb-1">
            <div class="card-body" style="padding: 0.75rem;">
                <div class="d-flex justify-between items-center">
                    <div>
                        <div class="font-semibold">R {{ number_format($log->total_cost, 2) }}</div>
                        <div class="text-small text-muted">{{ $log->fill_date->format('d M Y') }} &middot; {{ number_format($log->litres, 1) }}L @ R{{ number_format($log->cost_per_litre, 2) }}/L</div>
                        @if($log->station)<div class="text-small text-muted">{{ $log->station }}</div>@endif
                        @if($log->vehicle)<div class="text-small text-muted">{{ $log->vehicle->registration }}</div>@endif
                    </div>
                    @if($log->odometer)
                    <div class="text-end">
                        <div class="text-small text-muted">{{ number_format($log->odometer, 0) }} km</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted">
                No diesel logs yet. Log your first fill-up above.
            </div>
        </div>
    @endforelse

    <div class="mt-2">{{ $logs->links() }}</div>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}"><span class="nav-icon">&#9744;</span>All Jobs</a>
    <a href="{{ route('driver.diesel.index') }}" class="active"><span class="nav-icon">&#9981;</span>Diesel</a>
</nav>
@endsection

<script>
function dCalc() {
    const l = parseFloat(document.getElementById('d-litres').value) || 0;
    const c = parseFloat(document.getElementById('d-cpl').value) || 0;
    document.getElementById('d-total').textContent = (l * c).toFixed(2);
}
</script>
