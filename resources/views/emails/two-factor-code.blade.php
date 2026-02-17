<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto; padding: 0; background: #f5f6fa;">
    <div style="background: linear-gradient(135deg, #2c3e50, #1a252f); color: #fff; padding: 24px; text-align: center;">
        <h1 style="margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -0.02em;">Concreto</h1>
    </div>
    <div style="background: #fff; padding: 32px; border-left: 1px solid #e1e8ed; border-right: 1px solid #e1e8ed; text-align: center;">
        <h2 style="color: #e67e22; margin: 0 0 8px; font-size: 20px;">Login Verification Code</h2>
        <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6;">
            Hi {{ $user->name }}, use this code to complete your login:
        </p>
        <div style="background: #f8f9fc; border: 2px solid #e1e8ed; border-radius: 8px; padding: 20px; margin: 0 auto 24px; max-width: 200px;">
            <div style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #2c3e50;">{{ $code }}</div>
        </div>
        <p style="font-size: 14px; color: #7f8c8d; line-height: 1.6;">
            This code expires in 10 minutes. If you didn't try to log in, please ignore this email and consider changing your password.
        </p>
    </div>
    <div style="text-align: center; padding: 20px; font-size: 12px; color: #95a5a6; border-top: 1px solid #e1e8ed;">
        <p style="margin: 0;">Concreto | Do not share this code with anyone</p>
    </div>
</body>
</html>
