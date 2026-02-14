@extends('layouts.portal')
@section('title', 'Capture Signature')
@section('portal-name', 'Driver')
@section('home-url', route('driver.dashboard'))

@section('content')
    <div class="page-header">
        <h1>Capture Signature</h1>
        <p class="text-muted">Order {{ $order->order_number }}</p>
    </div>

    <form method="POST" action="{{ route('driver.jobs.signature.store', $order) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="signature" id="signature-data">

        <div class="card">
            <div class="card-header">Recipient Details</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Recipient Name</label>
                    <input type="text" name="signer_name" class="form-control" required placeholder="Name of person receiving delivery">
                    @error('signer_name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span>Signature</span>
                <button type="button" id="clear-signature" class="btn btn-sm btn-outline">Clear</button>
            </div>
            <div class="card-body">
                <div class="signature-pad-wrapper">
                    <canvas id="signature-canvas"></canvas>
                </div>
                @error('signature')<div class="form-error">{{ $message }}</div>@enderror
                <p class="text-small text-muted mt-1">Sign with your finger or stylus</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Photo (optional)</div>
            <div class="card-body">
                <input type="file" name="photo" accept="image/*" capture="environment" class="form-control">
                <p class="text-small text-muted mt-1">Take a photo of the delivered goods</p>
            </div>
        </div>

        <input type="hidden" name="gps_lat" id="gps-lat">
        <input type="hidden" name="gps_lng" id="gps-lng">

        <button type="submit" class="btn btn-success btn-lg btn-block">Complete Delivery</button>
    </form>
@endsection

@push('scripts')
<script>
    // Get GPS location for proof of delivery
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('gps-lat').value = pos.coords.latitude;
            document.getElementById('gps-lng').value = pos.coords.longitude;
        });
    }
</script>
@endpush
