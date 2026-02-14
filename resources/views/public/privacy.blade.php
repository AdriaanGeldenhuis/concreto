@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')

<div class="section section--alt">
    <div class="container" style="max-width: 800px;">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Home</a> / Privacy Policy
            </div>
            <h1>Privacy Policy</h1>
        </div>

        <div class="card">
            <div class="card-body p-3">
                <div class="legal-content">
                    {!! nl2br(e($siteSettings['privacy_content'] ?? 'Privacy policy will be displayed here. The admin can update this content from the settings panel.')) !!}
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-muted text-small">If you have any questions about this privacy policy, please <a href="{{ route('contact') }}">contact us</a>.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .legal-content {
        line-height: 1.8;
        font-size: 0.9375rem;
        color: var(--text);
    }
</style>
@endpush
