{{-- ╔══════════════════════════════════════════════════════════════════╗
     ║  KOMMUNITY — Biglietto da visita digitale  v3                    ║
     ║  Pagina pubblica standalone: nessun layout, nessuna navigazione  ║
     ║  URL: /card/{slug}   — auto-detect lingua visitatore             ║
     ╚══════════════════════════════════════════════════════════════════╝ --}}
@php
    // ── Iniziali avatar ──────────────────────────────────────────────
    $initials = collect(explode(' ', trim($user->name)))
        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->take(2)->implode('');

    $avatarUrl = $profile?->avatarUrl();

    // ── Professione ──────────────────────────────────────────────────
    $professionLabel = $profile?->professions?->first()?->name
        ?? $profile?->profession?->name
        ?? null;

    // ── Contatti condizionali ────────────────────────────────────────
    $showPhone     = $profile?->show_phone    && $profile->phone;
    $showEmail     = $profile?->show_email    && $user->email;
    $showWhatsapp  = $profile?->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number;
    $showLinkedin  = (bool) $profile?->linkedin_url;
    $showWebsite   = (bool) $profile?->website;
    $showInstagram = (bool) $profile?->instagram_url;
    $showFacebook  = (bool) $profile?->facebook_url;
    $showCity      = (bool) $profile?->city?->name;

    $hasSocialFoot = true;
    $hasContacts   = $showPhone || $showEmail || $showWebsite || $showCity;

    // ── URL QR ───────────────────────────────────────────────────────
    $qrUrl         = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($cardUrl) . '&size=280x280&margin=6&color=263d2a&bgcolor=f6faf8';
    $qrDownloadUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($cardUrl) . '&size=600x600&margin=16&color=263d2a&bgcolor=ffffff';

    // ── OG description ───────────────────────────────────────────────
    $ogDescription = $profile?->short_bio
        ?? ($professionLabel ? $professionLabel . ($profile?->company_name ? ' · ' . $profile->company_name : '') : config('app.name'));

    // ── Google Maps per la città ────────────────────────────────────
    $cityMapsUrl = $showCity
        ? 'https://maps.google.com/?q=' . urlencode($profile->city->name)
        : null;

    // ── Traduzioni UI (auto-detect lingua dal controller) ────────────
    $translations = [
        'it' => [
            'save'           => 'Aggiungi ai contatti',
            'share'          => 'Condividi',
            'qr_title'       => 'QR del profilo',
            'qr_dl'          => 'Scarica PNG',
            'already_on'     => 'Sei già su Kommunity',
            'connect_with'   => 'Connettiti con',
            'write'          => 'Scrivi',
            'full_profile'   => 'Profilo completo',
            'your_card'      => 'Questa è la tua card',
            'edit_profile'   => 'Modifica profilo',
            'powered_by'     => 'Powered by',
            'view_on_komm'   => 'Vedi il profilo su Kommunity',
            'cta_card'       => 'Crea anche tu il biglietto da visita digitale',
            'cta_register'   => 'Registrati con l\'invito di',
            'cta_planet'     => 'Entrerai nel Pianeta',
        ],
        'en' => [
            'save'           => 'Add to contacts',
            'share'          => 'Share',
            'qr_title'       => 'Profile QR',
            'qr_dl'          => 'Download PNG',
            'already_on'     => 'You\'re on Kommunity',
            'connect_with'   => 'Connect with',
            'write'          => 'Message',
            'full_profile'   => 'Full profile',
            'your_card'      => 'This is your card',
            'edit_profile'   => 'Edit profile',
            'powered_by'     => 'Powered by',
            'view_on_komm'   => 'View profile on Kommunity',
            'cta_card'       => 'Create your digital business card too',
            'cta_register'   => 'Join with the invite of',
            'cta_planet'     => 'You\'ll join the Planet',
        ],
        'fr' => [
            'save'           => 'Ajouter aux contacts',
            'share'          => 'Partager',
            'qr_title'       => 'QR du profil',
            'qr_dl'          => 'Télécharger PNG',
            'already_on'     => 'Vous êtes sur Kommunity',
            'connect_with'   => 'Connectez-vous avec',
            'write'          => 'Écrire',
            'full_profile'   => 'Profil complet',
            'your_card'      => 'C\'est votre carte',
            'edit_profile'   => 'Modifier le profil',
            'powered_by'     => 'Propulsé par',
            'view_on_komm'   => 'Voir le profil sur Kommunity',
            'cta_card'       => 'Créez vous aussi votre carte de visite digitale',
            'cta_register'   => 'Inscrivez-vous avec l\'invitation de',
            'cta_planet'     => 'Vous rejoindrez la Planète',
        ],
        'es' => [
            'save'           => 'Añadir a contactos',
            'share'          => 'Compartir',
            'qr_title'       => 'QR del perfil',
            'qr_dl'          => 'Descargar PNG',
            'already_on'     => 'Estás en Kommunity',
            'connect_with'   => 'Conéctate con',
            'write'          => 'Escribir',
            'full_profile'   => 'Perfil completo',
            'your_card'      => 'Esta es tu tarjeta',
            'edit_profile'   => 'Editar perfil',
            'powered_by'     => 'Desarrollado por',
            'view_on_komm'   => 'Ver perfil en Kommunity',
            'cta_card'       => 'Crea también tu tarjeta de visita digital',
            'cta_register'   => 'Regístrate con la invitación de',
            'cta_planet'     => 'Entrarás al Planeta',
        ],
        'de' => [
            'save'           => 'Zu Kontakten hinzufügen',
            'share'          => 'Teilen',
            'qr_title'       => 'Profil-QR',
            'qr_dl'          => 'PNG herunterladen',
            'already_on'     => 'Du bist auf Kommunity',
            'connect_with'   => 'Verbinde dich mit',
            'write'          => 'Schreiben',
            'full_profile'   => 'Vollständiges Profil',
            'your_card'      => 'Das ist deine Karte',
            'edit_profile'   => 'Profil bearbeiten',
            'powered_by'     => 'Bereitgestellt von',
            'view_on_komm'   => 'Profil auf Kommunity ansehen',
            'cta_card'       => 'Erstelle auch du deine digitale Visitenkarte',
            'cta_register'   => 'Registriere dich mit der Einladung von',
            'cta_planet'     => 'Du trittst dem Planeten bei',
        ],
        'ro' => [
            'save'           => 'Adaugă la contacte',
            'share'          => 'Distribuie',
            'qr_title'       => 'QR profil',
            'qr_dl'          => 'Descarcă PNG',
            'already_on'     => 'Ești pe Kommunity',
            'connect_with'   => 'Conectează-te cu',
            'write'          => 'Scrie',
            'full_profile'   => 'Profil complet',
            'your_card'      => 'Acesta este cardul tău',
            'edit_profile'   => 'Editează profilul',
            'powered_by'     => 'Oferit de',
            'view_on_komm'   => 'Vezi profilul pe Kommunity',
            'cta_card'       => 'Creează și tu cartea de vizită digitală',
            'cta_register'   => 'Înregistrează-te cu invitația lui',
            'cta_planet'     => 'Vei intra în Planeta',
        ],
    ];

    $t = $translations[$locale] ?? $translations['it'];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>{{ $user->name }} — Kommunity</title>

    {{-- ── Open Graph ── --}}
    <meta property="og:type"         content="profile">
    <meta property="og:title"        content="{{ $user->name }}">
    <meta property="og:description"  content="{{ $ogDescription }}">
    <meta property="og:url"          content="{{ $cardUrl }}">
    <meta property="og:site_name"    content="Kommunity">
    @if($avatarUrl)
    <meta property="og:image"        content="{{ $avatarUrl }}">
    <meta property="og:image:width"  content="400">
    <meta property="og:image:height" content="400">
    @endif
    <meta name="twitter:card"        content="summary">
    <meta name="twitter:title"       content="{{ $user->name }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @if($avatarUrl)
    <meta name="twitter:image"       content="{{ $avatarUrl }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kommunity.css') }}?v={{ filemtime(public_path('css/kommunity.css')) }}">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { -webkit-text-size-adjust: 100%; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--km-surface);
            color: var(--km-ink);
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .kc-wrap {
            width: 100%;
            max-width: 480px;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: var(--km-surface);
        }

        /* ── HERO ─────────────────────────────────────────────────── */
        .kc-hero {
            background:
                radial-gradient(circle at 80% -10%, rgba(139,197,63,.14), transparent 30%),
                radial-gradient(circle at 8% 22%, rgba(45,212,191,.08), transparent 32%),
                linear-gradient(160deg, var(--km-dark) 0%, var(--km-dark-2) 60%, #073040 100%);
            padding: 1.25rem 1.5rem 2.75rem;
            text-align: center;
            position: relative;
        }
        .kc-hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1.5rem;
            background: var(--km-surface);
            border-radius: 1.25rem 1.25rem 0 0;
        }
        .kc-avatar-wrap { display: inline-block; margin-bottom: .5rem; }
        .kc-avatar,
        .kc-avatar-initials {
            width: 72px; height: 72px;
            border-radius: 50%;
            border: 2px solid rgba(139,197,63,.35);
        }
        .kc-avatar { object-fit: cover; display: block; }
        .kc-avatar-initials {
            background: linear-gradient(135deg, #1d3a28, #2d5a3d);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.375rem; font-weight: 500;
            color: var(--km-green); letter-spacing: .04em;
        }
        .kc-name    { font-size: 1.2rem; font-weight: 600; color: var(--km-text); margin-bottom: .18rem; }
        .kc-role    { font-size: .8rem; color: var(--km-text-muted); margin-bottom: .12rem; }
        .kc-company { font-size: .72rem; color: rgba(170,183,196,.7); }

        /* ── BODY ─────────────────────────────────────────────────── */
        .kc-body {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
            padding: .2rem .875rem .75rem;
            background: var(--km-surface);
        }

        /* Bottone primario */
        .kc-btn-save {
            display: flex; align-items: center; justify-content: center; gap: .4rem;
            width: 100%; padding: .75rem 1rem;
            background: linear-gradient(135deg, var(--km-green), #5f9d42);
            color: #061018; font-family: inherit; font-size: .9375rem; font-weight: 600;
            border: none; border-radius: var(--km-radius-sm);
            text-decoration: none; cursor: pointer;
            margin-bottom: .75rem;
            transition: opacity var(--km-transition);
        }
        .kc-btn-save:hover { opacity: .9; }
        .kc-btn-save svg { width: 17px; height: 17px; stroke: #061018; flex-shrink: 0; }

        /* ── Sezione CONTATTI ─────────────────────────────────────── */
        .kc-contacts {
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            border-radius: var(--km-radius-sm);
            padding: .1rem .75rem;
            margin-bottom: .625rem;
        }
        .kc-contact-row {
            display: flex; align-items: center; gap: .625rem;
            padding: .55rem 0;
            border-bottom: .5px solid var(--km-line);
            font-size: .8125rem; color: var(--km-ink);
            text-decoration: none;
        }
        .kc-contact-row:last-child { border-bottom: none; }
        a.kc-contact-row:hover { color: var(--km-accent-strong); }
        a.kc-contact-row:hover .kc-ci { opacity: .8; }

        /* Icona cerchio inline */
        .kc-ci {
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .kc-ci svg { width: 13px; height: 13px; }
        .kc-ci--phone { background: #dbe7d8; }
        .kc-ci--phone svg { stroke: #3b6d11; }
        .kc-ci--email { background: #dbeafe; }
        .kc-ci--email svg { stroke: #1d4ed8; }
        .kc-ci--web   { background: #dbe7d8; }
        .kc-ci--web   svg { stroke: #3b6d11; }
        .kc-ci--city  { background: #f3f4f6; }
        .kc-ci--city  svg { stroke: var(--km-muted); }

        /* ── QR section ───────────────────────────────────────────── */
        .kc-qr {
            display: flex; align-items: center; gap: .625rem;
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            border-radius: var(--km-radius-sm);
            padding: .55rem; margin-bottom: .5rem;
        }
        .kc-qr-img {
            width: 52px; height: 52px; flex-shrink: 0;
            border-radius: .4rem; border: .5px solid var(--km-line-strong);
            object-fit: cover; background: #fff;
        }
        .kc-qr-info h4   { font-size: .78rem; font-weight: 600; color: var(--km-ink); margin-bottom: .1rem; }
        .kc-qr-info p    { font-size: .63rem; color: var(--km-muted); margin-bottom: .35rem; word-break: break-all; }
        .kc-qr-dl {
            display: inline-flex; align-items: center; gap: .25rem;
            font-size: .63rem; font-weight: 500;
            color: var(--km-accent-strong); background: var(--km-accent-soft);
            border-radius: .375rem; padding: .18rem .5rem; text-decoration: none;
        }
        .kc-qr-dl svg { width: 10px; height: 10px; stroke: var(--km-accent-strong); }

        /* Bottone condividi */
        .kc-btn-share {
            display: flex; align-items: center; justify-content: center; gap: .4rem;
            width: 100%; padding: .7rem;
            background: transparent; color: var(--km-accent-strong);
            font-family: inherit; font-size: .9375rem; font-weight: 600;
            border: 1.5px solid var(--km-accent); border-radius: var(--km-radius-sm);
            cursor: pointer; margin-bottom: .5rem;
            transition: background var(--km-transition);
        }
        .kc-btn-share:hover { background: var(--km-accent-soft); }
        .kc-btn-share svg { width: 17px; height: 17px; stroke: var(--km-accent-strong); flex-shrink: 0; }

        .kc-copied {
            display: none; text-align: center;
            font-size: .72rem; color: var(--km-accent-strong);
            padding: .2rem 0 .1rem;
        }

        /* ── Social in fondo (cerchi neutri, nessuna label) ─── */
        .kc-socials-foot {
            display: flex; flex-wrap: wrap;
            justify-content: center; gap: .65rem;
            padding: .65rem 0 .35rem;
            border-top: .5px solid var(--km-line);
        }
        .kc-sf {
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            transition: opacity var(--km-transition);
            flex-shrink: 0;
        }
        .kc-sf:hover { opacity: .7; }
        .kc-sf svg { width: 21px; height: 21px; stroke: var(--km-muted); }
        .kc-sf-img {
            width: 23px; height: 23px;
            object-fit: contain;
            display: block;
        }

        /* ── Footer ───────────────────────────────────────────────── */
        .kc-footer {
            border-top: .5px solid var(--km-line);
            background: var(--km-surface);
            padding: .625rem .875rem .9rem;
        }
        .kc-footer-guest {
            text-align: center;
            font-size: .72rem; color: var(--km-muted);
        }
        .kc-footer-guest a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 42px;
            padding: .65rem .9rem;
            border-radius: .75rem;
            background: var(--km-accent-soft);
            border: .5px solid rgba(66, 98, 64, .18);
            color: var(--km-accent-strong);
            text-decoration: none;
            font-size: .82rem;
            font-weight: 650;
            line-height: 1.25;
            box-shadow: 0 6px 16px rgba(66, 98, 64, .08);
        }
        .kc-footer-guest a:hover { background: rgba(139, 197, 63, .18); }
        .kc-footer-planet {
            display: inline-flex; align-items: center; gap: .25rem;
            margin-top: .2rem; font-size: .67rem; color: var(--km-muted);
        }

        /* Link profilo Kommunity nel body */
        .kc-profile-link {
            display: flex; align-items: center; justify-content: center; gap: .35rem;
            width: 100%; padding: .55rem 1rem;
            background: var(--km-surface-strong);
            border: .5px solid var(--km-line);
            border-radius: var(--km-radius-sm);
            font-family: inherit; font-size: .8125rem; font-weight: 500;
            color: var(--km-accent-strong); text-decoration: none;
            margin-bottom: .625rem;
            transition: opacity var(--km-transition);
        }
        .kc-profile-link:hover { opacity: .8; }
        .kc-profile-link svg { width: 14px; height: 14px; stroke: var(--km-accent-strong); flex-shrink: 0; }
        .kc-footer-logged p {
            font-size: .72rem; color: var(--km-muted);
            text-align: center; margin-bottom: .5rem;
        }
        .kc-footer-btns { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
        .kc-footer-btn {
            display: flex; align-items: center; justify-content: center; gap: .35rem;
            padding: .55rem .5rem; border-radius: .75rem;
            font-family: inherit; font-size: .78rem; font-weight: 600;
            text-decoration: none; cursor: pointer; border: none;
            transition: opacity var(--km-transition);
        }
        .kc-footer-btn:hover { opacity: .85; }
        .kc-footer-btn svg { width: 14px; height: 14px; }
        .kc-footer-btn--primary { background: linear-gradient(135deg, var(--km-green), #5f9d42); color: #061018; }
        .kc-footer-btn--primary svg { stroke: #061018; }
        .kc-footer-btn--secondary { background: var(--km-surface-strong); color: var(--km-ink); border: .5px solid var(--km-line-strong); }
        .kc-footer-btn--secondary svg { stroke: var(--km-ink); }
    </style>
</head>
<body>
<div class="kc-wrap">

    {{-- ════════ HERO ════════ --}}
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

    {{-- ════════ BODY ════════ --}}
    <div class="kc-body">

        {{-- Aggiungi ai contatti --}}
        <a class="kc-btn-save"
           href="{{ route('card.vcard', $onepage->slug) }}"
           aria-label="{{ $t['save'] }} — {{ $user->name }}">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="18" rx="2"/><path d="M8 10a2 2 0 104 0 2 2 0 00-4 0"/><path d="M4 20c0-2.21 1.79-4 4-4h4c2.21 0 4 1.79 4 4"/><path d="M16 8h2M16 12h2"/></svg>
            {{ $t['save'] }}
        </a>

        {{-- Contatti: valori cliccabili, nessuna label ──────────────── --}}
        @if($hasContacts)
        <div class="kc-contacts">

            @if($showPhone)
            <a class="kc-contact-row" href="tel:{{ $profile->phone }}">
                <span class="kc-ci kc-ci--phone">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.01 2.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                </span>
                {{ $profile->phone }}
            </a>
            @endif

            @if($showEmail)
            <a class="kc-contact-row" href="mailto:{{ $user->email }}">
                <span class="kc-ci kc-ci--email">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                {{ $user->email }}
            </a>
            @endif

            @if($showWebsite)
            <a class="kc-contact-row" href="{{ $profile->website }}" target="_blank" rel="noopener">
                <span class="kc-ci kc-ci--web">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                </span>
                {{ preg_replace('#^https?://(www\.)?#', '', rtrim($profile->website, '/')) }}
            </a>
            @endif

            @if($showCity)
            <a class="kc-contact-row" href="{{ $cityMapsUrl }}" target="_blank" rel="noopener">
                <span class="kc-ci kc-ci--city">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </span>
                {{ $profile->city->name }}
            </a>
            @endif

        </div>
        @endif

        {{-- Link al profilo su Kommunity --}}
        <a class="kc-profile-link"
           href="{{ route('members.show', $onepage->slug) }}"
           target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            {{ $t['view_on_komm'] }}
        </a>

        {{-- Condividi --}}
        <button class="kc-btn-share" id="kc-share-btn" type="button" aria-label="{{ $t['share'] }}">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            {{ $t['share'] }}
        </button>
        <p class="kc-copied" id="kc-copied-msg" role="status" aria-live="polite">✓ Link copiato</p>

        {{-- Social in fondo — cerchi neutri, nessuna label ─────── --}}
        @if($hasSocialFoot)
        <div class="kc-socials-foot" role="list" aria-label="Social">

            <a class="kc-sf"
               href="{{ route('members.show', $onepage->slug) }}"
               target="_blank" rel="noopener"
               aria-label="Profilo Kommunity"
               role="listitem">
                <img class="kc-sf-img" src="{{ asset('brand/kommunity-mark.png') }}" alt="" aria-hidden="true">
            </a>

            @if($showWhatsapp)
            <a class="kc-sf" href="{{ $whatsappUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
            </a>
            @endif

            @if($showLinkedin)
            <a class="kc-sf" href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" aria-label="LinkedIn" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
            </a>
            @endif

            @if($showInstagram)
            <a class="kc-sf" href="{{ $profile->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
            </a>
            @endif

            @if($showFacebook)
            <a class="kc-sf" href="{{ $profile->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook" role="listitem">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
            </a>
            @endif

        </div>
        @endif

    </div>{{-- /.kc-body --}}

    {{-- ════════ FOOTER ════════ --}}
    <div class="kc-footer">
        @auth
            @if(auth()->id() !== $user->id)
            <div class="kc-footer-logged">
                <p>{{ $t['already_on'] }} — {{ $t['connect_with'] }} {{ explode(' ', $user->name)[0] }}</p>
                <div class="kc-footer-btns">
                    <a class="kc-footer-btn kc-footer-btn--primary"
                       href="{{ route('conversations.start') }}"
                       onclick="event.preventDefault(); document.getElementById('kc-msg-form').submit();">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        {{ $t['write'] }}
                    </a>
                    <a class="kc-footer-btn kc-footer-btn--secondary"
                       href="{{ route('members.show', $onepage->slug) }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {{ $t['full_profile'] }}
                    </a>
                </div>
                <form id="kc-msg-form" method="POST" action="{{ route('conversations.start') }}" style="display:none">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                </form>
            </div>
            @else
            <div class="kc-footer-logged">
                <p>{{ $t['your_card'] }} — condividila per farti conoscere</p>
                <div class="kc-footer-btns">
                    <a class="kc-footer-btn kc-footer-btn--secondary"
                       href="{{ route('profile.edit') }}"
                       style="grid-column: 1 / -1">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        {{ $t['edit_profile'] }}
                    </a>
                </div>
            </div>
            @endif
        @else
            <div class="kc-footer-guest">
                <a href="{{ $referralUrl }}" target="_blank" rel="noopener">
                    {{ $t['cta_card'] }}
                </a>
            </div>
        @endauth
    </div>

</div>{{-- /.kc-wrap --}}

<script>
(function () {
    var btn   = document.getElementById('kc-share-btn');
    var msg   = document.getElementById('kc-copied-msg');
    var url   = '{{ $cardUrl }}';
    var title = '{{ addslashes($user->name) }}';
    var text  = '{{ addslashes($ogDescription) }}';
    if (!btn) return;
    btn.addEventListener('click', function () {
        if (navigator.share) {
            navigator.share({ title: title, text: text, url: url }).catch(function(){});
        } else {
            navigator.clipboard.writeText(url).then(function () {
                msg.style.display = 'block';
                setTimeout(function () { msg.style.display = 'none'; }, 2500);
            }).catch(function () { window.prompt('Copia il link:', url); });
        }
    });
}());
</script>

</body>
</html>
