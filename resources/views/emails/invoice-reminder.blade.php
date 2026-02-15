<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Reminder - {{ $invoice->invoice_no }}</title>
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
            border-bottom: 3px solid {{ $urgency === 'high' ? '#dc3545' : ($urgency === 'medium' ? '#ffc107' : '#e67e22') }};
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $siteSettings['primary_color'] ?? '#e67e22' }};
            margin-bottom: 5px;
        }
        .reminder-title {
            font-size: 20px;
            color: #333;
            margin: 20px 0 10px;
        }
        .urgency-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0;
            background: {{ $urgency === 'high' ? '#dc3545' : ($urgency === 'medium' ? '#ffc107' : '#17a2b8') }};
            color: {{ $urgency === 'high' || $urgency === 'medium' ? '#fff' : '#fff' }};
        }
        .invoice-details {
            background: #f9f9f9;
            border-left: 4px solid {{ $siteSettings['primary_color'] ?? '#e67e22' }};
            padding: 20px;
            margin: 20px 0;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            color: #666;
            width: 40%;
        }
        .amount-due {
            font-size: 32px;
            font-weight: bold;
            color: {{ $urgency === 'high' ? '#dc3545' : ($siteSettings['primary_color'] ?? '#e67e22') }};
            text-align: center;
            margin: 30px 0;
        }
        .overdue-alert {
            background: {{ $urgency === 'high' ? '#ffe5e5' : '#fff3cd' }};
            border: 1px solid {{ $urgency === 'high' ? '#dc3545' : '#ffc107' }};
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: {{ $urgency === 'high' ? '#721c24' : '#856404' }};
        }
        .payment-section {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .payment-section h3 {
            margin-top: 0;
            color: {{ $siteSettings['primary_color'] ?? '#e67e22' }};
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: {{ $siteSettings['primary_color'] ?? '#e67e22' }};
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">{{ $siteSettings['company_name'] ?? 'Concreto' }}</div>
            <div class="reminder-title">Payment Reminder</div>
            <div class="urgency-badge">
                @if($urgency === 'high')
                    URGENT
                @elseif($urgency === 'medium')
                    ATTENTION REQUIRED
                @else
                    FRIENDLY REMINDER
                @endif
            </div>
        </div>

        <p>Dear {{ $invoice->order->customer->user->name }},</p>

        @if($daysOverdue > 0)
            <div class="overdue-alert">
                <strong>This invoice is {{ $daysOverdue }} day(s) overdue.</strong>
                @if($urgency === 'high')
                    <br>Immediate payment is required to avoid service interruption.
                @endif
            </div>
            <p>We kindly remind you that payment for the invoice below is now overdue. We would appreciate your prompt attention to this matter.</p>
        @else
            <p>This is a friendly reminder that payment for the following invoice is due soon:</p>
        @endif

        <div class="invoice-details">
            <table>
                <tr>
                    <td>Invoice Number:</td>
                    <td><strong>{{ $invoice->invoice_no }}</strong></td>
                </tr>
                <tr>
                    <td>Invoice Date:</td>
                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'Immediate' }}</td>
                </tr>
                @if($daysOverdue > 0)
                <tr>
                    <td>Days Overdue:</td>
                    <td style="color: #dc3545; font-weight: bold;">{{ $daysOverdue }} days</td>
                </tr>
                @endif
                <tr>
                    <td>Order Number:</td>
                    <td>{{ $invoice->order->order_number }}</td>
                </tr>
            </table>
        </div>

        <div class="amount-due">
            R{{ number_format($amountDue, 2) }}
        </div>
        <p style="text-align: center; color: #666; margin-top: -20px;">Amount Due</p>

        <div class="payment-section">
            <h3>Payment Instructions</h3>
            @if(!empty($siteSettings['bank_name']))
            <p><strong>Bank:</strong> {{ $siteSettings['bank_name'] }}</p>
            @endif
            @if(!empty($siteSettings['account_name']))
            <p><strong>Account Name:</strong> {{ $siteSettings['account_name'] }}</p>
            @endif
            @if(!empty($siteSettings['account_number']))
            <p><strong>Account Number:</strong> {{ $siteSettings['account_number'] }}</p>
            @endif
            @if(!empty($siteSettings['branch_code']))
            <p><strong>Branch Code:</strong> {{ $siteSettings['branch_code'] }}</p>
            @endif
            <p><strong>Reference:</strong> {{ $invoice->invoice_no }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('customer.invoices.show', $invoice->id) }}" class="btn">View Invoice</a>
        </div>

        @if($urgency === 'high')
            <p style="color: #dc3545; font-weight: bold;">
                Please note: If payment is not received within 48 hours, we may need to suspend your account until the outstanding balance is settled.
            </p>
        @endif

        <p>If you have already made this payment, please disregard this reminder. If you have any questions or need to discuss payment arrangements, please contact us immediately.</p>

        <p>Thank you for your prompt attention to this matter.</p>

        <div class="footer">
            <p>{{ $siteSettings['company_name'] ?? 'Concreto' }}</p>
            <p>{{ $siteSettings['contact_email'] ?? '' }} | {{ $siteSettings['contact_phone'] ?? '' }}</p>
            <p>{{ $siteSettings['contact_address'] ?? '' }}</p>
        </div>
    </div>
</body>
</html>
