@extends('layouts.portal')
@section('title', 'Addresses')
@section('portal-name', 'My Account')
@section('home-url', route('customer.dashboard'))


@section('content')
    <div class="page-header">
        <h1>My Addresses</h1>
    </div>

    <div class="card">
        <div class="card-header">Add New Address</div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.addresses.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Search Address</label>
                    <input type="text" id="cust-address-search" class="form-control" placeholder="Start typing an address..." autocomplete="off">
                    <small class="text-muted">Type to search via Google Maps. Fields below will auto-fill.</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control"
                               placeholder="e.g. Site, Office, Warehouse"
                               value="{{ old('label') }}">
                        @error('label')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" name="line1" id="cust-line1" class="form-control" required
                               value="{{ old('line1') }}">
                        @error('line1')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="line2" id="cust-line2" class="form-control"
                               value="{{ old('line2') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="cust-city" class="form-control" required
                               value="{{ old('city') }}">
                        @error('city')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <input type="text" name="province" id="cust-province" class="form-control" required
                               value="{{ old('province') }}">
                        @error('province')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" id="cust-postal" class="form-control" required
                               value="{{ old('postal_code') }}">
                        @error('postal_code')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <input type="hidden" name="gps_lat" id="cust-lat" value="{{ old('gps_lat') }}">
                <input type="hidden" name="gps_lng" id="cust-lng" value="{{ old('gps_lng') }}">
                <button type="submit" class="btn btn-primary">Add Address</button>
            </form>
        </div>
    </div>

    @if(count($addresses))
        <div class="page-header mt-2">
            <h2 class="text-muted" style="font-size:1.125rem;">Saved Addresses</h2>
        </div>

        @foreach($addresses as $address)
        <div class="card">
            <div class="card-body d-flex justify-between items-center flex-wrap gap-1">
                <div>
                    @if($address->label)
                        <strong>{{ $address->label }}</strong><br>
                    @endif
                    <span class="text-muted">{{ $address->full_address }}</span>
                </div>
                <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Remove this address?')">Remove</button>
                </form>
            </div>
        </div>
        @endforeach
    @endif
@endsection

@php $gmKey = $siteSettings['google_maps_api_key'] ?? ''; @endphp
@if($gmKey)
@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $gmKey }}&libraries=places&callback=initCustAutocomplete" async defer></script>
<script>
function initCustAutocomplete() {
    var input = document.getElementById('cust-address-search');
    if (!input) return;
    var ac = new google.maps.places.Autocomplete(input, { componentRestrictions: { country: 'za' }, fields: ['address_components', 'geometry'] });
    ac.addListener('place_changed', function() {
        var place = ac.getPlace();
        if (!place.geometry) return;
        var c = {};
        place.address_components.forEach(function(comp) {
            comp.types.forEach(function(t) { c[t] = comp.long_name; });
        });
        document.getElementById('cust-line1').value = [(c.street_number || ''), (c.route || '')].filter(Boolean).join(' ');
        document.getElementById('cust-line2').value = c.sublocality || c.sublocality_level_1 || '';
        document.getElementById('cust-city').value = c.locality || c.sublocality || c.administrative_area_level_2 || '';
        document.getElementById('cust-province').value = c.administrative_area_level_1 || '';
        document.getElementById('cust-postal').value = c.postal_code || '';
        document.getElementById('cust-lat').value = place.geometry.location.lat();
        document.getElementById('cust-lng').value = place.geometry.location.lng();
    });
}
</script>
@endpush
@endif

@section('bottom-nav')
<nav class="bottom-nav">
    <a href="{{ route('customer.dashboard') }}"><span class="nav-icon">&#9632;</span>Dashboard</a>
    <a href="{{ route('customer.orders.index') }}"><span class="nav-icon">&#9744;</span>Orders</a>
    <a href="{{ route('customer.invoices.index') }}"><span class="nav-icon">&#9993;</span>Invoices</a>
    <a href="{{ route('customer.account') }}" class="active"><span class="nav-icon">&#9786;</span>Account</a>
</nav>
@endsection
