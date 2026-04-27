<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kommunity — La business community professionale</title>
    <meta name="description" content="Kommunity è l'ecosistema dove professionisti e aziende si connettono, collaborano e crescono insieme.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --home-bg: #020b12;
            --home-bg-2: #031822;
            --home-bg-3: #052532;
            --brand: #8bc53f;
            --brand-2: #6faf3a;
            --brand-3: #9ad84a;
            --teal: #1ca7a8;
            --teal-2: #2dd4bf;
            --text: #f8fafc;
            --muted: #aab7c4;
            --line: rgba(255,255,255,.10);
            --glass: rgba(255,255,255,.055);
            --glass-strong: rgba(255,255,255,.09);
        }

        html { scroll-behavior: smooth; overflow-x: hidden; }
        body {
            margin: 0;
            overflow-x: hidden;
            background: var(--home-bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .home-page {
            min-height: 100vh;
            background:
                radial-gradient(circle at 76% 6%, rgba(45, 212, 191, .15), transparent 27rem),
                radial-gradient(circle at 18% 13%, rgba(139, 197, 63, .12), transparent 28rem),
                linear-gradient(180deg, #020b12 0%, #031822 48%, #020b12 100%);
        }

        .home-container { width: min(100% - 2rem, 1360px); margin-inline: auto; }
        .section-pad { padding: 5.4rem 0; }
        .accent { color: var(--brand-3); }
        .muted { color: var(--muted); }
        .brand-logo svg path:first-child { stroke: #d7e0e8; }
        .brand-logo svg path:last-child { stroke: var(--brand); }

        .brand-lockup { display: inline-flex; align-items: center; gap: .6rem; color: var(--text); font-weight: 800; letter-spacing: -.03em; }
        .brand-mark { width: 2.15rem; height: 2.15rem; display: inline-flex; align-items: center; justify-content: center; }
        .brand-mark svg { width: 1.25rem; height: 2rem; filter: drop-shadow(0 0 14px rgba(139,197,63,.35)); }

        .glass {
            border: 1px solid var(--line);
            background: linear-gradient(145deg, rgba(255,255,255,.09), rgba(255,255,255,.035));
            box-shadow: 0 24px 70px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.08);
            backdrop-filter: blur(18px);
        }

        .btn {
            display: inline-flex;
            min-height: 2.9rem;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            border-radius: .45rem;
            padding: .78rem 1.25rem;
            font-size: .86rem;
            font-weight: 800;
            transition: transform .22s ease, border-color .22s ease, background .22s ease, box-shadow .22s ease;
            white-space: nowrap;
        }

        .btn-primary {
            color: #06100d;
            background: linear-gradient(135deg, var(--brand-3), var(--brand));
            box-shadow: 0 18px 34px rgba(139,197,63,.22), inset 0 1px 0 rgba(255,255,255,.32);
        }

        .btn-secondary {
            color: var(--text);
            border: 1px solid rgba(255,255,255,.16);
            background: rgba(255,255,255,.045);
        }

        .btn:hover { transform: translateY(-2px); }
        .btn-primary:hover { box-shadow: 0 22px 44px rgba(139,197,63,.32); }
        .btn-secondary:hover { border-color: rgba(45,212,191,.45); background: rgba(45,212,191,.08); }

        .nav-link { color: rgba(248,250,252,.78); font-size: .78rem; font-weight: 700; transition: color .2s ease; }
        .nav-link:hover { color: var(--brand-3); }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 60;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: rgba(2,11,18,.76);
            backdrop-filter: blur(22px);
        }

        .hero {
            position: relative;
            min-height: 760px;
            overflow: hidden;
            border-bottom: 1px solid rgba(45,212,191,.34);
        }

        .hero::before {
            content: "K";
            position: absolute;
            top: -7rem;
            right: 9%;
            font-size: clamp(28rem, 48vw, 48rem);
            line-height: 1;
            font-weight: 800;
            color: rgba(139,197,63,.075);
            text-shadow: 0 0 70px rgba(45,212,191,.18);
            pointer-events: none;
        }

        .network-bg {
            position: absolute;
            inset: 0;
            opacity: .42;
            background-image:
                radial-gradient(circle at 72% 27%, rgba(45,212,191,.58) 0 2px, transparent 3px),
                radial-gradient(circle at 83% 42%, rgba(139,197,63,.7) 0 2px, transparent 3px),
                radial-gradient(circle at 63% 57%, rgba(45,212,191,.5) 0 2px, transparent 3px),
                radial-gradient(circle at 91% 18%, rgba(154,216,74,.66) 0 2px, transparent 3px),
                linear-gradient(118deg, transparent 55%, rgba(45,212,191,.16) 55.2%, transparent 55.6%),
                linear-gradient(38deg, transparent 61%, rgba(139,197,63,.15) 61.2%, transparent 61.6%),
                linear-gradient(156deg, transparent 71%, rgba(255,255,255,.12) 71.2%, transparent 71.5%);
            pointer-events: none;
        }

        .hero-grid { position: relative; z-index: 2; display: grid; grid-template-columns: minmax(0, .92fr) minmax(420px, 1fr); gap: 4.5rem; align-items: center; padding: 5.5rem 0 5rem; }
        .hero h1, .section-title { font-size: clamp(2.85rem, 6vw, 5.7rem); line-height: .98; letter-spacing: -.055em; font-weight: 800; }
        .hero-copy { max-width: 660px; margin-top: 1.75rem; color: #d6e0e8; font-size: 1.09rem; line-height: 1.75; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 2.35rem; }

        .avatars { display: flex; align-items: center; margin-top: 2rem; }
        .avatar {
            width: 2.45rem; height: 2.45rem; border-radius: 999px; margin-left: -.55rem;
            border: 2px solid rgba(255,255,255,.35);
            background: linear-gradient(135deg, #d8f3dc, #7dd3fc 48%, #f8fafc);
            box-shadow: 0 10px 20px rgba(0,0,0,.22);
        }
        .avatar:first-child { margin-left: 0; }
        .avatar:nth-child(2n) { background: linear-gradient(135deg, #f8fafc, #a7f3d0 48%, #64748b); }
        .avatar:nth-child(3n) { background: linear-gradient(135deg, #e2e8f0, #2dd4bf 48%, #14532d); }
        .avatar-badge { display: inline-flex; align-items: center; justify-content: center; color: var(--text); font-size: .72rem; font-weight: 800; background: rgba(255,255,255,.08); }

        .dashboard-wrap { position: relative; min-height: 560px; }
        .dashboard-card { position: relative; z-index: 2; border-radius: 1.15rem; padding: 1.25rem; }
        .dash-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .dash-window { display: flex; gap: .35rem; }
        .dash-window span { width: .55rem; height: .55rem; border-radius: 999px; background: rgba(255,255,255,.24); }
        .metric-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .8rem; }
        .metric { border: 1px solid rgba(255,255,255,.09); border-radius: .8rem; padding: 1rem; background: rgba(2,11,18,.38); }
        .metric strong { display: block; color: var(--brand-3); font-size: 1.75rem; line-height: 1; }
        .metric span { display: block; margin-top: .45rem; color: var(--muted); font-size: .76rem; }
        .activity-list { margin-top: 1rem; display: grid; gap: .72rem; }
        .activity { display: flex; align-items: center; gap: .72rem; border: 1px solid rgba(255,255,255,.08); border-radius: .8rem; padding: .72rem; background: rgba(255,255,255,.035); }
        .activity-dot { width: .55rem; height: .55rem; border-radius: 999px; background: var(--brand); box-shadow: 0 0 16px rgba(139,197,63,.7); }
        .event-card { margin-top: 1rem; display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: center; border-radius: 1rem; padding: 1rem; background: linear-gradient(135deg, rgba(139,197,63,.16), rgba(28,167,168,.13)); border: 1px solid rgba(139,197,63,.25); }

        .floating-node {
            position: absolute;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: .75rem;
            width: 13.4rem;
            border-radius: .9rem;
            padding: .8rem;
            background: rgba(3,24,34,.72);
            border: 1px solid rgba(255,255,255,.12);
            box-shadow: 0 24px 55px rgba(0,0,0,.3);
            backdrop-filter: blur(14px);
        }
        .floating-node.one { top: 1.5rem; right: 0; }
        .floating-node.two { bottom: 2rem; left: -1rem; }
        .mini-face { width: 2.35rem; height: 2.35rem; border-radius: 999px; background: linear-gradient(135deg, #f8fafc, #2dd4bf 55%, #0f766e); border: 2px solid rgba(255,255,255,.24); }

        .step-section { border-top: 1px solid rgba(45,212,191,.38); border-bottom: 1px solid rgba(139,197,63,.34); background: linear-gradient(180deg, rgba(3,24,34,.7), rgba(2,11,18,.7)); }
        .steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
        .step { position: relative; padding: 1.1rem 1rem; }
        .step:not(:last-child)::after { content: "›"; position: absolute; right: -.35rem; top: 50%; color: var(--brand-3); font-size: 2.6rem; font-weight: 300; transform: translateY(-50%); }
        .step-icon, .card-icon {
            width: 2.9rem; height: 2.9rem; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 999px; color: var(--teal-2); border: 1px solid rgba(45,212,191,.42);
            background: rgba(45,212,191,.055); box-shadow: 0 0 28px rgba(45,212,191,.12);
        }
        .step-num { display: inline-flex; margin-left: .55rem; width: 2rem; height: 2rem; align-items: center; justify-content: center; border-radius: 999px; color: var(--brand-3); background: rgba(45,212,191,.14); font-size: .74rem; font-weight: 800; }

        .ecosystem-grid { display: grid; grid-template-columns: .82fr 1.18fr; gap: 4.5rem; align-items: center; }
        .feature-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .9rem; }
        .feature-card, .testimonial-card {
            border: 1px solid var(--line);
            border-radius: .65rem;
            padding: 1.25rem;
            background: linear-gradient(145deg, rgba(255,255,255,.07), rgba(255,255,255,.026));
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease, background .22s ease;
        }
        .feature-card:hover, .testimonial-card:hover {
            transform: translateY(-4px);
            border-color: rgba(45,212,191,.42);
            background: linear-gradient(145deg, rgba(45,212,191,.08), rgba(255,255,255,.035));
            box-shadow: 0 22px 54px rgba(45,212,191,.09);
        }

        .stats-band { padding: 2.2rem 0; background: radial-gradient(circle at 12% 50%, rgba(45,212,191,.12), transparent 18rem), linear-gradient(90deg, rgba(255,255,255,.035), rgba(255,255,255,.07), rgba(255,255,255,.035)); }
        .stats-card { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border-radius: 1rem; overflow: hidden; }
        .stat { padding: 1.65rem 1rem; text-align: center; border-right: 1px solid rgba(255,255,255,.11); }
        .stat:last-child { border-right: 0; }
        .stat strong { display: block; color: var(--brand-3); font-size: clamp(2.35rem, 4vw, 3.8rem); line-height: 1; letter-spacing: -.045em; }

        .split-grid { display: grid; grid-template-columns: .7fr 1.3fr; gap: 4rem; align-items: center; }
        .testimonial-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        .stars { color: var(--brand-3); letter-spacing: .12em; font-size: .92rem; }
        .person { display: flex; align-items: center; gap: .75rem; margin-top: 1.4rem; }

        .local-section { background: linear-gradient(145deg, rgba(5,37,50,.95), rgba(2,11,18,.9)); border-top: 1px solid rgba(255,255,255,.08); border-bottom: 1px solid rgba(255,255,255,.08); }
        .local-grid { display: grid; grid-template-columns: .85fr 1.15fr; gap: 4rem; align-items: center; }
        .search-fake { display: flex; align-items: center; justify-content: space-between; margin-top: 1.7rem; border: 1px solid rgba(255,255,255,.13); background: rgba(255,255,255,.055); border-radius: .55rem; padding: .85rem 1rem; color: #718293; }
        .city-list { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1rem; }
        .city-pill { border: 1px solid rgba(139,197,63,.36); border-radius: .45rem; padding: .58rem .9rem; color: #dce8f2; font-size: .78rem; background: rgba(255,255,255,.035); }
        .map-wrap { position: relative; min-height: 420px; display: flex; align-items: center; justify-content: center; }
        .italy-map { position: relative; width: min(440px, 86vw); height: 360px; filter: drop-shadow(0 28px 44px rgba(0,0,0,.34)); }
        .italy-shape { position: absolute; inset: 2rem 5rem 2rem 5rem; border-radius: 46% 54% 54% 46% / 18% 20% 80% 82%; transform: rotate(24deg); background: linear-gradient(145deg, rgba(45,212,191,.28), rgba(139,197,63,.18)); border: 1px solid rgba(45,212,191,.32); }
        .island { position: absolute; width: 3.4rem; height: 5.3rem; border-radius: 55% 45% 60% 40%; background: rgba(45,212,191,.20); border: 1px solid rgba(45,212,191,.26); }
        .sardinia { left: 5.3rem; bottom: 3.9rem; transform: rotate(13deg); }
        .sicily { right: 4.6rem; bottom: 1.8rem; width: 5.2rem; height: 2.35rem; transform: rotate(9deg); }
        .map-point { position: absolute; width: .72rem; height: .72rem; border-radius: 999px; background: var(--brand-3); box-shadow: 0 0 0 .35rem rgba(139,197,63,.13), 0 0 22px rgba(139,197,63,.9); }
        .city-card { position: absolute; right: 0; bottom: 2.1rem; width: 15.2rem; border-radius: .75rem; padding: .9rem; }

        .video-grid { display: grid; grid-template-columns: 1.02fr .98fr; gap: 3.5rem; align-items: center; }
        .video-card { position: relative; min-height: 300px; border-radius: .75rem; overflow: hidden; background: linear-gradient(135deg, rgba(248,250,252,.14), rgba(2,11,18,.9)), radial-gradient(circle at 35% 34%, rgba(45,212,191,.20), transparent 13rem); }
        .video-card::before { content: ""; position: absolute; inset: 0; background: linear-gradient(130deg, rgba(255,255,255,.08), transparent 45%), repeating-linear-gradient(90deg, transparent 0 34px, rgba(255,255,255,.025) 34px 35px); }
        .play { position: absolute; inset: 0; margin: auto; width: 5rem; height: 5rem; border-radius: 999px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,.85); background: rgba(255,255,255,.08); color: var(--text); }
        .play svg { margin-left: .25rem; }

        .final-cta { position: relative; overflow: hidden; border-top: 1px solid rgba(45,212,191,.28); border-bottom: 1px solid rgba(139,197,63,.38); background: radial-gradient(circle at 76% 100%, rgba(139,197,63,.32), transparent 25rem), linear-gradient(135deg, rgba(5,37,50,.95), rgba(2,11,18,.96)); }
        .final-cta::before { content: "K"; position: absolute; left: 5%; bottom: -7rem; color: rgba(139,197,63,.16); font-size: clamp(15rem, 24vw, 28rem); line-height: .8; font-weight: 800; text-shadow: 0 0 54px rgba(139,197,63,.25); }
        .cta-inner { position: relative; z-index: 1; display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: center; padding: 3.7rem 0; }

        .site-footer { background: #03131d; border-top: 1px solid rgba(255,255,255,.08); }
        .footer-grid { display: grid; grid-template-columns: 1.4fr .7fr .8fr .9fr 1fr; gap: 2rem; padding: 2.2rem 0; }
        .footer-title { color: #dce8f2; font-size: .78rem; font-weight: 800; margin-bottom: .9rem; }
        .footer-link { display: block; color: var(--muted); font-size: .78rem; margin-top: .55rem; transition: color .2s ease; }
        .footer-link:hover { color: var(--brand-3); }
        .newsletter { display: flex; gap: .45rem; border: 1px solid rgba(139,197,63,.38); border-radius: .55rem; padding: .34rem; background: rgba(255,255,255,.035); }
        .newsletter input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; color: var(--text); padding: .55rem .65rem; font-size: .78rem; }
        .newsletter button { width: 2.2rem; border-radius: .38rem; background: var(--brand); color: #06100d; font-weight: 900; }

        @media (max-width: 1180px) {
            .hero-grid, .ecosystem-grid, .local-grid, .video-grid { grid-template-columns: 1fr; }
            .dashboard-wrap { min-height: auto; }
            .floating-node { display: none; }
            .feature-grid, .testimonial-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .footer-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        @media (max-width: 820px) {
            .desktop-nav { display: none; }
            .header-actions .btn-secondary { display: none; }
            .hero { min-height: auto; }
            .hero-grid { gap: 2.4rem; padding: 3.6rem 0; }
            .section-pad { padding: 3.8rem 0; }
            .steps, .stats-card, .metric-grid { grid-template-columns: 1fr; }
            .step:not(:last-child)::after { display: none; }
            .split-grid { grid-template-columns: 1fr; gap: 2rem; }
            .feature-grid, .testimonial-grid, .footer-grid { grid-template-columns: 1fr; }
            .stat { border-right: 0; border-bottom: 1px solid rgba(255,255,255,.11); }
            .stat:last-child { border-bottom: 0; }
            .cta-inner { grid-template-columns: 1fr; }
            .btn { width: 100%; }
            .header-actions .btn { width: auto; padding-inline: .85rem; }
            .city-card { position: relative; right: auto; bottom: auto; margin-top: 1rem; width: auto; }
            .map-wrap { min-height: 340px; }
        }
    </style>
</head>
<body>
@php
    $navItems = ['Chi siamo', 'Come funziona', 'Community', 'Eventi', 'Blog', 'Contatti'];
    $steps = [
        ['01', 'Crea il tuo profilo', 'Mostra chi sei, cosa fai e quali sono i tuoi obiettivi.'],
        ['02', 'Entra nei capitoli', 'Unisciti alla community più vicina a te.'],
        ['03', 'Connettiti', 'Incontra professionisti e crea relazioni di valore.'],
        ['04', 'Genera opportunità', 'Collabora, condividi, fai crescere il tuo business.'],
    ];
    $features = [
        ['Directory professionisti', 'Trova e fatti trovare dai professionisti giusti.'],
        ['Mini sito personale', 'Presentati al meglio con il tuo spazio dedicato.'],
        ['Eventi e incontri', 'Partecipa a eventi esclusivi e networking dal vivo.'],
        ['Referral e opportunità', 'Scambia, segnala, collabora.'],
        ['Chat e connessioni', 'Comunica in modo diretto e senza filtri.'],
        ['News e contenuti', 'Resta aggiornato su trend, novità e best practice.'],
    ];
    $cities = ['Milano', 'Roma', 'Torino', 'Bologna', 'Firenze', 'Napoli', 'Verona', 'Padova', 'Bari', 'Catania'];
    $footerExplore = ['Chi siamo', 'Come funziona', 'Community', 'Eventi', 'Blog'];
    $footerResources = ['Guide', 'FAQ', 'News', 'Storie di successo', 'Lavora con noi'];
@endphp
<div class="home-page">
    <header class="site-header">
        <div class="home-container flex items-center justify-between gap-6 py-4">
            <a href="{{ route('home') }}" class="brand-lockup brand-logo" aria-label="Kommunity home">
                <span class="brand-mark"><x-application-logo /></span>
                <span class="text-xl">Kommunity</span>
            </a>
            <nav class="desktop-nav flex items-center gap-8">
                @foreach($navItems as $item)
                    <a href="#{{ \Illuminate\Support\Str::slug($item) }}" class="nav-link">{{ $item }}</a>
                @endforeach
            </nav>
            <div class="header-actions flex items-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-secondary">Accedi</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Entra nella community</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="network-bg"></div>
            <div class="home-container hero-grid">
                <div>
                    <h1>Le relazioni giuste<br><span class="accent">generano risultati reali.</span></h1>
                    <p class="hero-copy">Kommunity è l'ecosistema dove professionisti e aziende si connettono, collaborano e crescono insieme.</p>
                    <div class="hero-actions">
                        <a href="{{ route('register') }}" class="btn btn-primary">Entra nella community <span aria-hidden="true">→</span></a>
                        <a href="#come-funziona" class="btn btn-secondary">Scopri come funziona <span aria-hidden="true">▷</span></a>
                    </div>
                    <div class="mt-8">
                        <p class="text-sm font-semibold text-slate-300">Già oltre 500 professionisti connessi</p>
                        <div class="avatars" aria-hidden="true">
                            @for($i = 0; $i < 8; $i++)
                                <span class="avatar"></span>
                            @endfor
                            <span class="avatar avatar-badge">+500</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-wrap">
                    <div class="floating-node one">
                        <span class="mini-face"></span>
                        <span><strong class="block text-sm">Sara Verdi</strong><small class="muted">Growth Designer</small></span>
                    </div>
                    <div class="floating-node two">
                        <span class="mini-face"></span>
                        <span><strong class="block text-sm">Luca Rosi</strong><small class="muted">Finanziario</small></span>
                    </div>
                    <div class="dashboard-card glass">
                        <div class="dash-top">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[.18em] text-teal-200/80">Dashboard</p>
                                <h2 class="mt-2 text-2xl font-extrabold">Benvenuto Marco!</h2>
                            </div>
                            <div class="dash-window" aria-hidden="true"><span></span><span></span><span></span></div>
                        </div>

                        <div class="metric-grid">
                            <div class="metric"><strong>128</strong><span>Le tue connessioni</span></div>
                            <div class="metric"><strong>23</strong><span>Opportunità attive</span></div>
                            <div class="metric"><strong>356</strong><span>Visite al profilo</span></div>
                        </div>

                        <div class="activity-list">
                            @foreach(['Francesca ha richiesto una connessione', 'Nuova opportunità nel capitolo Milano', 'Alessandro ti ha inviato un referral'] as $activity)
                                <div class="activity">
                                    <span class="activity-dot"></span>
                                    <p class="text-sm text-slate-200">{{ $activity }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="event-card">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[.14em] text-teal-100/80">Prossimo evento</p>
                                <h3 class="mt-1 text-xl font-extrabold">Networking Day</h3>
                                <p class="mt-1 text-sm text-slate-300">Milano · 18:30 · 48 membri iscritti</p>
                            </div>
                            <a href="{{ route('register') }}" class="btn btn-primary">Partecipa</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="come-funziona" class="step-section section-pad">
            <div class="home-container">
                <div class="mb-10 text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[.22em] text-teal-200/70">Metodo Kommunity</p>
                    <h2 class="mt-3 text-4xl font-extrabold tracking-[-.04em] md:text-5xl">Come funziona Kommunity</h2>
                </div>
                <div class="steps">
                    @foreach($steps as [$num, $title, $copy])
                        <article class="step">
                            <div class="mb-5 flex items-center">
                                <span class="step-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v4m0 10v4M3 12h4m10 0h4M6.4 6.4l2.8 2.8m5.6 5.6 2.8 2.8m0-11.2-2.8 2.8m-5.6 5.6-2.8 2.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                </span>
                                <span class="step-num">{{ $num }}</span>
                            </div>
                            <h3 class="text-lg font-extrabold">{{ $title }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-400">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="community" class="section-pad">
            <div class="home-container ecosystem-grid">
                <div>
                    <h2 class="section-title text-[clamp(2.2rem,4vw,4rem)]">Non è un social.<br>È un sistema progettato<br>per <span class="accent">far crescere il tuo business.</span></h2>
                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-400">Kommunity unisce persone, competenze e opportunità in un ambiente sicuro, selezionato e orientato ai risultati.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary mt-8">Scopri l'ecosistema <span aria-hidden="true">→</span></a>
                </div>
                <div class="feature-grid">
                    @foreach($features as [$title, $copy])
                        <article class="feature-card">
                            <span class="card-icon mb-5">
                                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM7 21a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-6-4h2m-2 10h2m4-6v2M7 11v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                            </span>
                            <h3 class="font-extrabold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="stats-band">
            <div class="home-container">
                <div class="stats-card glass">
                    @foreach([['500+', 'Professionisti attivi'], ['120+', 'Collaborazioni generate'], ['20+', 'Capitoli attivi'], ['15+', 'Eventi ogni mese']] as [$value, $label])
                        <div class="stat">
                            <strong>{{ $value }}</strong>
                            <p class="mt-3 text-sm font-semibold text-slate-300">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-pad">
            <div class="home-container split-grid">
                <div>
                    <h2 class="text-4xl font-extrabold leading-tight tracking-[-.045em] md:text-5xl">Cosa dicono<br>i nostri membri</h2>
                    <p class="mt-5 text-slate-400">Professionisti e aziende che hanno già scelto di far parte di Kommunity.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary mt-7">Leggi tutte le testimonianze <span aria-hidden="true">→</span></a>
                </div>
                <div class="testimonial-grid">
                    @foreach([
                        ['Kommunity mi ha permesso di entrare in contatto con clienti e partner in modo autentico e produttivo.', 'Francesca R.', 'Consulente HR'],
                        ['Grazie ai capitoli ho trovato persone straordinarie con cui collaboro ogni giorno.', 'Alessandro T.', 'Imprenditore'],
                        ['Un ambiente positivo, professionale e dove le relazioni contano davvero.', 'Giulia M.', 'Marketing Manager'],
                    ] as [$quote, $name, $role])
                        <article class="testimonial-card">
                            <div class="stars">★★★★★</div>
                            <p class="mt-5 text-sm leading-7 text-slate-200">“{{ $quote }}”</p>
                            <div class="person">
                                <span class="avatar !m-0"></span>
                                <span><strong class="block text-sm">{{ $name }}</strong><small class="text-slate-400">{{ $role }}</small></span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="local-section section-pad">
            <div class="home-container local-grid">
                <div>
                    <h2 class="text-4xl font-extrabold tracking-[-.045em] md:text-5xl">Trova la tua community locale</h2>
                    <p class="mt-5 max-w-xl leading-7 text-slate-400">Siamo presenti in tutta Italia con capitoli attivi. Trova quello più vicino a te e inizia a connetterti.</p>
                    <div class="search-fake"><span>Cerca la tua città...</span><span aria-hidden="true">⌕</span></div>
                    <p class="mt-5 text-sm font-bold text-slate-300">Alcune delle nostre città attive</p>
                    <div class="city-list">
                        @foreach($cities as $city)
                            <span class="city-pill">{{ $city }}</span>
                        @endforeach
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-secondary mt-7">Vedi tutte le città</a>
                </div>
                <div class="map-wrap">
                    <div class="italy-map" aria-hidden="true">
                        <span class="italy-shape"></span>
                        <span class="island sardinia"></span>
                        <span class="island sicily"></span>
                        @foreach([[58,19], [63,27], [54,36], [60,47], [68,57], [73,67], [82,78], [36,67], [67,82], [79,43]] as [$x, $y])
                            <span class="map-point" style="left: {{ $x }}%; top: {{ $y }}%;"></span>
                        @endforeach
                    </div>
                    <div class="city-card glass">
                        <p class="text-xl font-extrabold">Milano</p>
                        <p class="mt-1 text-sm text-teal-200">Capitolo attivo</p>
                        <p class="mt-1 text-sm text-slate-300">48 membri</p>
                        <a href="{{ route('register') }}" class="mt-4 inline-flex text-sm font-extrabold text-[color:var(--brand-3)]">Scopri il capitolo →</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-pad">
            <div class="home-container video-grid">
                <div class="video-card glass">
                    <button type="button" class="play" aria-label="Guarda il video">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72c0 .8.9 1.27 1.56.82l10.04-6.86a1 1 0 0 0 0-1.64L9.56 4.32C8.9 3.87 8 4.34 8 5.14Z"/></svg>
                    </button>
                    <div class="brand-lockup brand-logo absolute bottom-6 left-6">
                        <span class="brand-mark"><x-application-logo /></span>
                        <span>Kommunity</span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[.22em] text-[color:var(--brand-3)]">Scopri Kommunity</p>
                    <h2 class="mt-4 text-4xl font-extrabold tracking-[-.045em] md:text-5xl">In 60 secondi</h2>
                    <p class="mt-5 max-w-xl leading-7 text-slate-400">Guarda il video e scopri come Kommunity può trasformare le tue relazioni in risultati concreti.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary mt-8">Guarda il video <span aria-hidden="true">▶</span></a>
                </div>
            </div>
        </section>

        <section class="final-cta">
            <div class="home-container cta-inner">
                <div>
                    <h2 class="text-4xl font-extrabold leading-tight tracking-[-.045em] md:text-5xl">Pronto a trasformare<br>le relazioni in risultati?</h2>
                    <p class="mt-5 max-w-xl leading-7 text-slate-300">Entra oggi in Kommunity e inizia a costruire il tuo futuro professionale.</p>
                </div>
                <a href="{{ route('register') }}" class="btn btn-primary px-8">Entra nella community <span aria-hidden="true">→</span></a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="home-container footer-grid">
            <div>
                <a href="{{ route('home') }}" class="brand-lockup brand-logo">
                    <span class="brand-mark"><x-application-logo /></span>
                    <span class="text-lg">Kommunity</span>
                </a>
                <p class="mt-4 max-w-xs text-sm leading-6 text-slate-400">Kommunity è l'ecosistema che connette professionisti e aziende per generare valore, collaborare e crescere insieme.</p>
            </div>
            <div>
                <p class="footer-title">Esplora</p>
                @foreach($footerExplore as $link)<a href="#" class="footer-link">{{ $link }}</a>@endforeach
            </div>
            <div>
                <p class="footer-title">Risorse</p>
                @foreach($footerResources as $link)<a href="#" class="footer-link">{{ $link }}</a>@endforeach
            </div>
            <div>
                <p class="footer-title">Contatti</p>
                <p class="footer-link">info@kommunity.it</p>
                <p class="footer-link">+39 123 456 7900</p>
                <p class="footer-link">Via Example 123<br>20121 Milano (MI)</p>
            </div>
            <div>
                <p class="footer-title">Resta aggiornato</p>
                <p class="mb-3 text-sm text-slate-400">Iscriviti alla newsletter</p>
                <form class="newsletter">
                    <input type="email" placeholder="La tua email" aria-label="Email newsletter">
                    <button type="button" aria-label="Iscriviti">→</button>
                </form>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="home-container flex flex-col justify-between gap-3 py-5 text-xs text-slate-500 sm:flex-row">
                <p>© 2026 Kommunity — Tutti i diritti riservati</p>
                <div class="flex gap-6"><a href="#" class="hover:text-slate-300">Privacy Policy</a><a href="#" class="hover:text-slate-300">Termini e condizioni</a></div>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
