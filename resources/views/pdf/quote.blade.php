<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 3px solid {{ $settings['primary_color'] ?? '#e67e22' }}; padding-bottom: 15px; }
        .company-info { }
        .company-name { font-size: 20px; font-weight: bold; color: {{ $settings['primary_color'] ?? '#e67e22' }}; margin-bottom: 5px; }
        .quote-title { font-size: 24px; font-weight: bold; text-align: right; color: #333; }
        .quote-number { font-size: 14px; color: #666; text-align: right; }
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
        .expiry-box { background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 20px 0; border-radius: 3px; }
        .notes-box { background: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid {{ $settings['primary_color'] ?? '#e67e22' }}; }
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
                <div class="quote-title">QUOTATION</div>
                <div class="quote-number">{{ $quote->quote_number }}</div>
                <div style="color:#666;">{{ $quote->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <div class="section-title">Quote For</div>
                <strong>{{ $quote->customer_name }}</strong><br>
                @if($quote->customer_email)
                {{ $quote->customer_email }}<br>
                @endif
                @if($quote->customer_phone)
                {{ $quote->customer_phone }}
                @endif
            </td>
            <td width="50%">
                <div class="section-title">Quote Details</div>
                <table class="info-grid">
                    <tr>
                        <td class="label">Quote Number:</td>
                        <td>{{ $quote->quote_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td>{{ $quote->created_at->format('d M Y') }}</td>
                    </tr>
                    @if($quote->expiry_date)
                    <tr>
                        <td class="label">Valid Until:</td>
                        <td><strong>{{ $quote->expiry_date->format('d M Y') }}</strong></td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($quote->expiry_date && $quote->expiry_date->isFuture())
    <div class="expiry-box">
        <strong>This quote is valid until {{ $quote->expiry_date->format('d M Y') }}</strong> ({{ $quote->expiry_date->diffForHumans() }})
    </div>
    @endif

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
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->unit }}</td>
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
                <tr><td>Subtotal</td><td class="number">R{{ number_format($quote->subtotal, 2) }}</td></tr>
                @if($quote->delivery_fee > 0)
                <tr><td>Delivery Fee</td><td class="number">R{{ number_format($quote->delivery_fee, 2) }}</td></tr>
                @endif
                <tr><td>VAT (15%)</td><td class="number">R{{ number_format($vat, 2) }}</td></tr>
                <tr class="total-row"><td><strong>Grand Total</strong></td><td class="number"><strong>R{{ number_format($grandTotal, 2) }}</strong></td></tr>
            </table>
        </div>
    </div>

    @if($quote->notes)
    <div class="notes-box" style="clear: both;">
        <strong>Notes:</strong><br>
        {{ $quote->notes }}
    </div>
    @endif

    <div style="margin-top: 30px; clear: both;">
        <div class="section-title">Terms & Conditions</div>
        <ul style="font-size: 10px; color: #666; line-height: 1.6;">
            <li>This quotation is valid for {{ $quote->expiry_date ? $quote->expiry_date->diffInDays($quote->created_at) . ' days' : '30 days' }} from the date of issue.</li>
            <li>Prices are subject to change without notice after the expiry date.</li>
            <li>Delivery fees may vary based on location and order size.</li>
            <li>Payment terms: As per agreement or COD for new customers.</li>
            <li>All prices include VAT at 15%.</li>
        </ul>
    </div>

    <div class="footer">
        <p>{{ $settings['company_name'] ?? 'Concreto' }} | {{ $settings['contact_email'] ?? 'orders@concreto.co.za' }} | {{ $settings['contact_phone'] ?? '' }}</p>
        <p>Thank you for considering our quotation!</p>
    </div>
</body>
</html>
