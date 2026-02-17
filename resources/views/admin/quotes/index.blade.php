@extends('layouts.admin')
@section('title', 'Quotes')
@section('content')
    <div class="page-header">
        <h1>Quotes</h1>
        <span class="badge badge-secondary">{{ $quotes->total() }} total</span>
        <div style="margin-left: auto; display:flex; gap:0.5rem;">
            <a href="{{ route('admin.quotes.export', request()->query()) }}" class="btn btn-outline btn-sm">Export CSV</a>
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary btn-sm">+ Create Quote</a>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <form class="filters" method="GET" style="flex-wrap:wrap;">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Quote # or customer..." value="{{ request('search') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if(request()->hasAny(['status', 'search', 'from', 'to']))
                        <a href="{{ route('admin.quotes.index') }}" class="btn btn-ghost btn-sm">Clear</a>
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
                        <th>Quote #</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                        <th>Expires</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td><a href="{{ route('admin.quotes.show', $quote) }}" class="font-semibold">{{ $quote->quote_number ?? 'Q-' . $quote->id }}</a></td>
                        <td>{{ $quote->customer->user->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ match($quote->status) {
                                'approved' => 'success',
                                'rejected', 'expired' => 'danger',
                                'sent' => 'info',
                                'converted' => 'primary',
                                default => 'secondary'
                            } }}">{{ ucfirst($quote->status) }}</span>
                        </td>
                        <td class="text-right font-semibold">R{{ number_format($quote->total, 2) }}</td>
                        <td class="text-muted">
                            @if($quote->expires_at)
                                {{ $quote->expires_at->format('d M Y') }}
                                @if($quote->expires_at->isPast())
                                    <span class="badge badge-danger">Expired</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $quote->created_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <div class="d-flex gap-1" style="justify-content:flex-end;">
                                <a href="{{ route('admin.quotes.show', $quote) }}" class="btn btn-sm btn-outline">View</a>
                                @if(in_array($quote->status, ['draft', 'sent']))
                                    <form method="POST" action="{{ route('admin.quotes.send', $quote) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">{{ $quote->status === 'sent' ? 'Resend' : 'Send' }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="icon">&#9997;</div>
                                <h3>No quotes found</h3>
                                <p>Create your first quote for a customer.</p>
                                <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">Create Quote</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($quotes->hasPages())
        <div class="pagination">{!! $quotes->appends(request()->query())->links('pagination::simple-default') !!}</div>
    @endif
@endsection
