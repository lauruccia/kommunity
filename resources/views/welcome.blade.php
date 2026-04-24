<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kommunity — La business community professionale</title>
    <meta name="description" content="Kommunity unisce directory, mini sito personale, eventi, one-to-one e forum dentro un'unica piattaforma per professionisti e imprenditori.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .welcome-hero-bg {
            background: linear-gradient(160deg, #1e2d38 0%, #253545 42%, #2a3d2a 100%);
        }
        .welcome-feature-icon {
            width: 3rem; height: 3rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: 0.875rem;
            background: linear-gradient(135deg, var(--km-accent) 0%, var(--km-accent-strong) 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(66,98,64,0.28);
            flex-shrink: 0;
        }
        .welcome-stat-num {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1;
            color: var(--km-accent);
        }
        .welcome-step-num {
            width: 2.5rem; height: 2.5rem;
            border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.875rem; font-weight: 700;
            background: linear-gradient(135deg, var(--km-accent) 0%, var(--km-accent-strong) 100%);
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(66,98,64,0.28);
        }
        .welcome-cta-bg {
            background: linear-gradient(135deg, #2a3d2a 0%, #35495a 100%);
        }
    </style>
</head>
<body class="font-sans antialiased" style="background:#f5f8f8;overflow-x:hidden;">

    {{-- ══════════════════════════════════════════════════════
         NAVBAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-50 border-b border-white/10 bg-white/[0.92] backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-[0.8rem] border border-stone-200 bg-white shadow-sm">
                    <x-application-logo class="h-6 w-6" />
                </div>
                <span class="text-base font-semibold tracking-tight text-stone-950">Kommunity</span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('directory.index') }}" class="hidden rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-50 sm:block">Directory</a>
                    <a href="{{ route('dashboard') }}" class="km-button-primary px-5 py-2.5 text-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-50">Accedi</a>
                    <a href="{{ route('register') }}" class="km-button-primary px-5 py-2.5 text-sm">Richiedi accesso</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         HERO
    ══════════════════════════════════════════════════════ --}}
    <section class="welcome-hero-bg relative overflow-hidden pb-24 pt-16 sm:pb-32 sm:pt-20 lg:pb-40 lg:pt-28">
        {{-- decorazione --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -right-24 -top-24 h-[36rem] w-[36rem] rounded-full" style="background:radial-gradient(circle,rgba(85,121,79,0.22),transparent 70%);"></div>
            <div class="absolute -left-16 bottom-0 h-[28rem] w-[28rem] rounded-full" style="background:radial-gradient(circle,rgba(70,93,112,0.28),transparent 68%);"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div class="space-y-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/80 backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Community professionale italiana
                    </div>

                    <h1 class="text-4xl font-extrabold leading-[1.06] tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Dove le relazioni<br>
                        <span style="color:#7ec97a;">diventano</span><br>
                        opportunità reali
                    </h1>

                    <p class="max-w-lg text-base leading-8 text-white/70 sm:text-lg">
                        Kommunity unisce directory business, mini sito personale, eventi, agenda one-to-one, forum e messaggistica in un'unica piattaforma pensata per professionisti e imprenditori italiani.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('directory.index') }}" class="km-button-primary px-7 py-3.5">Apri la directory</a>
                            <a href="{{ route('dashboard') }}" class="rounded-full border border-white/20 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/18">Dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="km-button-primary px-7 py-3.5">Entra in Kommunity</a>
                            <a href="{{ route('login') }}" class="rounded-full border border-white/20 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/18">Ho già un account</a>
                        @endauth
                    </div>

                    {{-- trust badge --}}
                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                            <span class="text-sm text-white/60">Profilo personale incluso</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                            <span class="text-sm text-white/60">Videopresentazione integrata</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                            <span class="text-sm text-white/60">Capitoli territoriali</span>
                        </div>
                    </div>
                </div>

                {{-- Card preview --}}
                <div class="relative hidden lg:block">
                    <div class="absolute inset-0 -z-10 rounded-[2.5rem]" style="background:rgba(255,255,255,0.06);backdrop-filter:blur(10px);"></div>
                    <div class="space-y-4 rounded-[2.5rem] border border-white/10 bg-white/[0.07] p-8 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/40">Anteprima directory</p>
                        {{-- fake card --}}
                        @for ($i = 0; $i < 3; $i++)
                        <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.09] p-4 backdrop-blur">
                            <div class="h-12 w-12 flex-shrink-0 rounded-full bg-gradient-to-br from-emerald-400/60 to-teal-500/60"></div>
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <div class="h-3 w-2/3 rounded-full bg-white/25"></div>
                                <div class="h-2 w-1/2 rounded-full bg-white/15"></div>
                                <div class="h-2 w-3/4 rounded-full bg-white/10"></div>
                            </div>
                            <div class="h-8 w-8 flex-shrink-0 rounded-full bg-emerald-500/30"></div>
                        </div>
                        @endfor
                        <div class="pt-2 text-center text-xs text-white/30">e molti altri professionisti...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════
         STATS BAR
    ══════════════════════════════════════════════════════ --}}
    <section class="border-b border-stone-200 bg-white py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
                <div class="text-center">
                    <div class="welcome-stat-num">1:1</div>
                    <p class="mt-1 text-sm font-medium text-stone-500">Agenda relazionale</p>
                </div>
                <div class="text-center">
                    <div class="welcome-stat-num">∞</div>
                    <p class="mt-1 text-sm font-medium text-stone-500">Contatti diretti</p>
                </div>
                <div class="text-center">
                    <div class="welcome-stat-num">360°</div>
                    <p class="mt-1 text-sm font-medium text-stone-500">Visibilità professionale</p>
                </div>
                <div class="text-center">
                    <div class="welcome-stat-num" style="font-size:2rem;">IT</div>
                    <p class="mt-1 text-sm font-medium text-stone-500">Made in Italy</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════
         FEATURES
    ══════════════════════════════════════════════════════ --}}
    <section class="py-20 sm:py-28" style="background:#f5f8f8;">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="km-chip">Tutto incluso</span>
                <h2 class="mt-5 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
                    Una piattaforma completa<br>per la tua rete professionale
                </h2>
                <p class="mt-5 text-base leading-8 text-stone-500">
                    Ogni strumento è pensato per creare valore reale tra i membri: visibilità, relazioni, opportunità.
                </p>
            </div>

            <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Directory --}}
                <div class="km-panel p-7">
                    <div class="welcome-feature-icon mb-5">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H1zm13.5-9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-11 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-950">Directory business</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-500">Ricerca per categoria, città, settore e testo libero. Ogni membro è visibile con foto, video, contatti e professione.</p>
                </div>

                {{-- Onepage --}}
                <div class="km-panel p-7">
                    <div class="welcome-feature-icon mb-5">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-950">Mini sito personale</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-500">Ogni iscritto ottiene una pagina personale con gallery, bio, video di presentazione e link al proprio sito.</p>
                </div>

                {{-- One-to-one --}}
                <div class="km-panel p-7">
                    <div class="welcome-feature-icon mb-5">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-950">Agenda one-to-one</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-500">Proponi e gestisci incontri professionali con gli altri membri. Condividi le tue disponibilità e organizza il networking.</p>
                </div>

                {{-- Eventi --}}
                <div class="km-panel p-7">
                    <div class="welcome-feature-icon mb-5">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-950">Eventi e incontri</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-500">Calendario eventi della community, con registrazione online. Partecipa a workshop, aperitivi di networking e formazione.</p>
                </div>

                {{-- Forum --}}
                <div class="km-panel p-7">
                    <div class="welcome-feature-icon mb-5">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/><path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-950">Forum della community</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-500">Discuti, condividi esperienze, proponi collaborazioni e rimani aggiornato sulle novità del settore con gli altri membri.</p>
                </div>

                {{-- Messaggistica --}}
                <div class="km-panel p-7">
                    <div class="welcome-feature-icon mb-5">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.925A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.897 28.897 0 0015.293-7.155.75.75 0 000-1.115A28.897 28.897 0 003.105 2.289z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-stone-950">Messaggistica diretta</h3>
                    <p class="mt-3 text-sm leading-7 text-stone-500">Contatta direttamente gli altri membri con la chat integrata. Costruisci relazioni private e collabora in modo riservato.</p>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════
         COME FUNZIONA
    ══════════════════════════════════════════════════════ --}}
    <section class="border-t border-stone-200 bg-white py-20 sm:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-16 lg:grid-cols-2 lg:items-center">
                <div>
                    <span class="km-chip">Come funziona</span>
                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
                        Tre passi per entrare<br>nella community
                    </h2>
                    <p class="mt-5 text-base leading-8 text-stone-500">
                        Kommunity è una piattaforma ad accesso su invito o richiesta. Ogni membro viene verificato per garantire la qualità della rete.
                    </p>

                    <div class="mt-10 space-y-8">
                        <div class="flex items-start gap-5">
                            <div class="welcome-step-num mt-0.5">1</div>
                            <div>
                                <h3 class="font-semibold text-stone-950">Richiedi l'accesso</h3>
                                <p class="mt-1.5 text-sm leading-7 text-stone-500">Compila il form di registrazione indicando la tua attività professionale. Riceverai risposta entro 48 ore.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-5">
                            <div class="welcome-step-num mt-0.5">2</div>
                            <div>
                                <h3 class="font-semibold text-stone-950">Completa il profilo</h3>
                                <p class="mt-1.5 text-sm leading-7 text-stone-500">Aggiungi foto, videopresentazione, categorie, contatti e la tua storia professionale. In pochi minuti sei visibile nella directory.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-5">
                            <div class="welcome-step-num mt-0.5">3</div>
                            <div>
                                <h3 class="font-semibold text-stone-950">Fai networking</h3>
                                <p class="mt-1.5 text-sm leading-7 text-stone-500">Esplora la directory, proponi one-to-one, partecipa agli eventi e partecipa attivamente al forum della community.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="km-panel overflow-hidden p-0 shadow-[0_32px_80px_rgba(28,39,51,0.1)]">
                    <div class="bg-[linear-gradient(135deg,#253545_0%,#2a3d2a_100%)] px-8 py-10 text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/50">Perché Kommunity</p>
                        <h3 class="mt-3 text-2xl font-bold leading-snug">Non solo una rubrica, ma un ecosistema di relazioni</h3>
                    </div>
                    <div class="divide-y divide-stone-100 px-8 py-2">
                        @foreach([
                            ['Profilo professionale completo con video', 'La tua presentazione sempre aggiornata'],
                            ['Capitoli territoriali ("Pianeti")', 'Connettiti con chi è nella tua area'],
                            ['Referenze tra membri', 'Sistema di passaparola strutturato'],
                            ['Gestione abbonamenti flessibile', 'Piani mensili e annuali con prova gratuita'],
                        ] as [$title, $sub])
                        <div class="flex items-center gap-4 py-4">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-50">
                                <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-stone-950">{{ $title }}</p>
                                <p class="text-xs text-stone-500">{{ $sub }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════
         CTA FINALE
    ══════════════════════════════════════════════════════ --}}
    <section class="welcome-cta-bg py-20 sm:py-28">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
            <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                Pronto a far crescere<br>la tua rete professionale?
            </h2>
            <p class="mt-5 text-base leading-8 text-white/65">
                Unisciti a professionisti e imprenditori che scelgono Kommunity per costruire relazioni che creano valore.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                @auth
                    <a href="{{ route('directory.index') }}" class="km-button-primary px-8 py-4 text-base">Apri la directory</a>
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-white/20 bg-white/10 px-8 py-4 text-base font-semibold text-white backdrop-blur transition hover:bg-white/18">Vai alla dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="km-button-primary px-8 py-4 text-base">Richiedi accesso gratuito</a>
                    <a href="{{ route('login') }}" class="rounded-full border border-white/20 bg-white/10 px-8 py-4 text-base font-semibold text-white backdrop-blur transition hover:bg-white/18">Accedi al tuo account</a>
                @endauth
            </div>
            <p class="mt-6 text-xs text-white/35">Accesso su approvazione. I tuoi dati restano riservati alla community.</p>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="border-t border-stone-200 bg-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-stone-200 bg-white shadow-sm">
                        <x-application-logo class="h-5 w-5" />
                    </div>
                    <span class="text-sm font-semibold text-stone-950">Kommunity</span>
                </div>
                <p class="text-xs text-stone-400">&copy; {{ date('Y') }} Kommunity. Community professionale italiana.</p>
                <div class="flex gap-5">
                    <a href="{{ route('login') }}" class="text-xs text-stone-400 transition hover:text-stone-700">Accedi</a>
                    <a href="{{ route('register') }}" class="text-xs text-stone-400 transition hover:text-stone-700">Registrati</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
