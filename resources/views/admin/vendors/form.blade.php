@extends('layouts.admin')
@section('title', isset($vendor) ? 'Edit Vendor' : 'New Vendor')
@section('content')
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.vendors.index') }}">Vendors</a> / {{ isset($vendor) ? 'Edit' : 'New' }}
        </div>
        <h1>{{ isset($vendor) ? 'Edit' : 'New' }} Vendor</h1>
    </div>

    <form method="POST" action="{{ isset($vendor) ? route('admin.vendors.update', $vendor) : route('admin.vendors.store') }}">
        @csrf
        @if(isset($vendor)) @method('PUT') @endif

        <div class="card">
            <div class="card-header">Vendor Details</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Vendor / Company Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name ?? '') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $vendor->contact_person ?? '') }}">
                        @error('contact_person')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email ?? '') }}" required>
                        <div class="form-hint">Order notifications will be sent to this email</div>
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone ?? '') }}">
                        @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $vendor->is_active ?? true) ? 'checked' : '' }}>
                        <label for="is_active">Active (receives order notifications)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Address</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Search Address</label>
                    <input type="text" id="vendor-address-search" class="form-control" placeholder="Start typing an address..." autocomplete="off">
                    <small class="text-muted">Type to search via Google Maps. Fields below will auto-fill.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" name="address_line1" id="vendor-line1" class="form-control" value="{{ old('address_line1', $vendor->address_line1 ?? '') }}">
                    @error('address_line1')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line2" id="vendor-line2" class="form-control" value="{{ old('address_line2', $vendor->address_line2 ?? '') }}">
                    @error('address_line2')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="vendor-city" class="form-control" value="{{ old('city', $vendor->city ?? '') }}">
                        @error('city')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Province</label>
                        <input type="text" name="province" id="vendor-province" class="form-control" value="{{ old('province', $vendor->province ?? '') }}">
                        @error('province')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" id="vendor-postal" class="form-control" value="{{ old('postal_code', $vendor->postal_code ?? '') }}">
                        @error('postal_code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <input type="hidden" name="gps_lat" id="vendor-lat" value="{{ old('gps_lat', $vendor->gps_lat ?? '') }}">
                <input type="hidden" name="gps_lng" id="vendor-lng" value="{{ old('gps_lng', $vendor->gps_lng ?? '') }}">
                <div id="vendor-gps-display" class="form-hint" style="margin-top:0.5rem;">
                    @if(old('gps_lat', $vendor->gps_lat ?? ''))
                        GPS: {{ old('gps_lat', $vendor->gps_lat ?? '') }}, {{ old('gps_lng', $vendor->gps_lng ?? '') }}
                    @else
                        GPS coordinates will be set automatically from the address search.
                    @endif
                </div>
            </div>
        </div>

        @if(!isset($vendor))
        <div class="card">
            <div class="card-header">User Account (Optional)</div>
            <div class="card-body">
                <p class="text-muted" style="margin: 0 0 16px; font-size: 14px;">
                    Create a user account for this vendor to enable push notifications and app access.
                </p>
                <div class="form-row">
                    <div class="form-check">
                        <input type="checkbox" name="create_user_account" value="1" id="create_user_account"
                            {{ old('create_user_account') ? 'checked' : '' }}
                            onchange="document.getElementById('user-password-field').style.display = this.checked ? 'block' : 'none'">
                        <label for="create_user_account">Create user account for this vendor</label>
                    </div>
                </div>
                <div id="user-password-field" style="display: {{ old('create_user_account') ? 'block' : 'none' }};">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="user_password" class="form-control" minlength="8">
                        <div class="form-hint">Minimum 8 characters. The vendor will use their email to log in.</div>
                        @error('user_password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary">{{ isset($vendor) ? 'Update' : 'Create' }} Vendor</button>
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

@php $gmKey = $siteSettings['google_maps_api_key'] ?? ''; @endphp
@if($gmKey)
@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $gmKey }}&libraries=places&callback=initVendorAutocomplete" async defer></script>
<script>
function initVendorAutocomplete() {
    var input = document.getElementById('vendor-address-search');
    if (!input) return;
    var ac = new google.maps.places.Autocomplete(input, { componentRestrictions: { country: 'za' }, fields: ['address_components', 'geometry'] });
    ac.addListener('place_changed', function() {
        var place = ac.getPlace();
        if (!place.geometry) return;
        var c = {};
        place.address_components.forEach(function(comp) {
            comp.types.forEach(function(t) { c[t] = comp.long_name; if (t === 'administrative_area_level_1') c['province_short'] = comp.short_name; });
        });
        document.getElementById('vendor-line1').value = [(c.street_number || ''), (c.route || '')].filter(Boolean).join(' ');
        document.getElementById('vendor-line2').value = c.sublocality || c.sublocality_level_1 || '';
        document.getElementById('vendor-city').value = c.locality || c.sublocality || c.administrative_area_level_2 || '';
        document.getElementById('vendor-province').value = c.administrative_area_level_1 || '';
        document.getElementById('vendor-postal').value = c.postal_code || '';
        document.getElementById('vendor-lat').value = place.geometry.location.lat();
        document.getElementById('vendor-lng').value = place.geometry.location.lng();
        document.getElementById('vendor-gps-display').textContent = 'GPS: ' + place.geometry.location.lat().toFixed(7) + ', ' + place.geometry.location.lng().toFixed(7);
    });
}
</script>
@endpush
@endif
@endsection
