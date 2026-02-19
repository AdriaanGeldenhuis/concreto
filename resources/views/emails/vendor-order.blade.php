<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto; padding: 0; background: #f5f6fa;">
    <div style="background: linear-gradient(135deg, #2c3e50, #1a252f); color: #fff; padding: 24px; text-align: center;">
        <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.02em;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 32px; border-left: 1px solid #e1e8ed; border-right: 1px solid #e1e8ed;">
        <div style="background: #fef3e2; border-left: 4px solid #e67e22; padding: 16px; margin: 0 0 24px; border-radius: 0 4px 4px 0;">
            <h2 style="color: #e67e22; margin: 0; font-size: 18px; font-weight: 700;">ACTION REQUIRED: New Order to Process</h2>
        </div>

        <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6;">
            Hi {{ $vendor->contact_person ?? $vendor->name }},<br>
            A new order has been placed and requires processing.
        </p>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 24px;">
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d; width: 140px;">Order Number</td>
                <td style="padding: 12px 8px; font-weight: 700;">{{ $order->order_number }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Customer</td>
                <td style="padding: 12px 8px;">{{ $order->customer->user->name }}</td>
            </tr>
            @if($order->customer->company)
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Company</td>
                <td style="padding: 12px 8px;">{{ $order->customer->company->name }}</td>
            </tr>
            @endif
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Total</td>
                <td style="padding: 12px 8px; font-weight: 800; color: #e67e22; font-size: 18px;">R{{ number_format($order->total, 2) }}</td>
            </tr>
            @if($order->scheduled_date)
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Delivery Date</td>
                <td style="padding: 12px 8px; font-weight: 700; color: #e74c3c;">{{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window ?? '' }}</td>
            </tr>
            @endif
            @if($order->deliveryAddress)
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Delivery Address</td>
                <td style="padding: 12px 8px;">
                    {{ $order->deliveryAddress->line1 }}{{ $order->deliveryAddress->line2 ? ', ' . $order->deliveryAddress->line2 : '' }}<br>
                    {{ $order->deliveryAddress->city }}, {{ $order->deliveryAddress->postal_code }}
                </td>
            </tr>
            @endif
        </table>

        @if($order->items->count())
        <h3 style="font-size: 14px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Order Items</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 0 0 24px;">
            <tr style="background: #f8f9fa; border-bottom: 2px solid #e1e8ed;">
                <th style="padding: 10px 8px; text-align: left; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Product</th>
                <th style="padding: 10px 8px; text-align: right; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Qty</th>
                <th style="padding: 10px 8px; text-align: right; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Unit Price</th>
                <th style="padding: 10px 8px; text-align: right; font-size: 12px; color: #7f8c8d; text-transform: uppercase;">Total</th>
            </tr>
            @foreach($order->items as $item)
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 10px 8px; font-weight: 600;">{{ $item->product->name }}</td>
                <td style="padding: 10px 8px; text-align: right;">{{ $item->qty }} {{ $item->product->unit }}</td>
                <td style="padding: 10px 8px; text-align: right;">R{{ number_format($item->unit_price, 2) }}</td>
                <td style="padding: 10px 8px; text-align: right; font-weight: 600;">R{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </table>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 24px;">
            <tr>
                <td style="padding: 6px 8px; text-align: right; color: #7f8c8d;">Subtotal</td>
                <td style="padding: 6px 8px; text-align: right; width: 120px;">R{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td style="padding: 6px 8px; text-align: right; color: #27ae60;">Discount</td>
                <td style="padding: 6px 8px; text-align: right; color: #27ae60;">-R{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 6px 8px; text-align: right; color: #7f8c8d;">VAT (15%)</td>
                <td style="padding: 6px 8px; text-align: right;">R{{ number_format($order->vat, 2) }}</td>
            </tr>
            @if($order->delivery_fee > 0)
            <tr>
                <td style="padding: 6px 8px; text-align: right; color: #7f8c8d;">Delivery Fee</td>
                <td style="padding: 6px 8px; text-align: right;">R{{ number_format($order->delivery_fee, 2) }}</td>
            </tr>
            @endif
            <tr style="border-top: 2px solid #2c3e50;">
                <td style="padding: 12px 8px; text-align: right; font-weight: 800; font-size: 16px;">Total</td>
                <td style="padding: 12px 8px; text-align: right; font-weight: 800; font-size: 16px; color: #e67e22;">R{{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
        @endif

        @if($order->notes)
        <div style="background: #f8f9fa; padding: 16px; border-radius: 4px; margin: 0 0 24px;">
            <h4 style="margin: 0 0 8px; font-size: 13px; color: #7f8c8d; text-transform: uppercase;">Order Notes</h4>
            <p style="margin: 0; font-size: 14px; line-height: 1.5;">{{ $order->notes }}</p>
        </div>
        @endif
    </div>
    <div style="text-align: center; padding: 20px; font-size: 12px; color: #95a5a6; border-top: 1px solid #e1e8ed;">
        <p style="margin: 0;">Concreto | orders@concreto.co.za</p>
    </div>
</body>
</html>
