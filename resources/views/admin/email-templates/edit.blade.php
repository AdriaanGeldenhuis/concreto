@extends('layouts.admin')
@section('title', 'Edit Email Template')

@section('content')
<div class="page-header">
    <div class="breadcrumb"><a href="{{ route('admin.email-templates.index') }}">Email Templates</a> / {{ $label }}</div>
    <h1>Edit: {{ $label }}</h1>
    <small class="text-muted">Template key: <code>{{ $key }}</code></small>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
    {{-- Editor --}}
    <div class="card">
        <div class="card-header">Template Content</div>
        <div class="card-body">
            <form action="{{ route('admin.email-templates.update') }}" method="POST">
                @csrf
                <input type="hidden" name="template" value="{{ $key }}">

                <div class="form-group">
                    <textarea
                        class="form-control"
                        id="content"
                        name="content"
                        rows="28"
                        style="font-family:monospace; font-size:0.8125rem;"
                        required>{{ $content }}</textarea>
                    <small class="text-muted">A backup of the previous version is saved automatically before each update.</small>
                </div>

                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Reference Sidebar --}}
    <div>
        <div class="card mb-2">
            <div class="card-header">Blade Syntax</div>
            <div class="card-body" style="font-size:0.85rem;">
                <ul style="margin:0; padding-left:1.25rem;">
                    <li style="margin-bottom:0.35rem;"><code>@{{ $variable }}</code> — Echo (escaped)</li>
                    <li style="margin-bottom:0.35rem;"><code>{!! $html !!}</code> — Echo raw HTML</li>
                    <li style="margin-bottom:0.35rem;"><code>@@if(...) ... @@endif</code> — Conditional</li>
                    <li style="margin-bottom:0.35rem;"><code>@@foreach(...) ... @@endforeach</code> — Loop</li>
                    <li style="margin-bottom:0.35rem;"><code>@@include('partial')</code> — Include</li>
                    <li><code>number_format($val, 2)</code> — Format number</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Available Variables</div>
            <div class="card-body" style="font-size:0.85rem;">
                <h6 class="font-semibold" style="margin-bottom:0.35rem;">Customer</h6>
                <ul style="padding-left:1.25rem; margin-bottom:0.75rem;">
                    <li><code>$user->name</code></li>
                    <li><code>$user->email</code></li>
                </ul>
                <h6 class="font-semibold" style="margin-bottom:0.35rem;">Order</h6>
                <ul style="padding-left:1.25rem; margin-bottom:0.75rem;">
                    <li><code>$order->order_number</code></li>
                    <li><code>$order->total</code></li>
                    <li><code>$order->status</code></li>
                </ul>
                <h6 class="font-semibold" style="margin-bottom:0.35rem;">Invoice</h6>
                <ul style="padding-left:1.25rem; margin-bottom:0.75rem;">
                    <li><code>$invoice->invoice_no</code></li>
                    <li><code>$invoice->total_incl</code></li>
                </ul>
                <h6 class="font-semibold" style="margin-bottom:0.35rem;">System</h6>
                <ul style="padding-left:1.25rem; margin:0;">
                    <li><code>config('app.name')</code></li>
                    <li><code>$siteSettings['company_name']</code></li>
                    <li><code>$siteSettings['contact_phone']</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
