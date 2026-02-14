@extends('layouts.admin')
@section('title', 'Quotes')
@section('content')
    <div class="page-header d-flex justify-between items-center flex-wrap gap-1">
        <h1>Quotes</h1>
        <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary">Create Quote</a>
    </div>
    <div class="card"><div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Customer</th><th>Status</th><th>Total</th><th>Expires</th><th></th></tr></thead>
            <tbody>
            @foreach($quotes as $quote)
                <tr>
                    <td>Q-{{ $quote->id }}</td><td>{{ $quote->customer->user->name ?? '-' }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($quote->status) }}</span></td>
                    <td>R{{ number_format($quote->total, 2) }}</td>
                    <td>{{ $quote->expires_at ? $quote->expires_at->format('d M Y') : '-' }}</td>
                    <td>
                        @if($quote->status == 'draft')
                        <form method="POST" action="{{ route('admin.quotes.send', $quote) }}" style="display:inline;">@csrf
                            <button type="submit" class="btn btn-sm btn-success">Send</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>
@endsection
