<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto; padding: 0; background: #f5f6fa;">
    <div style="background: linear-gradient(135deg, #2c3e50, #1a252f); color: #fff; padding: 24px; text-align: center;">
        <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.02em;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 32px; border-left: 1px solid #e1e8ed; border-right: 1px solid #e1e8ed;">
        <h2 style="color: #e67e22; margin: 0 0 16px; font-size: 20px;">New Order Received</h2>
        <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6;">A new order has been placed and is ready for processing.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 24px;">
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d; width: 140px;">Order Number</td>
                <td style="padding: 12px 8px; font-weight: 700;">{{ $order->order_number }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Customer</td>
                <td style="padding: 12px 8px;">{{ $order->customer->user->name }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Total</td>
                <td style="padding: 12px 8px; font-weight: 800; color: #e67e22; font-size: 18px;">R{{ number_format($order->total, 2) }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Status</td>
                <td style="padding: 12px 8px;">
                    <span style="background: rgba(52,152,219,0.12); color: #3498db; padding: 4px 12px; border-radius: 50px; font-size: 12px; font-weight: 600;">{{ str_replace('_', ' ', $order->status) }}</span>
                </td>
            </tr>
            @if($order->scheduled_date)
            <tr>
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Delivery Date</td>
                <td style="padding: 12px 8px;">{{ $order->scheduled_date->format('d M Y') }} {{ $order->scheduled_time_window ?? '' }}</td>
            </tr>
            @endif
        </table>

        @if($order->items->count())
        <h3 style="font-size: 14px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 12px;">Order Items</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 0 0 24px;">
            @foreach($order->items as $item)
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 8px;">{{ $item->product->name }}</td>
                <td style="padding: 8px; text-align: right; color: #7f8c8d;">x{{ $item->qty }} {{ $item->product->unit }}</td>
                <td style="padding: 8px; text-align: right; font-weight: 600;">R{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </div>
    <div style="text-align: center; padding: 20px; font-size: 12px; color: #95a5a6; border-top: 1px solid #e1e8ed;">
        <p style="margin: 0;">Concreto | orders@concreto.co.za</p>
    </div>
</body>
</html>
