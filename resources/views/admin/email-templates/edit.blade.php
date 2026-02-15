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
                        @method('PUT')
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
                                <li>{{ '{{ $variable }}' }} - Echo variable</li>
                                <li>{{ '@if($condition) ... @endif' }} - Conditional</li>
                                <li>{{ '@foreach($items as $item) ... @endforeach' }} - Loop</li>
                                <li>{{ '@include(\'partial\')' }} - Include partial</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-light">Common Variables:</h6>
                            <ul class="small font-monospace">
                                <li>{{ '{{ $user->name }}' }} - User's name</li>
                                <li>{{ '{{ $order->id }}' }} - Order ID</li>
                                <li>{{ '{{ $order->total }}' }} - Order total</li>
                                <li>{{ '{{ $quote->id }}' }} - Quote ID</li>
                                <li>{{ '{{ config(\'app.name\') }}' }} - App name</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
