<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto; padding: 0; background: #f5f6fa;">
    <div style="background: linear-gradient(135deg, #2c3e50, #1a252f); color: #fff; padding: 24px; text-align: center;">
        <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 32px; border-left: 1px solid #e1e8ed; border-right: 1px solid #e1e8ed;">
        <h2 style="color: #3498db; margin: 0 0 16px; font-size: 20px;">Your Order is On the Way!</h2>
        <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6;">Your order <strong>{{ $order->order_number }}</strong> is now en route to your delivery address.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 0 0 24px;">
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Order Number</td>
                <td style="padding: 12px 8px; font-weight: 700;">{{ $order->order_number }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #f0f3f5;">
                <td style="padding: 12px 8px; font-weight: 600; color: #7f8c8d;">Driver</td>
                <td style="padding: 12px 8px;">{{ $order->driver?->name ?? 'Assigned' }}</td>
            </tr>
        </table>

        <p style="font-size: 14px; color: #7f8c8d;">You will be notified once the delivery is complete.</p>
    </div>
    <div style="text-align: center; padding: 20px; font-size: 12px; color: #95a5a6;">
        <p style="margin: 0;">Concreto | orders@concreto.co.za</p>
    </div>
</body>
</html>
