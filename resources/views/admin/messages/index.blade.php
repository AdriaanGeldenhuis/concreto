@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
    <div class="d-flex items-center justify-between mb-2 flex-wrap gap-1">
        <h1>Messages & Quote Requests</h1>
    </div>

    <div class="filters">
        <form method="GET" class="d-flex gap-1 flex-wrap items-center">
            <div class="form-group">
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="contact" {{ request('type') == 'contact' ? 'selected' : '' }}>Contact Messages</option>
                    <option value="quote_request" {{ request('type') == 'quote_request' ? 'selected' : '' }}>Quote Requests</option>
                </select>
            </div>
            <div class="form-group">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                </select>
            </div>
        </form>
    </div>

    <div class="card">
        @if($messages->count())
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $msg)
                    <tr style="{{ $msg->status === 'new' ? 'font-weight:700;' : '' }}">
                        <td>
                            @if($msg->type === 'quote_request')
                                <span class="badge badge-primary">Quote</span>
                            @else
                                <span class="badge badge-info">Contact</span>
                            @endif
                        </td>
                        <td>{{ $msg->name }}</td>
                        <td>{{ $msg->email }}</td>
                        <td>
                            @if($msg->status === 'new')
                                <span class="badge badge-warning">New</span>
                            @elseif($msg->status === 'read')
                                <span class="badge badge-info">Read</span>
                            @else
                                <span class="badge badge-success">Replied</span>
                            @endif
                        </td>
                        <td>{{ $msg->created_at->format('d M Y H:i') }}</td>
                        <td><a href="{{ route('admin.messages.show', $msg) }}" class="btn btn-outline btn-sm">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $messages->withQueryString()->links() }}
        </div>
        @else
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#9993;</div>
                <p>No messages yet.</p>
            </div>
        </div>
        @endif
    </div>
@endsection
