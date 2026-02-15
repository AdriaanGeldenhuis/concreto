@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card bg-dark text-light">
                <div class="card-header">
                    <h5 class="mb-0">Email Templates</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Template</th>
                                    <th>Key</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                <tr>
                                    <td><strong>{{ $template['label'] }}</strong></td>
                                    <td><code>{{ $template['key'] }}</code></td>
                                    <td>
                                        @if($template['exists'])
                                            <span class="badge bg-success">Exists</span>
                                        @else
                                            <span class="badge bg-warning">Not Created</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.email-templates.edit', ['template' => $template['key']]) }}" class="btn btn-sm btn-primary">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No email templates configured.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
                    <p class="small text-muted">Common variables available in email templates:</p>
                    <ul class="small">
                        <li><code>{{ '{{ $user->name }}' }}</code> - User's name</li>
                        <li><code>{{ '{{ $order->id }}' }}</code> - Order ID</li>
                        <li><code>{{ '{{ $order->total }}' }}</code> - Order total</li>
                        <li><code>{{ '{{ $quote->id }}' }}</code> - Quote ID</li>
                        <li><code>{{ '{{ $customer->name }}' }}</code> - Customer name</li>
                        <li><code>{{ '{{ config(\'app.name\') }}' }}</code> - Application name</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
