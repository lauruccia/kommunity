{{-- ╔══════════════════════════════════════════════════════════════════╗
     ║  KOMMUNITY — Biglietto da visita digitale                        ║
     ║  Pagina pubblica standalone: nessun layout, nessuna navigazione  ║
     ║  URL: /card/{slug}                                               ║
     ╚══════════════════════════════════════════════════════════════════╝ --}}
@php
    // Iniziali avatar (es. "Marco Conti" → "MC")
    $initials = collect(explode(' ', trim($user->name)))
        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->take(2)
        ->implode('');

    // URL assoluto avatar (per OG e display)
    $avatarUrl = $profile?->avatarUrl();

    // Professione da mostrare (prima delle professions many-to-many)
    $professionLabel = $profile?->professions?->first()?->name
        ?? $profile?->profession?->name
        ?? null;

    // Tags: categorie professionali
    $tags = $profile?->professions?->take(4) ?? collect();

    // Capitolo/Pianeta
    $chapterName = $profile?->chapter?->name ?? null;

    // Contatti condizionali
    $showPhone     = $profile?->show_phone && $profile->phone;
    $showEmail     = $profile?->show_email && $user->email;
    $showLinkedin  = (bool) $profile?->linkedin_url;
    $showWebsite   = (bool) $profile?->website;
    $showInstagram = (bool) $profile?->instagram_url;
    $showFacebook  = (bool) $profile?->facebook_url;
    $showCity      = (bool) $profile?->city?->name;

    // URL QR (servizio esterno, zero dipendenze server)
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data='
        . urlencode($cardUrl)
        . '&size=300x300&margin=8&color=263d2a&bgcolor=f6faf8';
    $qrDownloadUrl = 'https://api.qrserver.com/v1/create-qr-code/?data='
        . urlencode($cardUrl)
        . '&size=600x600&margin=16&color=263d2a&bgcolor=ffffff';

    // OG description
    $ogDescription = $profile?->short_bio
        ?? ($professionLabel ? $professionLabel . ($profile?->company_name ? ' · ' . $profile->company_name : '') : config('app.name'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>{{ $user->name }} — Kommunity</title>

    {{-- ── Open Graph (anteprima WhatsApp, Telegram, email, LinkedIn) ── --}}
    <meta property="og:type"        content="profile">
    <meta property="og:title"       content="{{ $user->name }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url"         content="{{ $cardUrl }}">
    <meta property="og:site_name"   content="Kommunity">
    @if($avatarUrl)
    <meta property="og:image"       content="{{ $avatarUrl }}">
    <meta property="og:image:width"  content="400">
    <meta property="og:image:height" content="400">
    @endif
    <meta name="twitter:card"        content="summary">
    <meta name="twitter:title"       content="{{ $user->name }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @if($avatarUrl)
    <meta name="twitter:image"       content="{{ $avatarUrl }}">
    @endif

    {{-- ── Font ── --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet">

    {{-- ── Design tokens Kommunity (colori, raggi, ecc.) ── --}}
    <link rel="stylesheet" href="{{ asset('css/kommunity.css') }}?v={{ filemtime(public_path('css/kommunity.css')) }}">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--km-bg);
            color: var(--km-ink);
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Contenitore carta ── */
        .kc-wrap {
            width: 100%;
            max-width: 480px;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: var(--km-surface);
        }

        /* ── Hero scuro ── */
        .kc-hero {
            background:
                radial-gradient(circle at 80% -10%, rgba(139,197,63,.14), transparent 30%),
                radial-gradient(circle at 8% 22%, rgba(45,212,191,.08), transparent 32%),
                linear-gradient(160deg, var(--km-dark) 0%, var(--km-dark-2) 60%, #073040 100%);
            padding: 1.25rem 1.5rem 2.5rem;
            text-align: center;
            position: relative;
        }

        /* Raccordo hero → body */
        .kc-hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2rem;
            background: var(--km-surface);
            border-radius: 1.5rem 1.5rem 0 0;
        }

        .kc-avatar-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: .625rem;
        }

        .kc-avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            border: 2px solid rgba(139,197,63,.35);
            object-fit: cover;
            display: block;
        }

        .kc-avatar-initials {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            border: 2px solid rgba(139,197,63,.35);
            background: linear-gradient(135deg, #1d3a28, #2d5a3d);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 500;
            color: var(--km-green);
            letter-spacing: .04em;
        }

        .kc-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--km-text);
            margin-bottom: .2rem;
        }

        .kc-role {
            font-size: .8125rem;
            color: var(--km-text-muted);
            margin-bottom: .15rem;
        }

        .kc-company {
            font-size: .75rem;
            color: rgba(170,183,196,.7);
            margin-bottom: 0;
        }

        .kc-chapter {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            background: rgba(139,197,63,.12);
            border: .5px solid rgba(139,197,63,.28);
            border-radius: 2rem;
            padding: .25rem .75rem;
            font-size: .75rem;
            color: var(--km-green);
            margin-bottom: .75rem;
        }

        .kc-bio {
            font-size: .8125rem;
            color: rgba(170,183,196,.85);
            line-height: 1.55;
            max-width: 340px;
            margin: .5rem auto 0;
        }

        /* ── Body card ── */
        .kc-body {
            flex: 1;
            padding: .125rem 1rem .75rem;
        }

        /* ── Bottoni azione ── */
        .kc-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(56px, 1fr));
            gap: .4rem;
            margin-bottom: .75rem;
        }

        .kc-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .25rem;
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            border-radius: var(--km-radius-sm);
            padding: .6rem .375rem .5rem;
            text-decoration: none;
            color: var(--km-muted);
            font-size: .6875rem;
            font-weight: 500;
            transition: border-color var(--km-transition), background var(--km-transition);
        }

        .kc-action:hover {
            border-color: var(--km-accent-soft);
            background: #f0f7ee;
        }

        .kc-action svg {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        .kc-action--phone  svg { stroke: var(--km-accent-strong); }
        .kc-action--wa     svg { stroke: #16a34a; }
        .kc-action--email  svg { stroke: #0369a1; }
        .kc-action--li     svg { stroke: #0369a1; }
        .kc-action--web    svg { stroke: var(--km-accent-strong); }
        .kc-action--ig     svg { stroke: #be185d; }
        .kc-action--fb     svg { stroke: #1d4ed8; }

        /* ── Tags professioni ── */
        .kc-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .375rem;
            margin-bottom: 1.125rem;
        }

        .kc-tag {
            font-size: .6875rem;
            font-weight: 500;
            padding: .25rem .75rem;
            background: var(--km-accent-soft);
            color: var(--km-accent-strong);
            border-radius: 2rem;
        }

        /* ── Info list ── */
        .kc-info {
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            border-radius: var(--km-radius-sm);
            padding: .25rem .875rem;
            margin-bottom: 1rem;
        }

        .kc-info-row {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: .625rem 0;
            border-bottom: .5px solid var(--km-line);
            font-size: .8125rem;
        }

        .kc-info-row:last-child { border-bottom: none; }

        .kc-info-row svg {
            width: 17px;
            height: 17px;
            stroke: var(--km-muted);
            flex-shrink: 0;
        }

        .kc-info-row .lbl { color: var(--km-muted); flex: 1; }

        .kc-info-row .val {
            color: var(--km-ink);
            font-weight: 500;
            font-size: .75rem;
            text-align: right;
        }

        .kc-info-row a.val {
            color: var(--km-accent-strong);
            text-decoration: none;
        }

        /* ── QR section ── */
        .kc-qr {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            border-radius: var(--km-radius-sm);
            padding: .7rem;
            margin-bottom: .5rem;
        }

        .kc-qr-img {
            width: 64px;
            height: 64px;
            border-radius: .5rem;
            border: .5px solid var(--km-line-strong);
            flex-shrink: 0;
            object-fit: cover;
            background: #fff;
        }

        .kc-qr-info h4 {
            font-size: .8125rem;
            font-weight: 600;
            color: var(--km-ink);
            margin-bottom: .2rem;
        }

        .kc-qr-info p {
            font-size: .6875rem;
            color: var(--km-muted);
            margin-bottom: .5rem;
            word-break: break-all;
        }

        .kc-qr-dl {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .6875rem;
            font-weight: 500;
            color: var(--km-accent-strong);
            background: var(--km-accent-soft);
            border-radius: .375rem;
            padding: .25rem .625rem;
            text-decoration: none;
        }

        .kc-qr-dl svg { width: 13px; height: 13px; stroke: var(--km-accent-strong); }

        /* ── Bottoni principali (riga a 2 colonne) ── */
        .kc-btn-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .4rem;
            margin-bottom: .5rem;
        }

        .kc-btn-save,
        .kc-btn-share {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            padding: .75rem .5rem;
            font-family: inherit;
            font-size: .875rem;
            font-weight: 600;
            border-radius: var(--km-radius-sm);
            text-decoration: none;
            cursor: pointer;
            transition: opacity var(--km-transition);
        }

        .kc-btn-save {
            background: linear-gradient(135deg, var(--km-green), #5f9d42);
            color: #061018;
            border: none;
        }

        .kc-btn-save:hover { opacity: .9; }
        .kc-btn-save svg { width: 18px; height: 18px; stroke: #061018; flex-shrink: 0; }

        .kc-btn-share {
            background: transparent;
            color: var(--km-accent-strong);
            border: 1.5px solid var(--km-accent);
        }

        .kc-btn-share:hover { background: var(--km-accent-soft); }
        .kc-btn-share svg { width: 18px; height: 18px; stroke: var(--km-accent-strong); flex-shrink: 0; }

        /* Feedback copia link */
        .kc-copied {
            display: none;
            text-align: center;
            font-size: .75rem;
            color: var(--km-accent-strong);
            margin-bottom: .375rem;
        }

        /* ── Footer ── */
        .kc-footer {
            border-top: .5px solid var(--km-line);
            padding: .75rem 1rem 1rem;
        }

        /* Footer ospite: solo credits discreti */
        .kc-footer-guest {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            font-size: .75rem;
            color: var(--km-muted);
        }

        .kc-footer-guest a {
            color: var(--km-accent);
            text-decoration: none;
            font-weight: 500;
        }

        /* Footer loggato: azioni community */
        .kc-footer-logged p {
            font-size: .75rem;
            color: var(--km-muted);
            text-align: center;
            margin-bottom: .75rem;
        }

        .kc-footer-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .625rem;
        }

        .kc-footer-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            padding: .625rem .5rem;
            border-radius: .75rem;
            font-family: inherit;
            font-size: .8125rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: opacity var(--km-transition);
        }

        .kc-footer-btn:hover { opacity: .85; }
        .kc-footer-btn svg { width: 16px; height: 16px; }

        .kc-footer-btn--primary {
            background: linear-gradient(135deg, var(--km-green), #5f9d42);
            color: #061018;
        }

        .kc-footer-btn--primary svg { stroke: #061018; }

        .kc-footer-btn--secondary {
            background: var(--km-surface-strong);
            color: var(--km-ink);
            border: .5px solid var(--km-line-strong);
        }

        .kc-footer-btn--secondary svg { stroke: var(--km-ink); }
    </style>
</head>
<body>
<div class="kc-wrap">

    {{-- ════════════════ HERO ════════════════ --}}
    <div class="kc-hero">
        <div class="kc-avatar-wrap">
            @if($avatarUrl)
                <img class="kc-avatar" src="{{ $avatarUrl }}" alt="{{ $user->name }}">
            @else
                <div class="kc-avatar-initials" aria-hidden="true">{{ $initials }}</div>
            @endif
        </div>

        <h1 class="kc-name">{{ $user->name }}</h1>

        @if($professionLabel)
            <p class="kc-role">{{ $professionLabel }}</p>
        @endif

        @if($profile?->company_name)
            <p class="kc-company">{{ $profile->company_name }}</p>
        @endif
    </div>

    {{-- ════════════════ BODY ════════════════ --}}
    <div class="kc-body">

        {{-- ── Bottoni azione rapida ── --}}
        @if($showPhone || $whatsappUrl || $showEmail || $showLinkedin || $showWebsite)
        <div class="kc-actions" role="list">

            @if($showPhone)
            <a class="kc-action kc-action--phone" href="tel:{{ $profile->phone }}" aria-label="Chiama {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.01 2.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                Chiama
            </a>
            @endif

            @if($whatsappUrl)
            <a class="kc-action kc-action--wa" href="{{ $whatsappUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                WhatsApp
            </a>
            @endif

            @if($showEmail)
            <a class="kc-action kc-action--email" href="mailto:{{ $user->email }}" aria-label="Email {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Email
            </a>
            @endif

            @if($showLinkedin)
            <a class="kc-action kc-action--li" href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" aria-label="LinkedIn {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                LinkedIn
            </a>
            @endif

            @if($showWebsite)
            <a class="kc-action kc-action--web" href="{{ $profile->website }}" target="_blank" rel="noopener" aria-label="Sito web {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                Sito web
            </a>
            @endif

            @if($showInstagram)
            <a class="kc-action kc-action--ig" href="{{ $profile->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                Instagram
            </a>
            @endif

            @if($showFacebook)
            <a class="kc-action kc-action--fb" href="{{ $profile->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook {{ $user->name }}" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                Facebook
            </a>
            @endif

        </div>
        @endif

        {{-- ── Info: città e sito web ── --}}
        @if($showCity || $showWebsite)
        <div class="kc-info">
            @if($showCity)
            <div class="kc-info-row">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <span class="lbl">Città</span>
                <span class="val">{{ $profile->city->name }}</span>
            </div>
            @endif
            @if($showWebsite)
            <div class="kc-info-row">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                <span class="lbl">Sito web</span>
                <a class="val" href="{{ $profile->website }}" target="_blank" rel="noopener">
                    {{ preg_replace('#^https?://(www\.)?#', '', rtrim($profile->website, '/')) }}
                </a>
            </div>
            @endif
        </div>
        @endif

        {{-- ── QR code ── --}}
        <div class="kc-qr">
            <img class="kc-qr-img" src="{{ $qrUrl }}" alt="QR code profilo {{ $user->name }}" loading="lazy" width="64" height="64">
            <div class="kc-qr-info">
                <h4>QR del profilo</h4>
                <p>{{ parse_url($cardUrl, PHP_URL_HOST) }}/card/{{ $onepage->slug }}</p>
                <a class="kc-qr-dl" href="{{ $qrDownloadUrl }}" target="_blank" rel="noopener" aria-label="Scarica QR code come immagine PNG">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Scarica PNG
                </a>
            </div>
        </div>

        {{-- ── Salva contatto + Condividi (riga a 2 colonne) ── --}}
        <div class="kc-btn-row">
            <a class="kc-btn-save" href="{{ route('card.vcard', $onepage->slug) }}" aria-label="Scarica il contatto di {{ $user->name }} in formato vCard">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="18" rx="2"/><path d="M8 10a2 2 0 104 0 2 2 0 00-4 0"/><path d="M4 20c0-2.21 1.79-4 4-4h4c2.21 0 4 1.79 4 4"/><path d="M16 8h2M16 12h2"/></svg>
                Salva
            </a>
            <button class="kc-btn-share" id="kc-share-btn" type="button" aria-label="Condividi profilo di {{ $user->name }}">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Condividi
            </button>
        </div>
        <p class="kc-copied" id="kc-copied-msg" role="status" aria-live="polite">✓ Link copiato negli appunti</p>

    </div>{{-- /.kc-body --}}

    {{-- ════════════════ FOOTER ════════════════ --}}
    <div class="kc-footer">

        @auth
            {{-- Utente loggato: azioni community --}}
            @if(auth()->id() !== $user->id)
            <div class="kc-footer-logged">
                <p>Sei già su Kommunity — connettiti con {{ explode(' ', $user->name)[0] }}</p>
                <div class="kc-footer-btns">
                    <a class="kc-footer-btn kc-footer-btn--primary"
                       href="{{ route('conversations.start') }}"
                       onclick="event.preventDefault(); document.getElementById('kc-msg-form').submit();"
                       aria-label="Scrivi un messaggio a {{ $user->name }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        Scrivi
                    </a>
                    <a class="kc-footer-btn kc-footer-btn--secondary"
                       href="{{ route('members.show', $onepage->slug) }}"
                       aria-label="Profilo completo di {{ $user->name }} su Kommunity">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profilo completo
                    </a>
                </div>
                {{-- Form nascosto per avviare la conversazione --}}
                <form id="kc-msg-form" method="POST" action="{{ route('conversations.start') }}" style="display:none">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                </form>
            </div>
            @else
            {{-- È il proprio profilo --}}
            <div class="kc-footer-logged">
                <p>Questa è la tua card Kommunity — condividila per farti conoscere</p>
                <div class="kc-footer-btns">
                    <a class="kc-footer-btn kc-footer-btn--secondary"
                       href="{{ route('profile.edit') }}"
                       style="grid-column: 1 / -1">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Modifica il tuo profilo
                    </a>
                </div>
            </div>
            @endif
        @else
            {{-- Ospite: solo credits discreti --}}
            <div class="kc-footer-guest">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--km-accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Powered by <a href="{{ route('home') }}" target="_blank" rel="noopener">Kommunity</a>
            </div>
        @endauth

    </div>{{-- /.kc-footer --}}

</div>{{-- /.kc-wrap --}}

<script>
(function () {
    var btn     = document.getElementById('kc-share-btn');
    var msg     = document.getElementById('kc-copied-msg');
    var url     = '{{ $cardUrl }}';
    var title   = '{{ addslashes($user->name) }}';
    var text    = '{{ addslashes($ogDescription) }}';

    if (!btn) return;

    btn.addEventListener('click', function () {
        if (navigator.share) {
            navigator.share({ title: title, text: text, url: url }).catch(function () {});
        } else {
            navigator.clipboard.writeText(url).then(function () {
                msg.style.display = 'block';
                setTimeout(function () { msg.style.display = 'none'; }, 2500);
            }).catch(function () {
                // Fallback estremo: prompt di selezione testo
                window.prompt('Copia il link:', url);
            });
        }
    });
}());
</script>

</body>
</html>
