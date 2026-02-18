@extends('layouts.admin')
@section('title', 'Messages')
@section('content')
    <div class="page-header">
        <h1>Messages & Quote Requests</h1>
    </div>

    {{-- Stats --}}
    @php
        $newCount = $messages->where('status', 'new')->count();
        $readCount = $messages->where('status', 'read')->count();
        $repliedCount = $messages->where('status', 'replied')->count();
        $quoteCount = $messages->where('type', 'quote_request')->count();
    @endphp
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Total</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $messages->total() }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">New</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--warning, #e67e22);">{{ $newCount }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Read</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $readCount }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Replied</h6><h3 class="mb-0" style="margin-top:0.25rem; color:var(--success, #27ae60);">{{ $repliedCount }}</h3></div></div>
        <div class="card"><div class="card-body" style="padding:0.75rem; text-align:center;"><h6 class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase;">Quotes</h6><h3 class="mb-0" style="margin-top:0.25rem;">{{ $quoteCount }}</h3></div></div>
    </div>

    {{-- Filters --}}
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filters">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="contact" {{ request('type') == 'contact' ? 'selected' : '' }}>Contact Messages</option>
                        <option value="quote_request" {{ request('type') == 'quote_request' ? 'selected' : '' }}>Quote Requests</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                </div>
                @if(request()->hasAny(['type', 'status']))
                    <div class="form-group">
                        <a href="{{ route('admin.messages.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                    </div>
                @endif
            </form>
        </div>
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
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
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
                        <td class="font-semibold">{{ $msg->name }}</td>
                        <td><a href="mailto:{{ $msg->email }}">{{ $msg->email }}</a></td>
                        <td>@if($msg->phone)<a href="tel:{{ $msg->phone }}">{{ $msg->phone }}</a>@else <span class="text-muted">-</span> @endif</td>
                        <td>
                            @if($msg->status === 'new')
                                <span class="badge badge-warning">New</span>
                            @elseif($msg->status === 'read')
                                <span class="badge badge-info">Read</span>
                            @else
                                <span class="badge badge-success">Replied</span>
                            @endif
                        </td>
                        <td><small>{{ $msg->created_at->format('d M Y H:i') }}</small></td>
                        <td class="text-right"><a href="{{ route('admin.messages.show', $msg) }}" class="btn btn-outline btn-sm">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="pagination" style="padding:0.75rem;">{!! $messages->withQueryString()->links('pagination::simple-default') !!}</div>
        @endif
        @else
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#9993;</div>
                <h3>No messages yet</h3>
                <p>Messages from your contact form and quote requests will appear here.</p>
            </div>
        </div>
        @endif
    </div>
@endsection
