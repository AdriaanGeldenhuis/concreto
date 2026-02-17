@extends('layouts.admin')
@section('title', 'Edit Email Template')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Template: {{ $label }}</h1>
            <small class="text-muted">Template key: <code>{{ $key }}</code>. This is a Blade template file that supports HTML and Blade directives.</small>
        </div>
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Back to Templates</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Template Content</h5></div>
                <div class="card-body">
                    <form action="{{ route('admin.email-templates.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="template" value="{{ $key }}">

                        <div class="mb-3">
                            <textarea
                                class="form-control font-monospace"
                                id="content"
                                name="content"
                                rows="28"
                                style="font-size: 0.8125rem;"
                                required>{{ $content }}</textarea>
                            <small class="text-muted mt-1 d-block">
                                A backup of the previous version is saved automatically before each update.
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Template</button>
                            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Blade Syntax</h5></div>
                <div class="card-body" style="font-size: 0.85rem;">
                    <ul class="mb-0">
                        <li class="mb-1"><code>@{{ $variable }}</code> - Echo variable (escaped)</li>
                        <li class="mb-1"><code>{!! $html !!}</code> - Echo raw HTML</li>
                        <li class="mb-1"><code>@@if($condition) ... @@endif</code> - Conditional</li>
                        <li class="mb-1"><code>@@foreach($items as $item) ... @@endforeach</code> - Loop</li>
                        <li class="mb-1"><code>@@include('partial')</code> - Include partial</li>
                        <li class="mb-1"><code>number_format($val, 2)</code> - Format number</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h5 class="mb-0">Available Variables</h5></div>
                <div class="card-body" style="font-size: 0.85rem;">
                    <h6>Customer</h6>
                    <ul>
                        <li><code>$user->name</code></li>
                        <li><code>$user->email</code></li>
                    </ul>
                    <h6>Order</h6>
                    <ul>
                        <li><code>$order->order_number</code></li>
                        <li><code>$order->total</code></li>
                        <li><code>$order->status</code></li>
                    </ul>
                    <h6>Invoice</h6>
                    <ul>
                        <li><code>$invoice->invoice_no</code></li>
                        <li><code>$invoice->total_incl</code></li>
                    </ul>
                    <h6>System</h6>
                    <ul class="mb-0">
                        <li><code>config('app.name')</code></li>
                        <li><code>$siteSettings['company_name']</code></li>
                        <li><code>$siteSettings['contact_phone']</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
