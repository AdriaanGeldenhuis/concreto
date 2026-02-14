@extends('layouts.app')
@section('title', 'Terms & Conditions')
@section('content')

<div class="section section--alt">
    <div class="container" style="max-width: 800px;">
        <div class="page-header">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Home</a> / Terms & Conditions
            </div>
            <h1>Terms & Conditions</h1>
        </div>

        <div class="card">
            <div class="card-body p-3">
                <div class="legal-content">
                    {!! nl2br(e($siteSettings['terms_content'] ?? 'Terms and conditions will be displayed here. The admin can update this content from the settings panel.')) !!}
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-muted text-small">If you have any questions about these terms, please <a href="{{ route('contact') }}">contact us</a>.</p>
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
