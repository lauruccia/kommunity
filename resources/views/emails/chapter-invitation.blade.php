<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invito a {{ $chapter?->name ?? 'Kommunity' }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 580px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a1f2e; padding: 32px 40px; text-align: center; }
        .header .brand { color: #9ad84a; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; }
        .header h1 { color: #ffffff; font-size: 22px; margin: 12px 0 0; font-weight: 600; }
        .body { padding: 36px 40px; }
        .body p { color: #374151; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
        .message-box { background: #f8fafc; border-left: 3px solid #9ad84a; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .message-box p { color: #4b5563; font-size: 14px; margin: 0; font-style: italic; }
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
        <h1>Sei invitato in {{ $chapter?->name ?? 'un Pianeta' }}</h1>
    </div>
    <div class="body">
        <p>Ciao,</p>

        @if($invitedBy)
            <p><strong>{{ $invitedBy->name }}</strong> ti ha invitato a entrare nel Pianeta <strong>{{ $chapter?->name }}</strong> su Kommunity.</p>
        @else
            <p>Sei stato invitato a entrare nel Pianeta <strong>{{ $chapter?->name }}</strong> su Kommunity.</p>
        @endif

        @if($message)
            <div class="message-box">
                <p>"{{ $message }}"</p>
            </div>
        @endif

        <p>Kommunity è la community professionale dove costruire relazioni di valore con altri professionisti del tuo settore.</p>

        <div class="btn-wrap">
            <a href="{{ $inviteUrl }}" class="btn">Accetta l'invito</a>
        </div>

        @if($expiresAt)
            <div class="expiry">
                <p>⚠️ Questo invito scade il <strong>{{ $expiresAt->format('d/m/Y') }}</strong>.</p>
            </div>
        @endif

        <p style="color:#6b7280; font-size:13px;">Se il pulsante non funziona, copia e incolla questo link nel browser:</p>
        <p class="link-fallback">{{ $inviteUrl }}</p>
    </div>
    <div class="footer">
        <p>Hai ricevuto questa email perché qualcuno ha usato il tuo indirizzo per un invito su Kommunity.<br>Se non conosci il mittente, ignora questa email.</p>
        <p>© {{ date('Y') }} Kommunity — tutti i diritti riservati</p>
    </div>
</div>
</body>
</html>
