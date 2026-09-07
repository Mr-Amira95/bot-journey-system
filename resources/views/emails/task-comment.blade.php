<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
    .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
    .header { background: #0f1b3d; padding: 28px 36px; }
    .header-title { color: #E26B3D; font-size: 13px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 8px; }
    .content { padding: 36px; color: #1a1a2e; font-size: 15px; line-height: 1.7; }
    .task-card { background: #f8f8f8; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
    .task-card .task-title { font-size: 16px; font-weight: 600; color: #1a1a2e; }
    .comment-box { border-left: 3px solid #E26B3D; background: #fbfbfb; padding: 12px 16px; margin: 16px 0; color: #333; font-size: 14px; }
    .cta { display: inline-block; margin-top: 24px; padding: 12px 28px; background: #E26B3D; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; }
    .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
    .footer { background: #f9f9f9; padding: 20px 36px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div style="color:#fff; font-size:18px; font-weight:700; letter-spacing:1px;">{{ config('app.name', 'BotJourney') }}</div>
        <div class="header-title">New Comment</div>
    </div>
    <div class="content">
        <p>Hi {{ $notifiable->name }},</p>
        <p><strong>{{ $commentedBy->name }}</strong> commented on a task assigned to you.</p>

        <div class="task-card">
            <div class="task-title">{{ $task->title }}</div>
        </div>

        <div class="comment-box">
            {{ \Illuminate\Support\Str::limit($comment->comment, 500) }}
        </div>

        <a href="{{ $taskUrl }}" class="cta">View Task &rarr;</a>

        <hr class="divider">
        <p style="font-size:13px; color:#666;">
            You can reply from the Tasks section.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name', 'BotJourney') }} &mdash; This is an automated notification.
    </div>
</div>
</body>
</html>
