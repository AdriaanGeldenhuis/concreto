<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .company-name { font-size: 20px; font-weight: bold; color: {{ $settings['primary_color'] ?? '#e67e22' }}; margin-bottom: 5px; }
        .cn-title { font-size: 24px; font-weight: bold; text-align: right; color: #c0392b; }
        .section-title { font-size: 14px; font-weight: bold; color: {{ $settings['primary_color'] ?? '#e67e22' }}; margin: 20px 0 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: center; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #f5f5f5; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #666; border-bottom: 2px solid #ddd; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.items .number { text-align: right; }
        .totals { float: right; width: 250px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 10px; }
        .totals .total-row { font-size: 16px; font-weight: bold; border-top: 2px solid #333; color: #c0392b; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
    <table width="100%" style="margin-bottom: 30px; border-bottom: 3px solid #c0392b; padding-bottom: 15px;">
        <tr>
            <td>
                <div class="company-name">{{ $settings['company_name'] ?? 'Concreto' }}</div>
                <div>{{ $settings['contact_email'] ?? 'orders@concreto.co.za' }}</div>
                <div>{{ $settings['contact_phone'] ?? '' }}</div>
                <div>{{ $settings['contact_address'] ?? '' }}</div>
            </td>
            <td style="text-align: right;">
                <div class="cn-title">CREDIT NOTE</div>
                <div style="font-size: 14px; color: #666;">{{ $creditNote->credit_note_no }}</div>
                <div style="color:#666;">{{ $creditNote->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    @php $customer = $creditNote->customer; $company = $customer?->company; $order = $creditNote->order; @endphp
    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <div class="section-title">Credit To</div>
                @if($company)
                    <strong>{{ $company->display_name }}</strong><br>
                    @if($company->vat_number)VAT: {{ $company->vat_number }}<br>@endif
                    @if($company->full_address){{ $company->full_address }}<br>@endif
                @else
                    <strong>{{ $customer?->user?->name ?? '-' }}</strong><br>
                    {{ $customer?->user?->email ?? '' }}
                @endif
            </td>
            <td width="50%">
                <div class="section-title">Reference</div>
                <strong>Original Invoice:</strong> {{ $creditNote->invoice?->invoice_no ?? '-' }}<br>
                <strong>Order:</strong> {{ $order?->order_number ?? '-' }}<br>
                <strong>Reason:</strong> {{ $creditNote->reason }}
            </td>
        </tr>
    </table>

    @if($order && $order->items->count())
    <div class="section-title">Original Invoice Items</div>
    <table class="items">
        <thead><tr>
            <th>Product</th>
            <th class="number">Qty</th>
            <th class="number">Unit Price</th>
            <th class="number">Total</th>
        </tr></thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="number">{{ $item->qty }}</td>
                <td class="number">R{{ number_format($item->unit_price, 2) }}</td>
                <td class="number">R{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="clearfix">
        <div class="totals">
            <table>
                <tr><td>Credit Amount (excl VAT)</td><td class="number">R{{ number_format($creditNote->amount, 2) }}</td></tr>
                <tr><td>VAT ({{ $settings['vat_rate'] ?? '15' }}%)</td><td class="number">R{{ number_format($creditNote->vat_amount, 2) }}</td></tr>
                <tr class="total-row"><td><strong>Total Credit</strong></td><td class="number"><strong>R{{ number_format($creditNote->total, 2) }}</strong></td></tr>
            </table>
        </div>
    </div>

    @if($creditNote->notes)
    <div style="margin-top: 40px;">
        <div class="section-title">Notes</div>
        <p>{{ $creditNote->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>{{ $settings['company_name'] ?? 'Concreto' }} | {{ $settings['contact_email'] ?? 'orders@concreto.co.za' }} | {{ $settings['contact_phone'] ?? '' }}</p>
        <p>This credit note reduces the amount owed on the referenced invoice.</p>
    </div>
</body>
</html>
