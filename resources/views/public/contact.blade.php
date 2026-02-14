@extends('layouts.app')
@section('title', 'Contact')
@section('content')

<div class="hero">
    <div class="container">
        <h1>Get in Touch</h1>
        <p>We are here to help with your building material needs. Reach out to us through any of the channels below.</p>
    </div>
</div>

<div class="section">
    <div class="container">
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">&#9993;</div>
                <h3>Email Us</h3>
                <p>{{ $siteSettings['contact_email'] ?? 'orders@concreto.co.za' }}</p>
            </div>
            <div class="feature-card">
                <div class="icon">&#9742;</div>
                <h3>Call Us</h3>
                <p>{{ $siteSettings['contact_phone'] ?? 'Contact number coming soon' }}</p>
            </div>
            <div class="feature-card">
                <div class="icon">&#9962;</div>
                <h3>Visit Us</h3>
                <p>{{ $siteSettings['contact_address'] ?? 'Address coming soon' }}</p>
            </div>
        </div>

        @if(!empty($siteSettings['business_hours']))
            <div class="text-center mb-4">
                <span class="badge badge-primary">&#9200; Business Hours: {{ $siteSettings['business_hours'] }}</span>
            </div>
        @endif
    </div>
</div>

<div class="section section--alt">
    <div class="container" style="max-width: 600px;">
        <h2 class="section-title">Send Us a Message</h2>
        <p class="section-subtitle">Fill out the form below and we will get back to you as soon as possible.</p>

        <div class="auth-card" style="max-width: 100%;">
            <form method="POST" action="{{ route('contact.submit') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Your full name" value="{{ old('name') }}" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" required>
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Your phone number" value="{{ old('phone') }}">
                        @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <select name="subject" class="form-control">
                            <option value="general" {{ old('subject') == 'general' ? 'selected' : '' }}>General Enquiry</option>
                            <option value="products" {{ old('subject') == 'products' ? 'selected' : '' }}>Product Information</option>
                            <option value="orders" {{ old('subject') == 'orders' ? 'selected' : '' }}>Order Enquiry</option>
                            <option value="delivery" {{ old('subject') == 'delivery' ? 'selected' : '' }}>Delivery Enquiry</option>
                            <option value="account" {{ old('subject') == 'account' ? 'selected' : '' }}>Account Support</option>
                            <option value="other" {{ old('subject') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('subject')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required>{{ old('message') }}</textarea>
                    @error('message')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Send Message</button>
            </form>
        </div>
    </div>
</div>
@endsection
