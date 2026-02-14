@extends('layouts.portal')
@section('title', 'Capture Signature')
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))
@section('nav-links')
    <a href="{{ route('driver.dashboard') }}">Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}">All Jobs</a>
@endsection

@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('driver.dashboard') }}">Dashboard</a> /
            <a href="{{ route('driver.jobs.show', $order) }}">{{ $order->order_number }}</a> /
            Signature
        </div>
        <h1>Capture Signature</h1>
        <p class="text-muted mb-0">Complete delivery for {{ $order->order_number }}</p>
    </div>

    <form method="POST" action="{{ route('driver.jobs.signature.store', $order) }}" enctype="multipart/form-data" id="signature-form">
        @csrf
        <input type="hidden" name="signature" id="signature-data">
        <input type="hidden" name="gps_lat" id="gps-lat">
        <input type="hidden" name="gps_lng" id="gps-lng">

        {{-- Delivery summary --}}
        <div class="card">
            <div class="card-header">
                <span>Delivery Summary</span>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="label">Customer</span>
                    <span class="value">{{ $order->customer->user->name }}</span>
                </div>
                @if($order->deliveryAddress)
                    <div class="info-row">
                        <span class="label">Address</span>
                        <span class="value text-small">{{ Str::limit($order->deliveryAddress->full_address, 40) }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="label">Items</span>
                    <span class="value">{{ $order->items->count() }} item(s)</span>
                </div>
            </div>
        </div>

        {{-- Recipient name --}}
        <div class="card">
            <div class="card-header">Recipient Details</div>
            <div class="card-body">
                <div class="form-group mb-0">
                    <label class="form-label" for="signer_name">Recipient Name</label>
                    <input type="text"
                           name="signer_name"
                           id="signer_name"
                           class="form-control"
                           required
                           autocomplete="off"
                           placeholder="Name of person receiving delivery"
                           value="{{ old('signer_name') }}"
                           style="min-height:50px; font-size:1rem;">
                    @error('signer_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Signature canvas --}}
        <div class="card">
            <div class="card-header">
                <span>Signature</span>
                <button type="button" id="clear-signature" class="btn btn-sm btn-outline">Clear</button>
            </div>
            <div class="card-body">
                <div class="signature-pad-wrapper">
                    <canvas id="signature-canvas"></canvas>
                </div>
                @error('signature')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <p class="text-small text-muted mt-1">Sign with your finger or stylus above</p>
            </div>
        </div>

        {{-- Photo capture --}}
        <div class="card">
            <div class="card-header">Photo Evidence <span class="text-muted text-small font-normal">(optional)</span></div>
            <div class="card-body">
                <label for="photo-input" class="btn btn-outline btn-block btn-lg" style="min-height:56px; cursor:pointer;">
                    &#128247; Take Photo of Delivery
                </label>
                <input type="file"
                       name="photo"
                       id="photo-input"
                       accept="image/*"
                       capture="environment"
                       class="hidden">
                <div id="photo-preview" class="mt-1 hidden">
                    <p class="text-small text-success font-semibold">&#10003; Photo attached</p>
                </div>
                <p class="text-small text-muted mt-1">Take a photo of the delivered goods as proof</p>
            </div>
        </div>

        {{-- GPS status --}}
        <div class="card">
            <div class="card-body d-flex items-center gap-1">
                <span id="gps-status" class="text-small text-muted">&#128205; Acquiring GPS location...</span>
            </div>
        </div>

        {{-- Submit --}}
        <div class="action-buttons">
            <button type="submit" class="btn btn-success btn-block btn-lg" style="min-height:56px;">
                &#10003; Complete Delivery
            </button>
            <a href="{{ route('driver.jobs.show', $order) }}" class="btn btn-ghost btn-block btn-lg" style="min-height:56px;">
                Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // GPS capture for proof of delivery
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                document.getElementById('gps-lat').value = pos.coords.latitude;
                document.getElementById('gps-lng').value = pos.coords.longitude;
                document.getElementById('gps-status').innerHTML = '&#10003; GPS location captured';
                document.getElementById('gps-status').className = 'text-small text-success font-semibold';
            },
            function(err) {
                document.getElementById('gps-status').innerHTML = '&#9888; GPS unavailable - delivery will still be recorded';
                document.getElementById('gps-status').className = 'text-small text-warning';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
        );
    } else {
        document.getElementById('gps-status').innerHTML = '&#9888; GPS not supported on this device';
        document.getElementById('gps-status').className = 'text-small text-warning';
    }

    // Photo preview feedback
    var photoInput = document.getElementById('photo-input');
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            var preview = document.getElementById('photo-preview');
            if (this.files && this.files.length > 0) {
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
        });
    }
</script>
@endpush

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('driver.jobs.index') }}"><span class="nav-icon">&#9744;</span>All Jobs</a>
</nav>
@endsection
