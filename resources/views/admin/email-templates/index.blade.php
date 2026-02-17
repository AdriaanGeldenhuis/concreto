@extends('layouts.admin')
@section('title', 'Email Templates')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Email Templates</h1>
            <small class="text-muted">Manage the Blade email templates sent to customers for orders, invoices, and delivery updates.</small>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Key</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr>
                            <td><strong>{{ $template['label'] }}</strong></td>
                            <td><code>{{ $template['key'] }}</code></td>
                            <td>
                                @if($template['exists'])
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning">Not Created</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.email-templates.edit', ['template' => $template['key']]) }}" class="btn btn-sm btn-primary">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No email templates configured.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header"><h5 class="mb-0">Template Variables Guide</h5></div>
        <div class="card-body" style="font-size: 0.9rem;">
            <p class="text-muted mb-3">These variables are available inside email templates depending on the template type:</p>
            <div class="row">
                <div class="col-md-4">
                    <h6>Order Templates</h6>
                    <ul class="mb-3">
                        <li><code>@{{ $order->order_number }}</code> - Order number</li>
                        <li><code>@{{ $order->total }}</code> - Order total</li>
                        <li><code>@{{ $order->status }}</code> - Order status</li>
                        <li><code>@{{ $order->delivery_date }}</code> - Delivery date</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Invoice Templates</h6>
                    <ul class="mb-3">
                        <li><code>@{{ $invoice->invoice_no }}</code> - Invoice number</li>
                        <li><code>@{{ $invoice->total_incl }}</code> - Total incl. VAT</li>
                        <li><code>@{{ $invoice->vat_amount }}</code> - VAT amount</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Common Variables</h6>
                    <ul class="mb-3">
                        <li><code>@{{ $user->name }}</code> - Customer name</li>
                        <li><code>@{{ $user->email }}</code> - Customer email</li>
                        <li><code>@{{ config('app.name') }}</code> - App name</li>
                        <li><code>@{{ $siteSettings['company_name'] }}</code> - Company</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
