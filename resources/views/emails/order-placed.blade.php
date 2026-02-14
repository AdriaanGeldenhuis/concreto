<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #2c3e50; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none;">
        <h2 style="color: #e67e22;">New Order Received</h2>
        <p>A new order has been placed:</p>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><strong>Order:</strong></td>
                <td style="padding: 8px;">{{ $order->order_number }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><strong>Customer:</strong></td>
                <td style="padding: 8px;">{{ $order->customer->user->name }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><strong>Total:</strong></td>
                <td style="padding: 8px; font-weight: bold;">R{{ number_format($order->total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Status:</strong></td>
                <td style="padding: 8px;">{{ $order->status }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
