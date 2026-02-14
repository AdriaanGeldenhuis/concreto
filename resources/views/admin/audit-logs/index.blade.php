@extends('layouts.admin')
@section('title', 'Audit Log')
@section('content')
    <div class="page-header"><h1>Audit Log</h1></div>
    <form class="filters" method="GET">
        <div class="form-group"><input type="text" name="entity" class="form-control" placeholder="Entity (Order, Product...)" value="{{ request('entity') }}"></div>
        <div class="form-group"><input type="text" name="action" class="form-control" placeholder="Action (created, updated...)" value="{{ request('action') }}"></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>Date</th><th>User</th><th>Action</th><th>Entity</th><th>ID</th><th>Details</th></tr></thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    <td>{{ $log->actor->name ?? 'System' }}</td>
                    <td><span class="badge badge-secondary">{{ $log->action }}</span></td>
                    <td>{{ $log->entity }}</td>
                    <td>{{ $log->entity_id ?? '-' }}</td>
                    <td class="text-small">{{ $log->meta ? json_encode($log->meta) : '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="pagination">{!! $logs->appends(request()->query())->links('pagination::simple-default') !!}</div>
@endsection
