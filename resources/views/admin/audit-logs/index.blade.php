@extends('layouts.admin')
@section('title', 'Audit Log')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Audit Log</h1>
            <small class="text-muted">Track all admin actions: settings changes, order updates, user management, and more.</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">Entity</label>
                    <input type="text" name="entity" class="form-control form-control-sm" placeholder="e.g. Order, Product, Setting..." value="{{ request('entity') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">Action</label>
                    <input type="text" name="action" class="form-control form-control-sm" placeholder="e.g. created, updated, deleted..." value="{{ request('action') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem;">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['entity', 'action', 'from', 'to']))
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>ID</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted text-nowrap">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="fw-bold">{{ $log->actor->name ?? 'System' }}</td>
                            <td>
                                @php
                                    $actionColor = match($log->action) {
                                        'created' => 'success',
                                        'deleted' => 'danger',
                                        'updated' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $actionColor }}">{{ $log->action }}</span>
                            </td>
                            <td>{{ $log->entity }}</td>
                            <td class="text-muted">{{ $log->entity_id ?? '-' }}</td>
                            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:0.8rem;" title="{{ $log->meta ? json_encode($log->meta) : '' }}">
                                @if($log->meta)
                                    <code class="text-muted">{{ json_encode($log->meta) }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No log entries found. Audit log entries will appear here as actions are performed.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-3">{!! $logs->appends(request()->query())->links() !!}</div>
    @endif
</div>
@endsection
