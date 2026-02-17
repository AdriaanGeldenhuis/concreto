<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote - {{ $quote->quote_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid {{ $settings['primary_color'] ?? '#e67e22' }};
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $settings['primary_color'] ?? '#e67e22' }};
            margin-bottom: 5px;
        }
        .quote-title {
            font-size: 20px;
            color: #333;
            margin: 20px 0 10px;
        }
        .quote-number {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: {{ $settings['primary_color'] ?? '#e67e22' }};
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 250px;
        }
        .totals table td {
            padding: 5px 10px;
        }
        .total-row {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            color: {{ $settings['primary_color'] ?? '#e67e22' }};
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: {{ $settings['primary_color'] ?? '#e67e22' }};
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 12px;
        }
        .clearfix::after {
            content: '';
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $settings['company_name'] ?? 'Concreto' }}</div>
            <div class="quote-title">Quotation</div>
            <div class="quote-number">{{ $quote->quote_number }}</div>
        </div>

        <p>Dear {{ $quote->customer->user->name ?? 'Customer' }},</p>
        <p>Thank you for your interest in our products. Please find your quotation details below:</p>

        <div class="section">
            <div class="section-title">Quote Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->product->unit ?? '-' }}</td>
                        <td class="text-right">{{ $item->qty }}</td>
                        <td class="text-right">R{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">R{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="clearfix">
                <div class="totals">
                    <table>
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-right">R{{ number_format($quote->subtotal, 2) }}</td>
                        </tr>
                        @if($quote->delivery_fee > 0)
                        <tr>
                            <td>Delivery Fee</td>
                            <td class="text-right">R{{ number_format($quote->delivery_fee, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>VAT (15%)</td>
                            <td class="text-right">R{{ number_format($quote->vat, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>Grand Total</strong></td>
                            <td class="text-right"><strong>R{{ number_format($quote->total, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        @if($quote->notes)
        <div class="section">
            <div class="section-title">Notes</div>
            <p>{{ $quote->notes }}</p>
        </div>
        @endif

        @if($quote->expires_at)
        <div class="section">
            <p style="color: #666; font-size: 14px;">
                <strong>Valid until:</strong> {{ $quote->expires_at->format('d M Y') }}
            </p>
        </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ route('customer.quotes.show', $quote->id) }}" class="btn">View Quote in Portal</a>
        </div>

        <p>If you have any questions about this quote, please don't hesitate to contact us.</p>

        <div class="footer">
            <p>{{ $settings['company_name'] ?? 'Concreto' }}</p>
            <p>{{ $settings['contact_email'] ?? '' }} | {{ $settings['contact_phone'] ?? '' }}</p>
            <p>{{ $settings['contact_address'] ?? '' }}</p>
        </div>
    </div>
</body>
</html>
