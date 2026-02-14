@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
    <div class="page-header"><h1>Site Settings</h1></div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <div class="card">
            <div class="card-header">Theme / Colors</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Primary Color</label><input type="color" name="settings[primary_color]" class="form-control" value="{{ $siteSettings['primary_color'] ?? '#e67e22' }}" style="height:45px;"><input type="hidden" name="groups[primary_color]" value="theme"></div>
                    <div class="form-group"><label class="form-label">Secondary Color</label><input type="color" name="settings[secondary_color]" class="form-control" value="{{ $siteSettings['secondary_color'] ?? '#2c3e50' }}" style="height:45px;"><input type="hidden" name="groups[secondary_color]" value="theme"></div>
                    <div class="form-group"><label class="form-label">Background Color</label><input type="color" name="settings[bg_color]" class="form-control" value="{{ $siteSettings['bg_color'] ?? '#f8f9fa' }}" style="height:45px;"><input type="hidden" name="groups[bg_color]" value="theme"></div>
                    <div class="form-group"><label class="form-label">Text Color</label><input type="color" name="settings[text_color]" class="form-control" value="{{ $siteSettings['text_color'] ?? '#2c3e50' }}" style="height:45px;"><input type="hidden" name="groups[text_color]" value="theme"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Company Info</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Company Name</label><input type="text" name="settings[company_name]" class="form-control" value="{{ $siteSettings['company_name'] ?? 'Concreto' }}"><input type="hidden" name="groups[company_name]" value="general"></div>
                <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="settings[contact_email]" class="form-control" value="{{ $siteSettings['contact_email'] ?? 'orders@concreto.co.za' }}"><input type="hidden" name="groups[contact_email]" value="general"></div>
                <div class="form-group"><label class="form-label">Contact Phone</label><input type="text" name="settings[contact_phone]" class="form-control" value="{{ $siteSettings['contact_phone'] ?? '' }}"><input type="hidden" name="groups[contact_phone]" value="general"></div>
                <div class="form-group"><label class="form-label">Contact Address</label><input type="text" name="settings[contact_address]" class="form-control" value="{{ $siteSettings['contact_address'] ?? '' }}"><input type="hidden" name="groups[contact_address]" value="general"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Homepage</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Hero Title</label><input type="text" name="settings[hero_title]" class="form-control" value="{{ $siteSettings['hero_title'] ?? 'Quality Building Materials, Delivered' }}"><input type="hidden" name="groups[hero_title]" value="homepage"></div>
                <div class="form-group"><label class="form-label">Hero Subtitle</label><textarea name="settings[hero_subtitle]" class="form-control">{{ $siteSettings['hero_subtitle'] ?? '' }}</textarea><input type="hidden" name="groups[hero_subtitle]" value="homepage"></div>
                <div class="form-group"><label class="form-label">Delivery Info Banner</label><input type="text" name="settings[delivery_info]" class="form-control" value="{{ $siteSettings['delivery_info'] ?? '' }}"><input type="hidden" name="groups[delivery_info]" value="homepage"></div>
                <div class="form-group"><label class="form-label">Promo Banner</label><input type="text" name="settings[homepage_promo]" class="form-control" value="{{ $siteSettings['homepage_promo'] ?? '' }}"><input type="hidden" name="groups[homepage_promo]" value="homepage"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Footer & Social</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Footer About Text</label><textarea name="settings[footer_about]" class="form-control">{{ $siteSettings['footer_about'] ?? '' }}</textarea><input type="hidden" name="groups[footer_about]" value="footer"></div>
                <div class="form-group"><label class="form-label">Footer Text</label><input type="text" name="settings[footer_text]" class="form-control" value="{{ $siteSettings['footer_text'] ?? '' }}"><input type="hidden" name="groups[footer_text]" value="footer"></div>
                <div class="form-group"><label class="form-label">Facebook URL</label><input type="url" name="settings[social_facebook]" class="form-control" value="{{ $siteSettings['social_facebook'] ?? '' }}"><input type="hidden" name="groups[social_facebook]" value="social"></div>
                <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="settings[social_instagram]" class="form-control" value="{{ $siteSettings['social_instagram'] ?? '' }}"><input type="hidden" name="groups[social_instagram]" value="social"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Legal Pages</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Terms & Conditions</label><textarea name="settings[terms_content]" class="form-control" rows="6">{{ $siteSettings['terms_content'] ?? '' }}</textarea><input type="hidden" name="groups[terms_content]" value="legal"></div>
                <div class="form-group"><label class="form-label">Privacy Policy</label><textarea name="settings[privacy_content]" class="form-control" rows="6">{{ $siteSettings['privacy_content'] ?? '' }}</textarea><input type="hidden" name="groups[privacy_content]" value="legal"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Business Rules</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Minimum Order Value (R)</label><input type="number" name="settings[min_order_value]" class="form-control" step="0.01" value="{{ $siteSettings['min_order_value'] ?? '0' }}"><input type="hidden" name="groups[min_order_value]" value="business"></div>
                <div class="form-group"><label class="form-label">Business Hours</label><input type="text" name="settings[business_hours]" class="form-control" value="{{ $siteSettings['business_hours'] ?? 'Mon-Fri 07:00-17:00, Sat 07:00-12:00' }}"><input type="hidden" name="groups[business_hours]" value="business"></div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
    </form>
@endsection
