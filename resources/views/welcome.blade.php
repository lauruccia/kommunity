<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Kommunity') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <div class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-[42rem] bg-[radial-gradient(circle_at_top_left,_rgba(70,93,112,0.18),_transparent_38%)]"></div>
            <div class="absolute right-0 top-20 -z-10 h-[28rem] w-[28rem] rounded-full bg-[radial-gradient(circle,_rgba(85,121,79,0.18),_transparent_64%)]"></div>
            <div class="km-shell py-6">
                <div class="flex flex-col gap-4 rounded-[2rem] border border-white/60 bg-white/[0.78] px-4 py-4 shadow-[0_18px_42px_rgba(28,39,51,0.08)] backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:rounded-full sm:py-3">
                    <div class="km-brand-lockup">
                        <div class="km-brand-mark km-brand-mark-sm">
                            <x-application-logo />
                        </div>
                        <div class="min-w-0">
                            <div class="text-lg font-semibold tracking-tight text-stone-950 sm:text-xl">Kommunity</div>
                            <div class="km-brand-kicker">Community professionale</div>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:flex sm:w-auto sm:items-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="km-button-secondary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="km-button-secondary">Accedi</a>
                            <a href="{{ route('register') }}" class="km-button-primary">Richiedi accesso</a>
                        @endauth
                    </div>
                </div>
            </div>

            <section class="km-shell pb-12 pt-8 lg:pb-20 lg:pt-12">
                <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                    <div class="space-y-8">
                        <span class="km-chip">Networker, imprenditori, professionisti</span>
                        <div class="space-y-5">
                            <h1 class="text-3xl font-semibold leading-[1.08] text-stone-950 sm:text-4xl lg:text-5xl xl:text-6xl">
                                La business community dove relazioni, visibilita' e opportunita' diventano sistema.
                            </h1>
                            <p class="text-sm leading-7 text-stone-500 sm:text-base lg:text-lg lg:leading-8">
                                Kommunity unisce directory interna, mini sito personale, capitoli territoriali, eventi, agenda one-to-one e collaborazione tra membri dentro un'unica piattaforma professionale.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('directory.index') }}" class="km-button-primary">Apri la directory</a>
                                <a href="{{ route('dashboard') }}" class="km-button-secondary">Vai alla dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="km-button-primary">Entra in Kommunity</a>
                                <a href="{{ route('login') }}" class="km-button-secondary">Ho gia' un account</a>
                            @endauth
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="km-panel p-5">
                                <div class="text-2xl font-semibold text-stone-950">1:1</div>
                                <p class="mt-2 text-sm text-stone-500">Agenda relazionale per fissare incontri professionali con disponibilita' condivise.</p>
                            </div>
                            <div class="km-panel p-5">
                                <div class="text-2xl font-semibold text-stone-950">Directory</div>
                                <p class="mt-2 text-sm text-stone-500">Ricerca per categoria, citta', settore e testo libero con ordine casuale dei membri.</p>
                            </div>
                            <div class="km-panel p-5">
                                <div class="text-2xl font-semibold text-stone-950">Onepage</div>
                                <p class="mt-2 text-sm text-stone-500">Mini sito professionale generato automaticamente per ogni iscritto.</p>
                            </div>
                        </div>
                    </div>

                    <div class="km-panel relative overflow-hidden border-white/60 bg-[linear-gradient(180deg,rgba(255,255,255,0.9)_0%,rgba(237,243,243,0.94)_100%)] p-6 lg:p-8">
                        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-[rgba(85,121,79,0.18)] blur-3xl"></div>
                        <div class="absolute -left-10 bottom-0 h-32 w-32 rounded-full bg-[rgba(70,93,112,0.14)] blur-3xl"></div>
                        <div class="space-y-6">
                            <div class="inline-flex rounded-full border border-[rgba(70,93,112,0.14)] bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[color:var(--km-deep-strong)]">
                                Ecosistema attivo
                            </div>
                            <div>
                                <p class="text-sm uppercase tracking-[0.22em] text-stone-500">Focus del progetto</p>
                                <h2 class="mt-3 text-2xl font-semibold text-stone-950 leading-snug">Relazioni di business, visibilita' professionale e community organizzata</h2>
                            </div>
                            <ul class="space-y-3 text-sm leading-7 text-stone-600">
                                <li>Ogni membro ha una pagina personale collegata al proprio profilo business.</li>
                                <li>La directory interna consente ricerca, filtri e contatto rapido tra iscritti.</li>
                                <li>Eventi, forum, one-to-one e messaggistica mantengono viva l'interazione nella community.</li>
                                <li>Il backoffice consente a super admin e admin community di governare la piattaforma.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>
