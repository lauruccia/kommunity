<x-app-layout>
    <style>
        :root{
            --km-dark:#020b12;
            --km-dark-2:#031822;
            --km-dark-3:#052532;
            --km-green:#8BC53F;
            --km-green-2:#9AD84A;
            --km-teal:#2DD4BF;
            --km-text:#F8FAFC;
            --km-muted:#AAB7C4;
            --km-line:rgba(255,255,255,.12);
            --km-glass:rgba(255,255,255,.075);
        }

        body{
            background:
                radial-gradient(circle at 80% 0%, rgba(139,197,63,.18), transparent 30%),
                radial-gradient(circle at 10% 25%, rgba(45,212,191,.12), transparent 35%),
                linear-gradient(135deg,var(--km-dark),var(--km-dark-2) 48%,#06111a)!important;
        }

        .km-shell{
            width:min(1480px,calc(100% - 48px));
            margin:0 auto;
        }

        .km-dark-panel{
            position:relative;
            overflow:hidden;
            border:1px solid var(--km-line);
            background:linear-gradient(135deg,rgba(255,255,255,.10),rgba(255,255,255,.045));
            box-shadow:0 24px 80px rgba(0,0,0,.35);
            backdrop-filter:blur(18px);
            border-radius:2rem;
            color:var(--km-text);
        }

        .km-dark-card{
            border:1px solid var(--km-line);
            background:linear-gradient(135deg,rgba(255,255,255,.085),rgba(255,255,255,.035));
            box-shadow:0 18px 60px rgba(0,0,0,.22);
            backdrop-filter:blur(18px);
            border-radius:1.8rem;
            color:var(--km-text);
            transition:.22s ease;
        }

        .km-dark-card:hover{
            transform:translateY(-3px);
            border-color:rgba(139,197,63,.35);
            box-shadow:0 24px 70px rgba(0,0,0,.32),0 0 34px rgba(139,197,63,.08);
        }

        .km-eyebrow{
            color:var(--km-green-2);
            font-size:.72rem;
            letter-spacing:.28em;
            text-transform:uppercase;
            font-weight:800;
        }

        .km-muted{
            color:var(--km-muted);
        }

        .km-glass-box{
            border:1px solid rgba(255,255,255,.10);
            background:rgba(255,255,255,.055);
            border-radius:1.4rem;
        }

        .km-hero::before{
            content:"K";
            position:absolute;
            right:12%;
            top:-120px;
            font-size:430px;
            font-weight:900;
            line-height:1;
            transform:skewX(-8deg);
            color:rgba(255,255,255,.045);
            pointer-events:none;
        }

        .km-hero::after{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 78% 32%, var(--km-green) 0 4px, transparent 5px),
                radial-gradient(circle at 87% 62%, var(--km-teal) 0 3px, transparent 4px),
                radial-gradient(circle at 67% 76%, var(--km-green-2) 0 3px, transparent 4px),
                repeating-radial-gradient(circle at 79% 48%, transparent 0 50px, rgba(139,197,63,.10) 51px 52px, transparent 54px 100px);
            opacity:.75;
            pointer-events:none;
        }

        .km-orbit{
            background:
                radial-gradient(circle at 50% 50%,rgba(139,197,63,.32),transparent 7%),
                radial-gradient(circle at 28% 34%,rgba(45,212,191,.35),transparent 6%),
                radial-gradient(circle at 73% 68%,rgba(139,197,63,.35),transparent 6%),
                repeating-radial-gradient(circle at 50% 50%,transparent 0 28px,rgba(139,197,63,.13) 30px 31px,transparent 33px 62px);
        }

        .km-button-primary{
            background:linear-gradient(135deg,var(--km-green),#5f9d42)!important;
            color:#061018!important;
            border:0!important;
            box-shadow:0 16px 42px rgba(139,197,63,.22);
        }

        .km-button-secondary{
            background:rgba(255,255,255,.08)!important;
            color:var(--km-text)!important;
            border:1px solid rgba(255,255,255,.14)!important;
        }

        .km-button-primary,
        .km-button-secondary{
            border-radius:1rem!important;
            min-height:52px;
            padding:.9rem 1.25rem!important;
            font-weight:800!important;
            transition:.22s ease;
        }

        .km-button-primary:hover,
        .km-button-secondary:hover{
            transform:translateY(-2px);
        }
    </style>

    <x-slot name="header">
        <div class="km-dark-panel km-hero p-7 lg:p-9">
            <div class="relative z-10 grid gap-8 lg:grid-cols-[1.25fr_0.75fr] lg:items-center">
                <div>
                    <p class="km-eyebrow">Dashboard membro · Pianeta Roma</p>

                    <h1 class="mt-4 max-w-5xl text-4xl font-black leading-[1.03] tracking-[-0.055em] text-white lg:text-6xl">
                        Gestisci presenza, relazioni e attività nel tuo
                        <span class="text-[color:var(--km-green)]">Pianeta professionale</span>
                    </h1>

                    <p class="km-muted mt-5 max-w-4xl text-base leading-8 lg:text-lg">
                        Profilo business, pagina personale, incontri one-to-one, eventi, forum e messaggi sono concentrati in un’unica area operativa per Roma e Lazio.
                    </p>

                    <div class="mt-7 grid gap-3 sm:flex sm:flex-wrap">
                        <a href="{{ route('profile.edit') }}" class="km-button-secondary w-full text-center sm:w-auto">
                            Completa onboarding
                        </a>

                        @if(optional($user->memberOnepage)->slug)
                            <a href="{{ route('members.show', $user->memberOnepage->slug) }}" class="km-button-primary w-full text-center sm:w-auto">
                                Apri pagina personale →
                            </a>
                        @else
                            <span class="km-button-primary w-full cursor-not-allowed text-center opacity-60 sm:w-auto">
                                Pagina personale non disponibile
                            </span>
                        @endif
                    </div>
                </div>

                <div class="km-dark-card relative z-10 p-6">
                    <p class="km-eyebrow">Zona Roma / Lazio</p>
                    <h2 class="mt-3 text-2xl font-bold text-white">Pianeta Roma</h2>
                    <p class="km-muted mt-2 text-sm leading-6">
                        Il tuo centro di controllo per connessioni, opportunità e visibilità locale.
                    </p>

                    <div class="km-orbit mt-5 h-40 rounded-[1.5rem] border border-white/10"></div>

                    <div class="mt-5 inline-flex rounded-full bg-[color:var(--km-green)] px-4 py-2 text-sm font-black text-[#071018]">
                        ● Professionista verificato
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell space-y-6">

            <section class="grid gap-5 lg:grid-cols-4">
                <article class="km-dark-card p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-[rgba(139,197,63,.12)] text-xl text-[color:var(--km-green)]">◎</div>
                    <p class="km-eyebrow">Onboarding</p>
                    <p class="mt-3 text-3xl font-black text-white">
                        {{ optional($user->memberProfile)->onboarding_completed ? '100%' : 'in corso' }}
                    </p>
                    <p class="km-muted mt-2 text-sm leading-6">
                        {{ optional($user->memberProfile)->onboarding_completed ? 'Profilo pronto per la directory.' : 'Completa i dati business e le preferenze di networking.' }}
                    </p>
                </article>

                <article class="km-dark-card p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-[rgba(139,197,63,.12)] text-xl text-[color:var(--km-green)]">↔</div>
                    <p class="km-eyebrow">One-to-one ricevuti</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ $receivedOneToOnes->count() }}</p>
                    <p class="km-muted mt-2 text-sm leading-6">Richieste recenti da membri del Pianeta Roma.</p>
                </article>

                <article class="km-dark-card p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-[rgba(139,197,63,.12)] text-xl text-[color:var(--km-green)]">◷</div>
                    <p class="km-eyebrow">Eventi in arrivo</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ $upcomingEvents->count() }}</p>
                    <p class="km-muted mt-2 text-sm leading-6">Appuntamenti pubblicati e aperti alla registrazione.</p>
                </article>

                <article class="km-dark-card p-5">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-[rgba(139,197,63,.12)] text-xl text-[color:var(--km-green)]">✉</div>
                    <p class="km-eyebrow">Messaggi recenti</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ $recentMessages->count() }}</p>
                    <p class="km-muted mt-2 text-sm leading-6">Conversazioni attive con altri membri.</p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-6">
                    <div class="km-dark-card p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="km-eyebrow">Profilo business</p>
                                <h2 class="mt-2 text-3xl font-black text-white">{{ $user->name ?? 'Utente' }}</h2>
                                <p class="km-muted mt-1 text-sm">
                                    {{ optional($user->memberProfile)->company_name ?: 'Azienda da inserire' }} · Pianeta Roma
                                </p>
                            </div>

                            <div class="flex h-20 w-20 items-center justify-center rounded-[1.7rem] border border-[rgba(139,197,63,.25)] bg-gradient-to-br from-[#0b1219] to-[#1e3128] text-4xl font-black text-white shadow-[0_0_40px_rgba(139,197,63,.22)]">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="km-glass-box p-4">
                                <p class="km-eyebrow">Categoria</p>
                                <p class="mt-3 text-sm font-bold text-white">
                                    {{ optional(optional($user->memberProfile)->category)->name ?? 'Da definire' }}
                                </p>
                            </div>

                            <div class="km-glass-box p-4">
                                <p class="km-eyebrow">Città</p>
                                <p class="mt-3 text-sm font-bold text-white">Roma</p>
                            </div>

                            <div class="km-glass-box p-4">
                                <p class="km-eyebrow">Zona</p>
                                <p class="mt-3 text-sm font-bold text-white">Roma / Lazio</p>
                            </div>

                            <div class="km-glass-box p-4">
                                <p class="km-eyebrow">Visibile in directory</p>
                                <p class="mt-3 text-sm font-bold text-white">
                                    {{ optional($user->memberProfile)->is_visible_in_directory ? 'Sì' : 'No' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="km-glass-box p-4">
                                <p class="km-eyebrow">Contatto preferito</p>
                                <p class="mt-3 text-sm font-bold text-white">
                                    {{ optional(optional($user->memberProfile)->preferred_contact_method)->label() ?? 'Email' }}
                                </p>
                            </div>

                            <div class="km-glass-box p-4">
                                <p class="km-eyebrow">Pianeta</p>
                                <p class="mt-3 text-sm font-bold text-[color:var(--km-green)]">Pianeta Roma</p>
                            </div>
                        </div>
                    </div>

                    <div class="km-dark-card p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="km-eyebrow">One-to-one ricevuti</p>
                                <h2 class="mt-2 text-2xl font-black text-white">Ultime richieste</h2>
                            </div>
                            <a href="{{ route('one-to-ones.index') }}" class="text-sm font-black text-[color:var(--km-green)]">Gestisci</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($receivedOneToOnes as $requestItem)
                                <div class="rounded-[1.4rem] border border-white/10 bg-white/[.045] p-4">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                        <div>
                                            <p class="text-sm font-bold text-white">
                                                {{ $requestItem->requester?->name ?? 'Utente non disponibile' }}
                                            </p>
                                            <p class="km-muted text-xs uppercase tracking-[0.16em]">
                                                {{ $requestItem->meeting_mode === 'online' ? 'Online' : 'In presenza' }}
                                                &middot;
                                                {{ $requestItem->status?->label() ?? 'Stato non disponibile' }}
                                            </p>
                                        </div>
                                        <div class="text-right text-xs text-[color:var(--km-muted)]">
                                            {{ optional($requestItem->requested_at)->format('d/m H:i') ?? 'Data non disponibile' }}
                                        </div>
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-white/80">
                                        {{ $requestItem->goal ?? 'Valutare partnership e collaborazioni nel Pianeta Roma.' }}
                                    </p>
                                </div>
                            @empty
                                <p class="km-muted text-sm">Nessuna richiesta ricevuta per ora nel Pianeta Roma.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="km-dark-card p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="km-eyebrow">Eventi</p>
                                <h2 class="mt-2 text-2xl font-black text-white">Prossimi appuntamenti</h2>
                            </div>
                            <a href="{{ route('events.index') }}" class="text-sm font-black text-[color:var(--km-green)]">Vedi tutti</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($upcomingEvents as $event)
                                <a href="{{ route('events.show', $event) }}" class="block rounded-[1.4rem] border border-white/10 bg-white/[.045] p-4 transition hover:border-[rgba(139,197,63,.35)]">
                                    <p class="text-sm font-bold text-white">{{ $event->title ?? 'Evento senza titolo' }}</p>
                                    <p class="km-muted mt-1 text-sm">{{ $event->location ?: 'Roma / Online' }}</p>
                                    <p class="km-muted mt-2 text-xs uppercase tracking-[0.16em]">
                                        {{ optional($event->starts_at)->format('d M Y - H:i') ?? 'Data da definire' }}
                                    </p>
                                </a>
                            @empty
                                <p class="km-muted text-sm">Nessun evento in arrivo nel Pianeta Roma.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="km-dark-card p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="km-eyebrow">Forum</p>
                                <h2 class="mt-2 text-2xl font-black text-white">Discussioni attive</h2>
                            </div>
                            <a href="{{ route('forum.index') }}" class="text-sm font-black text-[color:var(--km-green)]">Apri forum</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($latestThreads as $thread)
                                <a href="{{ route('forum.show', $thread) }}" class="block rounded-[1.4rem] border border-white/10 bg-white/[.045] p-4 transition hover:border-[rgba(139,197,63,.35)]">
                                    <p class="text-sm font-bold text-white">{{ $thread->title ?? 'Discussione senza titolo' }}</p>
                                    <p class="km-muted mt-1 text-sm">
                                        {{ $thread->category?->name ?? 'Pianeta Roma' }}
                                    </p>
                                </a>
                            @empty
                                <p class="km-muted text-sm">Nessuna discussione disponibile nel Pianeta Roma.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="km-dark-card p-6">
                        <div>
                            <p class="km-eyebrow">Referenze inviate</p>
                            <h2 class="mt-2 text-2xl font-black text-white">Pipeline relazionale</h2>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($sentReferrals as $referral)
                                <div class="rounded-[1.4rem] border border-white/10 bg-white/[.045] p-4">
                                    <p class="text-sm font-bold text-white">{{ $referral->title ?? 'Referenza senza titolo' }}</p>
                                    <p class="km-muted mt-1 text-sm">
                                        {{ $referral->recipient?->name ?? 'Destinatario non disponibile' }}
                                    </p>
                                    <p class="km-muted mt-2 text-xs uppercase tracking-[0.16em]">
                                        {{ $referral->status?->label() ?? 'Stato non disponibile' }}
                                    </p>
                                </div>
                            @empty
                                <p class="km-muted text-sm">Non hai ancora inviato referenze nel tuo Pianeta professionale.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('onboarding._wizard')
</x-app-layout>
