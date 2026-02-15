@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-light">Edit Email Template: {{ $label }}</h3>
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">Back to Templates</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Template: <code>{{ $key }}</code></h5>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-templates.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="template" value="{{ $key }}">

                        <div class="mb-3">
                            <label for="content" class="form-label">Template Content</label>
                            <textarea
                                class="form-control bg-dark text-light border-secondary font-monospace"
                                id="content"
                                name="content"
                                rows="25"
                                style="font-size: 0.875rem;"
                                required>{{ $content }}</textarea>
                            <small class="form-text text-muted">
                                This is a Blade template file. You can use HTML, Blade directives, and template variables.
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
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h6 class="mb-0">Template Variables Guide</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Common Blade syntax and variables:</p>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-light">Blade Directives:</h6>
                            <ul class="small font-monospace">
                                <li><code>@{{ $variable }}</code> - Echo variable</li>
                                <li><code>@@if($condition) ... @@endif</code> - Conditional</li>
                                <li><code>@@foreach($items as $item) ... @@endforeach</code> - Loop</li>
                                <li><code>@@include('partial')</code> - Include partial</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-light">Common Variables:</h6>
                            <ul class="small font-monospace">
                                <li><code>@{{ $user->name }}</code> - User's name</li>
                                <li><code>@{{ $order->id }}</code> - Order ID</li>
                                <li><code>@{{ $order->total }}</code> - Order total</li>
                                <li><code>@{{ $quote->id }}</code> - Quote ID</li>
                                <li><code>@{{ config('app.name') }}</code> - App name</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
