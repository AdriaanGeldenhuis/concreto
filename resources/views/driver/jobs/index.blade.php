@extends('layouts.portal')
@section('title', 'All Jobs')
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))

@section('content')
    <div class="page-header"><h1>All Jobs</h1></div>
    @foreach($orders as $order)
    <div class="card">
        <div class="card-header">
            <span>{{ $order->order_number }}</span>
            <span class="badge badge-primary">{{ str_replace('_', ' ', $order->status) }}</span>
        </div>
        <div class="card-body">
            @if($order->scheduled_date)<p>{{ $order->scheduled_date->format('d M Y') }}</p>@endif
            <a href="{{ route('driver.jobs.show', $order) }}" class="btn btn-primary btn-sm">View</a>
        </div>
    </div>
    @endforeach
    <div class="pagination">{!! $orders->links('pagination::simple-default') !!}</div>
@endsection

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"><span class="nav-icon">&#9632;</span>Home</a>
    <a href="{{ route('driver.jobs.index') }}" class="active"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
