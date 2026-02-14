@extends('layouts.app')
@section('title', 'Contact')
@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 3rem; max-width: 600px;">
    <div class="page-header">
        <h1>Contact Us</h1>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>Get in Touch</h3>
            <p><strong>Email:</strong> {{ $siteSettings['contact_email'] ?? 'orders@concreto.co.za' }}</p>
            <p><strong>Phone:</strong> {{ $siteSettings['contact_phone'] ?? '' }}</p>
            <p><strong>Address:</strong> {{ $siteSettings['contact_address'] ?? '' }}</p>
            @if(!empty($siteSettings['business_hours']))
                <p><strong>Business Hours:</strong> {{ $siteSettings['business_hours'] }}</p>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header">Send us a message</div>
        <div class="card-body">
            <form>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>
@endsection
