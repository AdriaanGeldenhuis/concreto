<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto; padding: 0; background: #f5f6fa;">
    <div style="background: linear-gradient(135deg, #2c3e50, #1a252f); color: #fff; padding: 24px; text-align: center;">
        <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.02em;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 32px; border-left: 1px solid #e1e8ed; border-right: 1px solid #e1e8ed;">
        <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6;">
            Dear {{ $contactMessage->name }},
        </p>
        <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.6;">
            Thank you for contacting us. Here is our response to your {{ $contactMessage->type === 'quote_request' ? 'quote request' : 'message' }}:
        </p>
        <div style="background: #f8f9fc; border-left: 4px solid #e67e22; padding: 16px 20px; margin: 0 0 24px; border-radius: 0 4px 4px 0;">
            <div style="font-size: 15px; line-height: 1.6; white-space: pre-line;">{{ $replyText }}</div>
        </div>
        <p style="font-size: 14px; color: #7f8c8d; line-height: 1.6;">
            If you have any further questions, feel free to reply to this email or contact us directly.
        </p>
    </div>
    <div style="text-align: center; padding: 20px; font-size: 12px; color: #95a5a6; border-top: 1px solid #e1e8ed;">
        <p style="margin: 0;">Concreto | orders@concreto.co.za</p>
    </div>
</body>
</html>
