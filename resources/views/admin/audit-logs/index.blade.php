@extends('layouts.admin')
@section('title', 'Audit Log')
@section('content')
    <div class="page-header">
        <h1>Audit Log</h1>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET">
                <div class="form-group">
                    <label class="form-label">Entity</label>
                    <input type="text" name="entity" class="form-control" placeholder="e.g. Order, Product..." value="{{ request('entity') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Action</label>
                    <input type="text" name="action" class="form-control" placeholder="e.g. created, updated..." value="{{ request('action') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['entity', 'action']))
                        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
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
                        <td class="text-muted text-small" style="white-space:nowrap;">
                            {{ $log->created_at->format('d M Y H:i:s') }}
                        </td>
                        <td class="font-semibold">{{ $log->actor->name ?? 'System' }}</td>
                        <td>
                            <span class="badge badge-{{ match($log->action) {
                                'created' => 'success',
                                'deleted' => 'danger',
                                'updated' => 'info',
                                default => 'secondary'
                            } }}">{{ $log->action }}</span>
                        </td>
                        <td>{{ $log->entity }}</td>
                        <td class="text-muted">{{ $log->entity_id ?? '-' }}</td>
                        <td class="text-small text-muted" style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            @if($log->meta)
                                {{ json_encode($log->meta) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon">&#9201;</div>
                                <h3>No log entries</h3>
                                <p>Audit log entries will appear here as actions are performed.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="pagination">{!! $logs->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
@endsection
