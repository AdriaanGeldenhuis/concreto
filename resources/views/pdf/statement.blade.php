<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #666; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        table.orders { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.orders th, table.orders td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.orders th { background-color: #f5f5f5; font-weight: bold; }
        table.orders td.amount { text-align: right; }
        .total-row { font-weight: bold; background-color: #f0f0f0; }
        .footer { margin-top: 30px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings['company_name'] ?? 'Concreto Aggregates' }}</h1>
        <h2>Account Statement</h2>
    </div>

    @php $company = $customer->company; @endphp
    <table class="info-table">
        <tr>
            <td style="width:50%">
                @if($company)
                    <strong>{{ $company->display_name }}</strong><br>
                    @if($company->vat_number)<strong>VAT:</strong> {{ $company->vat_number }}<br>@endif
                    @if($company->registration_number)<strong>Reg:</strong> {{ $company->registration_number }}<br>@endif
                    @if($company->full_address){{ $company->full_address }}<br>@endif
                @else
                    <strong>Customer:</strong> {{ $customer->user->name }}<br>
                    <strong>Email:</strong> {{ $customer->user->email }}<br>
                @endif
                <strong>Account Type:</strong> {{ $customer->type }}
            </td>
            <td style="width:50%; text-align:right">
                <strong>Period:</strong> {{ \Carbon\Carbon::parse($from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}<br>
                <strong>Generated:</strong> {{ now()->format('d M Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="orders">
        <thead>
            <tr>
                <th>Date</th>
                <th>Order #</th>
                <th>Invoice #</th>
                <th>Items</th>
                <th style="text-align:right">Subtotal</th>
                <th style="text-align:right">VAT</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->updated_at->format('d/m/Y') }}</td>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->invoice?->invoice_no ?? '-' }}</td>
                <td>
                    @foreach($order->items as $item)
                        {{ $item->product->name }} x {{ $item->qty }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </td>
                <td class="amount">R {{ number_format($order->subtotal, 2) }}</td>
                <td class="amount">R {{ number_format($order->vat, 2) }}</td>
                <td class="amount">R {{ number_format($order->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center">No delivered orders in this period.</td>
            </tr>
            @endforelse
            @if($orders->count())
            <tr class="total-row">
                <td colspan="6" style="text-align:right">Total:</td>
                <td class="amount">R {{ number_format($totalAmount, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>This statement was generated automatically. For queries, contact {{ $settings['contact_email'] ?? 'accounts@concreto.co.za' }}.</p>
    </div>
</body>
</html>
