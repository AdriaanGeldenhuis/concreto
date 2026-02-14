<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #2c3e50; color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none;">
        <h2 style="color: #e67e22;">Invoice {{ $invoice->invoice_no }}</h2>
        <p>Dear {{ $order->customer->user->name }},</p>
        <p>Please find attached your invoice for order <strong>{{ $order->order_number }}</strong>.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><strong>Order Number:</strong></td>
                <td style="padding: 8px;">{{ $order->order_number }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><strong>Invoice Number:</strong></td>
                <td style="padding: 8px;">{{ $invoice->invoice_no }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 8px;"><strong>Total:</strong></td>
                <td style="padding: 8px; font-weight: bold; color: #e67e22;">R{{ number_format($order->total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px;"><strong>Date:</strong></td>
                <td style="padding: 8px;">{{ $invoice->created_at->format('d M Y') }}</td>
            </tr>
        </table>

        <p>The PDF invoice is attached to this email.</p>
        <p>Thank you for choosing Concreto!</p>
    </div>
    <div style="text-align: center; padding: 15px; font-size: 12px; color: #999;">
        <p>Concreto | orders@concreto.co.za</p>
    </div>
</body>
</html>
