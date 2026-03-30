<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 40px 16px; color: #111827; }
        .card { background: #fff; border-radius: 12px; max-width: 520px; margin: 0 auto; padding: 40px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { font-size: 22px; font-weight: 700; margin: 0 0 8px; }
        p  { font-size: 15px; line-height: 1.6; color: #374151; margin: 0 0 20px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; }
        .footer { margin-top: 32px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome to {{ config('app.name') }}!</h1>
        <p>Hi {{ $user->name }},</p>
        <p>Thanks for signing up. Your account is ready and your workspace has been created. Head to your dashboard to get started.</p>
        <a href="{{ route('dashboard') }}" class="btn">Go to Dashboard</a>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
