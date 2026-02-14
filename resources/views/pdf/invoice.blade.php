<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid {{ $settings['primary_color'] ?? '#e67e22' }}; padding-bottom: 15px; }
        .company-info { }
        .company-name { font-size: 20px; font-weight: bold; color: {{ $settings['primary_color'] ?? '#e67e22' }}; margin-bottom: 5px; }
        .invoice-title { font-size: 24px; font-weight: bold; text-align: right; color: #333; }
        .invoice-number { font-size: 14px; color: #666; text-align: right; }
        .info-grid { width: 100%; margin-bottom: 20px; }
        .info-grid td { vertical-align: top; padding: 3px 0; }
        .info-grid .label { font-weight: bold; color: #666; width: 120px; }
        .section-title { font-size: 14px; font-weight: bold; color: {{ $settings['primary_color'] ?? '#e67e22' }}; margin: 20px 0 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-align: center; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #f5f5f5; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #666; border-bottom: 2px solid #ddd; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.items .number { text-align: right; }
        .totals { float: right; width: 250px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 10px; }
        .totals .total-row { font-size: 16px; font-weight: bold; border-top: 2px solid #333; color: {{ $settings['primary_color'] ?? '#e67e22' }}; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>
    <table width="100%" style="margin-bottom: 30px; border-bottom: 3px solid {{ $settings['primary_color'] ?? '#e67e22' }}; padding-bottom: 15px;">
        <tr>
            <td>
                <div class="company-name">{{ $settings['company_name'] ?? 'Concreto' }}</div>
                <div>{{ $settings['contact_email'] ?? 'orders@concreto.co.za' }}</div>
                <div>{{ $settings['contact_phone'] ?? '' }}</div>
                <div>{{ $settings['contact_address'] ?? '' }}</div>
            </td>
            <td style="text-align: right;">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->invoice_no }}</div>
                <div style="color:#666;">{{ $invoice->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <div class="section-title">Bill To</div>
                <strong>{{ $order->customer->user->name }}</strong><br>
                {{ $order->customer->user->email }}<br>
                {{ $order->customer->user->phone ?? '' }}
            </td>
            <td width="50%">
                <div class="section-title">Deliver To</div>
                @if($order->deliveryAddress)
                    {{ $order->deliveryAddress->full_address }}
                @endif
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 10px;">
        <tr>
            <td><strong>Order Number:</strong> {{ $order->order_number }}</td>
            <td><strong>Order Date:</strong> {{ $order->created_at->format('d M Y') }}</td>
            @if($order->scheduled_date)
            <td><strong>Delivery Date:</strong> {{ $order->scheduled_date->format('d M Y') }}</td>
            @endif
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>Unit</th>
                <th class="number">Qty</th>
                <th class="number">Unit Price</th>
                <th class="number">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->product->unit }}</td>
                <td class="number">{{ $item->qty }}</td>
                <td class="number">R{{ number_format($item->unit_price, 2) }}</td>
                <td class="number">R{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals">
            <table>
                <tr><td>Subtotal</td><td class="number">R{{ number_format($order->subtotal, 2) }}</td></tr>
                <tr><td>Delivery Fee</td><td class="number">R{{ number_format($order->delivery_fee, 2) }}</td></tr>
                <tr><td>VAT (15%)</td><td class="number">R{{ number_format($order->vat, 2) }}</td></tr>
                <tr class="total-row"><td><strong>Total</strong></td><td class="number"><strong>R{{ number_format($order->total, 2) }}</strong></td></tr>
            </table>
        </div>
    </div>

    @if($order->proofOfDelivery)
    <div style="margin-top: 40px;">
        <div class="section-title">Proof of Delivery</div>
        <p><strong>Received by:</strong> {{ $order->proofOfDelivery->signer_name }}</p>
        <p><strong>Date & Time:</strong> {{ $order->proofOfDelivery->signed_at->format('d M Y H:i') }}</p>
    </div>
    @endif

    <div class="footer">
        <p>{{ $settings['company_name'] ?? 'Concreto' }} | {{ $settings['contact_email'] ?? 'orders@concreto.co.za' }} | {{ $settings['contact_phone'] ?? '' }}</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
