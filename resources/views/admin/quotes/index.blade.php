@extends('layouts.admin')
@section('title', 'Quotes')
@section('content')
    <div class="page-header">
        <h1>Quotes</h1>
        <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">Create Quote</a>
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
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td class="font-semibold">Q-{{ $quote->id }}</td>
                        <td>{{ $quote->customer->user->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ match($quote->status) {
                                'accepted' => 'success',
                                'declined', 'expired' => 'danger',
                                'sent' => 'info',
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
                        <td class="text-right">
                            @if($quote->status == 'draft')
                                <form method="POST" action="{{ route('admin.quotes.send', $quote) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Send</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon">&#9997;</div>
                                <h3>No quotes yet</h3>
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
@endsection
