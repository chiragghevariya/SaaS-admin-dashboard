<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deactivated</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 40px 16px; color: #111827; }
        .card { background: #fff; border-radius: 12px; max-width: 520px; margin: 0 auto; padding: 40px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { font-size: 22px; font-weight: 700; margin: 0 0 8px; }
        p  { font-size: 15px; line-height: 1.6; color: #374151; margin: 0 0 16px; }
        .footer { margin-top: 32px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Your account has been deactivated</h1>
        <p>Hi {{ $user->name }},</p>
        <p>Your account on <strong>{{ config('app.name') }}</strong> has been deactivated by an administrator. You will no longer be able to log in.</p>
        <p>If you believe this was a mistake, please contact your team administrator.</p>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
