<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }} — Kommunity</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg: #07111a; --bg2: #0d1e2b;
            --brand: #55794F; --brand3: #6fa367;
            --teal: #465D70; --teal2: #6e90a8;
            --text: #F0F6FA; --muted: #8FAAB8; --line: rgba(255,255,255,.08);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--bg); color: var(--text);
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }
        .km-wrap { width: min(100% - 2.5rem, 900px); margin-inline: auto; }

        /* Header */
        .site-header { position: sticky; top: 0; z-index: 80;
            background: rgba(7,17,26,.85); border-bottom: 1px solid rgba(255,255,255,.07);
            backdrop-filter: blur(22px); }
        .header-inner { display: flex; align-items: center; justify-content: space-between;
            gap: 2rem; padding: 1rem 0; }
        .brand-lockup { display: inline-flex; align-items: center; gap: .6rem;
            font-weight: 800; font-size: 1.15rem; letter-spacing: -.03em; color: var(--text); }
        .brand-mark { width: 2.1rem; height: 2.1rem; display: flex; align-items: center;
            justify-content: center; filter: drop-shadow(0 0 10px rgba(85,121,79,.35)); }
        .brand-mark img { width: 1.25rem; height: 2rem; object-fit: contain; display: block; }
        .btn { display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; border-radius: .45rem; padding: .7rem 1.2rem;
            font-size: .82rem; font-weight: 800; cursor: pointer; border: none;
            transition: transform .2s, box-shadow .2s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { color: #edf5ea;
            background: linear-gradient(135deg, var(--brand3) 0%, var(--brand) 100%);
            box-shadow: 0 12px 28px rgba(85,121,79,.28); }
        .btn-ghost { color: var(--text); background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.14); }

        /* Page content */
        .page-hero {
            padding: 4rem 0 2.5rem;
            border-bottom: 1px solid var(--line);
            background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(70,93,112,.18), transparent);
        }
        .page-hero h1 { font-size: clamp(2rem, 5vw, 3.8rem); font-weight: 800;
            letter-spacing: -.05em; line-height: 1.05; margin: 0; }
        .page-breadcrumb { font-size: .78rem; color: var(--muted); margin-bottom: 1.2rem; }
        .page-breadcrumb a { color: var(--brand3); }

        .page-body {
            padding: 3.5rem 0 5rem;
        }
        /* Rich content typography */
        .prose { max-width: 72ch; color: rgba(214,228,236,.92); line-height: 1.80; font-size: 1.02rem; }
        .prose h2 { font-size: 1.7rem; font-weight: 800; letter-spacing: -.04em;
            color: var(--text); margin: 2.5rem 0 .9rem; }
        .prose h3 { font-size: 1.2rem; font-weight: 800; letter-spacing: -.03em;
            color: var(--text); margin: 2rem 0 .7rem; }
        .prose p  { margin: 0 0 1.2rem; }
        .prose ul, .prose ol { padding-left: 1.5rem; margin: 0 0 1.2rem; }
        .prose li { margin-bottom: .45rem; }
        .prose a  { color: var(--brand3); text-decoration: underline; text-underline-offset: 3px; }
        .prose a:hover { color: var(--teal2); }
        .prose blockquote { margin: 1.5rem 0; padding: 1rem 1.5rem;
            border-left: 3px solid var(--brand3); background: rgba(85,121,79,.08);
            border-radius: 0 .5rem .5rem 0; color: rgba(214,228,236,.85); font-style: italic; }
        .prose strong { color: var(--text); font-weight: 700; }
        .prose hr { border: none; border-top: 1px solid var(--line); margin: 2rem 0; }
        .prose code { background: rgba(70,93,112,.22); border-radius: .3rem; padding: .15em .4em;
            font-size: .88em; color: var(--teal2); }

        /* Footer */
        .page-footer { background: #03111a; border-top: 1px solid var(--line);
            padding: 1.5rem 0; text-align: center; font-size: .76rem; color: var(--muted); }
        .page-footer a { color: var(--brand3); }
    </style>
</head>
<body>

@php
    $navPages    = \App\Models\Page::forNav();
    $footerPages = \App\Models\Page::forFooter();
@endphp

<header class="site-header">
    <div class="km-wrap header-inner">
        <a href="{{ route('home') }}" class="brand-lockup">
            <span class="brand-mark"><x-application-logo /></span>
            <span>Kommunity</span>
        </a>
        <div style="display:flex;align-items:center;gap:1rem">
            @foreach($navPages as $np)
                <a href="{{ route('page.show', $np->slug) }}"
                   style="font-size:.8rem;font-weight:700;color:rgba(240,246,250,.72);transition:color .2s"
                   onmouseover="this.style.color='#6fa367'" onmouseout="this.style.color='rgba(240,246,250,.72)'">
                    {{ $np->title }}
                </a>
            @endforeach
            <a href="{{ route('login') }}" class="btn btn-ghost" style="padding:.58rem .95rem;font-size:.78rem">Accedi</a>
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding:.58rem .95rem;font-size:.78rem">Accedi</a>
        </div>
    </div>
</header>

<main>
    <div class="page-hero">
        <div class="km-wrap">
            <p class="page-breadcrumb">
                <a href="{{ route('home') }}">Home</a> &rsaquo; {{ $page->title }}
            </p>
            <h1>{{ $page->title }}</h1>
        </div>
    </div>

    <div class="page-body">
        <div class="km-wrap">
            <div class="prose">
                {!! purify($page->content) !!}
            </div>
        </div>
    </div>
</main>

<footer class="page-footer">
    <div class="km-wrap">
        <p>
            © {{ date('Y') }} KNM Srl · P.IVA 13273091002
            @foreach($footerPages as $fp)
                &nbsp;·&nbsp;<a href="{{ route('page.show', $fp->slug) }}">{{ $fp->title }}</a>
            @endforeach
        </p>
    </div>
</footer>

</body>
</html>
