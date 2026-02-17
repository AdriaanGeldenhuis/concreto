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
                            <td style="max-width:300px; font-size:0.8rem;">
                                @if($log->meta)
                                    <button class="btn btn-sm btn-outline-secondary audit-detail-btn"
                                            data-meta="{{ e(json_encode($log->meta, JSON_PRETTY_PRINT)) }}"
                                            data-action="{{ e($log->action) }}"
                                            data-entity="{{ e($log->entity) }}"
                                            data-entity-id="{{ e($log->entity_id ?? '-') }}">View</button>
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

{{-- Audit Detail Modal --}}
<div class="modal fade" id="auditDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Detail: <code id="auditDetailTitle"></code></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="auditDetailContent" class="mb-0 p-3 rounded" style="background: rgba(0,0,0,0.15); max-height: 500px; overflow: auto; font-size: 0.8125rem;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="navigator.clipboard.writeText(document.getElementById('auditDetailContent').textContent)">Copy to Clipboard</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.audit-detail-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('auditDetailTitle').textContent = this.dataset.action + ' ' + this.dataset.entity + ' #' + this.dataset.entityId;
        document.getElementById('auditDetailContent').textContent = this.dataset.meta;
        new bootstrap.Modal(document.getElementById('auditDetailModal')).show();
    });
});
</script>
@endpush
@endsection
