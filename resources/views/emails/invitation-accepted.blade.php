<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invito accettato</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a1f2e; padding: 32px 40px; text-align: center; }
        .header .brand { color: #9ad84a; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; }
        .header h1 { color: #ffffff; font-size: 22px; margin: 12px 0 0; font-weight: 600; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .info-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
        .info-box .label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 4px; }
        .info-box .value { color: #111827; font-size: 15px; font-weight: 600; margin: 0 0 12px; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #9ca3af; font-size: 12px; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="brand">Kommunity</div>
        <h1>✓ Invito accettato!</h1>
    </div>
    <div class="body">
        <p>Ottima notizia! Il tuo invito al Pianeta <strong>{{ $chapter?->name }}</strong> è stato accettato.</p>

        <div class="info-box">
            <p class="label">Nuovo membro</p>
            <p class="value">{{ $newUser->name }}</p>
            <p class="label">Email</p>
            <p class="value" style="margin-bottom:0;">{{ $newUser->email }}</p>
        </div>

        <p>{{ $newUser->name }} è ora parte del Pianeta <strong>{{ $chapter?->name }}</strong> e potrà accedere a tutti i contenuti della community.</p>
        <p>Puoi visualizzare i dettagli del nuovo membro dal pannello di amministrazione.</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Kommunity — tutti i diritti riservati</p>
    </div>
</div>
</body>
</html>
