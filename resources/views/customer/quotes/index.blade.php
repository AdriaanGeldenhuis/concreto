@extends('layouts.portal')
@section('title', 'Quotes')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))

@section('content')
    <div class="page-header"><h1>My Quotes</h1></div>
    @if($quotes->count())
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Status</th><th>Total</th><th>Expires</th><th></th></tr></thead>
            <tbody>
            @foreach($quotes as $quote)
                <tr>
                    <td>Q-{{ $quote->id }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($quote->status) }}</span></td>
                    <td>R{{ number_format($quote->total, 2) }}</td>
                    <td>{{ $quote->expires_at ? $quote->expires_at->format('d M Y') : '-' }}</td>
                    <td><a href="{{ route('customer.quotes.show', $quote) }}" class="btn btn-sm btn-outline">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
    @else
        <div class="empty-state"><div class="icon">&#9997;</div><p>No quotes yet.</p></div>
    @endif
@endsection
