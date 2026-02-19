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
@endsection
