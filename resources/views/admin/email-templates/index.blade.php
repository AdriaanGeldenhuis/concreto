@extends('layouts.admin')
@section('title', 'Email Templates')

@section('content')
<div class="page-header">
    <h1>Email Templates</h1>
</div>

<p class="text-muted" style="margin-bottom:1.25rem;">Manage the Blade email templates sent to customers for orders, invoices, and delivery updates.</p>

{{-- Templates Table --}}
<div class="card mb-2">
    <div class="card-header"><span>Templates</span><span class="badge badge-secondary">{{ count($templates) }}</span></div>
    @if(empty($templates))
        <div class="card-body">
            <div class="empty-state">
                <div class="icon">&#9993;</div>
                <h3>No templates configured</h3>
            </div>
        </div>
    @else
        <div class="table-responsive"><table><thead><tr>
            <th>Template</th>
            <th>Key</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
        </tr></thead><tbody>
            @foreach($templates as $template)
            <tr>
                <td class="font-semibold">{{ $template['label'] }}</td>
                <td><code style="font-size:0.8rem;">{{ $template['key'] }}</code></td>
                <td>
                    @if($template['exists'])
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-warning">Not Created</span>
                    @endif
                </td>
                <td class="text-right">
                    <a href="{{ route('admin.email-templates.edit', ['template' => $template['key']]) }}" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody></table></div>
    @endif
</div>

{{-- Template Variables Guide --}}
<div class="card">
    <div class="card-header">Template Variables Guide</div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom:0.75rem;">These variables are available inside email templates depending on the template type:</p>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <h6 class="font-semibold" style="margin-bottom:0.5rem;">Order Templates</h6>
                <ul style="margin:0; padding-left:1.25rem; font-size:0.85rem;">
                    <li><code>@{{ $order->order_number }}</code></li>
                    <li><code>@{{ $order->total }}</code></li>
                    <li><code>@{{ $order->status }}</code></li>
                    <li><code>@{{ $order->delivery_date }}</code></li>
                </ul>
            </div>
            <div>
                <h6 class="font-semibold" style="margin-bottom:0.5rem;">Invoice Templates</h6>
                <ul style="margin:0; padding-left:1.25rem; font-size:0.85rem;">
                    <li><code>@{{ $invoice->invoice_no }}</code></li>
                    <li><code>@{{ $invoice->total_incl }}</code></li>
                    <li><code>@{{ $invoice->vat_amount }}</code></li>
                </ul>
            </div>
            <div>
                <h6 class="font-semibold" style="margin-bottom:0.5rem;">Common Variables</h6>
                <ul style="margin:0; padding-left:1.25rem; font-size:0.85rem;">
                    <li><code>@{{ $user->name }}</code></li>
                    <li><code>@{{ $user->email }}</code></li>
                    <li><code>@{{ config('app.name') }}</code></li>
                    <li><code>@{{ $siteSettings['company_name'] }}</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
