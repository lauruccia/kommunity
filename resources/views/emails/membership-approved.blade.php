<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('application.mail_approved_title') }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a1f2e; padding: 32px 40px; text-align: center; }
        .header .brand { color: #9ad84a; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; }
        .header h1 { color: #ffffff; font-size: 24px; margin: 12px 0 0; font-weight: 700; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .planet-box { background: #f0f7ec; border: 1px solid #cde3bd; border-radius: 8px; padding: 14px 20px; margin: 20px 0; text-align: center; }
        .planet-box p { color: #3e6039; font-size: 15px; margin: 0; font-weight: 700; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #537d4d; color: #ffffff !important; text-decoration: none; padding: 14px 36px; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: 0.01em; }
        .btn:hover { background: #3e6039; }
        .expiry { background: #fefce8; border: 1px solid #fde68a; border-radius: 6px; padding: 12px 16px; margin: 20px 0; }
        .expiry p { color: #92400e; font-size: 13px; margin: 0; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0 0 8px; line-height: 1.6; }
        .link-fallback { word-break: break-all; color: #537d4d; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="brand">Kommunity</div>
        <h1>{{ __('application.mail_approved_title') }}</h1>
    </div>
    <div class="body">
        <p>{{ __('application.mail_approved_greeting', ['name' => explode(' ', trim($user->name))[0]]) }}</p>
        <p>{{ __('application.mail_approved_line1') }}</p>

        @if($planet)
            <div class="planet-box">
                <p>🪐 {{ __('application.mail_approved_planet', ['planet' => $planet->name]) }}</p>
            </div>
        @endif

        <p>{{ __('application.mail_approved_line2') }}</p>

        <div class="btn-wrap">
            <a href="{{ $setPasswordUrl }}" class="btn">{{ __('application.mail_approved_button') }}</a>
        </div>

        <div class="expiry">
            <p>⚠️ {{ __('application.mail_approved_expiry', ['minutes' => $expireMinutes, 'forgot' => __('application.mail_approved_forgot')]) }}</p>
        </div>

        <p>{{ __('application.mail_approved_line3') }}</p>

        <p style="color:#6b7280; font-size:13px;">{{ app()->getLocale() === 'it' ? 'Se il pulsante non funziona, copia e incolla questo link nel browser:' : 'If the button does not work, copy and paste this link into your browser:' }}</p>
        <p class="link-fallback">{{ $setPasswordUrl }}</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Kommunity</p>
    </div>
</div>
</body>
</html>
