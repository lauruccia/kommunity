<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('application.mail_rejected_title') }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a1f2e; padding: 32px 40px; text-align: center; }
        .header .brand { color: #9ad84a; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; }
        .header h1 { color: #ffffff; font-size: 22px; margin: 12px 0 0; font-weight: 600; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .reason-box { background: #f8fafc; border-left: 3px solid #cbd5e1; border-radius: 6px; padding: 14px 20px; margin: 20px 0; }
        .reason-box p { color: #4b5563; font-size: 14px; margin: 0; font-style: italic; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0 0 8px; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="brand">Kommunity</div>
        <h1>{{ __('application.mail_rejected_title') }}</h1>
    </div>
    <div class="body">
        <p>{{ __('application.mail_rejected_greeting', ['name' => explode(' ', trim($application->name))[0]]) }}</p>
        <p>{{ __('application.mail_rejected_line1') }}</p>

        @if($reason)
            <div class="reason-box">
                <p>{{ __('application.mail_rejected_reason', ['reason' => $reason]) }}</p>
            </div>
        @endif

        <p>{{ __('application.mail_rejected_line2') }}</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Kommunity</p>
    </div>
</div>
</body>
</html>
