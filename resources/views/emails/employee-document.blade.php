<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .header { background: #0f1b3d; padding: 28px 36px; }
    .header img { height: 36px; }
    .header-title { color: #E26B3D; font-size: 13px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 8px; }
    .content { padding: 36px; color: #1a1a2e; font-size: 15px; line-height: 1.7; }
    .body-text { white-space: pre-line; }
    .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
    .footer { background: #f9f9f9; padding: 20px 36px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div style="color:#fff; font-size:18px; font-weight:700; letter-spacing:1px;">{{ config('app.name', 'BotJourney') }}</div>
        <div class="header-title">HR Communication</div>
    </div>
    <div class="content">
        <p class="body-text">{{ $body }}</p>
        <hr class="divider">
        <p style="font-size:13px; color:#666;">A document has been attached to this email. Please open it, review the contents, and follow the instructions provided.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name', 'BotJourney') }} &mdash; This email and its attachments are confidential.
    </div>
</div>
</body>
</html>
