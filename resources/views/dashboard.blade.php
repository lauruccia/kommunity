<x-app-layout>
    {{-- Body in tema scuro. Tutte le classi .km-* arrivano da public/css/kommunity.css --}}
    {{-- Tema scuro: classe spinta sul body via stack 'body-class' --}}
    @push('body-class') km-bg-dark @endpush

    @push('styles')
        <style>
            /* Stili UNICI di questa pagina. Tutto il resto vive in public/css/kommunity.css */
            .km-hero-shell{
                position:relative;
                overflow:hidden;
                border:1px solid var(--km-line-dark);
                background:
                    radial-gradient(circle at 95% 0%, rgba(139,197,63,.18), transparent 38%),
                    radial-gradient(circle at 0% 100%, rgba(45,212,191,.10), transparent 35%),
                    linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
                box-shadow:0 18px 60px rgba(0,0,0,.35);
                backdrop-filter:blur(18px);
                border-radius:var(--km-radius-lg);
            }
            .km-hero-shell::before{
                content:"K";
                position:absolute;
                right:-12px; top:-90px;
                font-size:280px; font-weight:900; line-height:1;
                color:rgba(255,255,255,.035);
                transform:skewX(-8deg);
                pointer-events:none;
            }
        </style>
    @endpush

    @php
        // ────────────────────────────────────────────────────────────────────
        // Calcoli locali (dati REALI: niente "Roma/Lazio" hardcoded)
        // ────────────────────────────────────────────────────────────────────
        $mp          = $user->memberProfile;
        $cityName    = $mp?->city?->name;
        $regionName  = $mp?->region?->name;
        $chapterName = $mp?->chapter?->name;
        $firstName   = \Illuminate\Support\Str::of($user->name)->before(' ');

        $zoneLabel = match (true) {
            !empty($cityName) && !empty($regionName) => $cityName . ' / ' . $regionName,
            !empty($regionName) => $regionName,
            !empty($cityName)   => $cityName,
            default => null,
        };
        $heroArea = $regionName ?: ($cityName ?: ($chapterName ?: null));

        // KPI computati dal database (no placeholder)
        $pendingOneToOnes = $receivedOneToOnes->filter(fn ($r) =>
            in_array($r->status->value ?? '', ['pending', 'rescheduled'], true)
        )->count();

        $unreadMessages = method_exists($user, 'unreadMessagesCount')
            ? $user->unreadMessagesCount()
            : ($recentMessages->count() ?: 0);

        $upcomingCount  = $upcomingEvents->count();
        $completionPct  = (int) ($profileCompletion['percentage'] ?? 0);
        $missingCount   = count($profileCompletion['missing'] ?? []);
        $hasOnepage     = (bool) optional($user->memberOnepage)->slug;

        // Profilo: card mostrate solo se popolate
        $businessFields = [];
        if ($mp?->category?->name)   $businessFields[] = ['Categoria', $mp->category->name, false];
        if ($cityName)               $businessFields[] = ['Citta\'', $cityName, false];
        if ($zoneLabel && $zoneLabel !== $cityName) $businessFields[] = ['Zona', $zoneLabel, false];
        if ($mp)                     $businessFields[] = ['Visibile in directory', $mp->is_visible_in_directory ? 'Si' : 'No', false];
        if (optional(optional($mp)->preferred_contact_method)?->label())
            $businessFields[] = ['Contatto preferito', $mp->preferred_contact_method->label(), false];
        if ($chapterName)            $businessFields[] = ['Pianeta', $chapterName, true];
    @endphp

    <main class="km-shell-wide space-y-4 py-5 sm:space-y-5">

        @if($needsOnboarding ?? false)
            <section class="km-dark-card p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-[color:var(--km-green-2)]">
                            Account attivo
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-white">
                            Completa il profilo per sbloccare la community
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-white/65">
                            Mancano alcuni dati essenziali: professione, città e telefono. La dashboard resta accessibile, ma directory, eventi e incontri sono guidati dal profilo.
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="km-cta-primary shrink-0 text-sm">
                        Completa profilo
                    </a>
                </div>
            </section>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════
             1. HERO COMPATTO (max ~140px)
             ═══════════════════════════════════════════════════════════════════ --}}
        <section class="km-hero-shell px-5 py-5 sm:px-7 sm:py-6">
            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-[color:var(--km-green-2)]">
                        Dashboard membro{{ $chapterName ? ' · ' . $chapterName : '' }}
                    </p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl">
                        Ciao {{ $firstName }},
                        <span class="text-[color:var(--km-green)]">cosa vuoi fare oggi?</span>
                    </h1>

                    {{-- Bar completamento integrata nell'hero, niente sezione separata --}}
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <div class="flex min-w-[180px] flex-1 items-center gap-3 sm:max-w-md">
                            <div class="km-progress flex-1">
                                <span style="width: {{ $completionPct }}%"></span>
                            </div>
                            <span class="text-xs font-bold tabular-nums text-white/85">{{ $completionPct }}%</span>
                        </div>
                        @if ($missingCount > 0)
                            <span class="text-xs text-white/55">
                                {{ $missingCount }} {{ $missingCount === 1 ? 'campo da completare' : 'campi da completare' }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    @if ($completionPct < 100)
                        <a href="{{ route('profile.edit') }}" class="km-cta-secondary text-sm">
                            Completa profilo
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="km-cta-secondary text-sm">
                            Modifica profilo
                        </a>
                    @endif
                    @if ($hasOnepage)
                        <a href="{{ route('members.show', $user->memberOnepage->slug) }}" class="km-cta-primary text-sm">
                            Pagina personale
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17 17 7M9 7h8v8"/></svg>
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="km-cta-primary text-sm">
                            Crea pagina personale
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════
             2. KPI ROW (sm: 2x2, lg: 1x4)
             ═══════════════════════════════════════════════════════════════════ --}}
        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-dashboard.kpi
                label="Profilo"
                value="{{ $completionPct }}%"
                sub="{{ $profileCompletion['done'] ?? 0 }}/{{ $profileCompletion['total'] ?? 0 }} campi"
                tone="{{ $completionPct >= 80 ? 'green' : ($completionPct >= 50 ? 'amber' : 'rose') }}"
                href="{{ route('profile.edit') }}"
                :badge="$completionPct < 100 ? 'Completa' : null"
            >
                <x-slot name="icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                </x-slot>
            </x-dashboard.kpi>

            <x-dashboard.kpi
                label="One-to-one da gestire"
                value="{{ $pendingOneToOnes }}"
                sub="{{ $receivedOneToOnes->count() }} ricevuti totali"
                tone="{{ $pendingOneToOnes > 0 ? 'amber' : 'green' }}"
                href="{{ route('one-to-ones.index', ['type' => 'received']) }}"
                :badge="$pendingOneToOnes > 0 ? 'Azione' : null"
            >
                <x-slot name="icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 8l4 4-4 4M3 12h18M7 16l-4-4 4-4"/></svg>
                </x-slot>
            </x-dashboard.kpi>

            <x-dashboard.kpi
                label="Eventi in arrivo"
                value="{{ $upcomingCount }}"
                sub="{{ $upcomingCount === 0 ? 'Nessuno in calendario' : ($upcomingCount === 1 ? 'Prossimo appuntamento' : 'Appuntamenti aperti') }}"
                tone="teal"
                href="{{ route('events.index') }}"
            >
                <x-slot name="icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>
                </x-slot>
            </x-dashboard.kpi>

            <x-dashboard.kpi
                label="Conversazioni"
                value="{{ $recentMessages->count() }}"
                sub="Messaggi recenti tra membri"
                tone="green"
                href="{{ route('conversations.index') }}"
            >
                <x-slot name="icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a8 8 0 0 1-11.5 7.2L3 21l1.8-6.5A8 8 0 1 1 21 12z"/></svg>
                </x-slot>
            </x-dashboard.kpi>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════
             3. AZIONI RAPIDE (CTA orientate all'azione)
             ═══════════════════════════════════════════════════════════════════ --}}
        <section class="grid gap-3 sm:grid-cols-3">
            <x-dashboard.quick-action
                href="{{ route('one-to-ones.index', ['compose' => 1]) }}"
                title="Nuovo one-to-one"
                desc="Cerca un membro e invia richiesta"
                tone="green"
            >
                <x-slot name="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                </x-slot>
            </x-dashboard.quick-action>

            <x-dashboard.quick-action
                href="{{ route('conversations.index') }}"
                title="Scrivi un membro"
                desc="Apri una conversazione privata"
                tone="teal"
            >
                <x-slot name="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7l9 6 9-6M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-9-4-9 4z"/></svg>
                </x-slot>
            </x-dashboard.quick-action>

            <x-dashboard.quick-action
                href="{{ route('referrals.index') }}"
                title="Invia una referenza"
                desc="Connetti due membri per opportunita'"
                tone="amber"
            >
                <x-slot name="icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4 20-7zM22 2 11 13"/></svg>
                </x-slot>
            </x-dashboard.quick-action>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════
             4. OPERATIVO (1:1 ricevuti + eventi prossimi)
             ═══════════════════════════════════════════════════════════════════ --}}
        <section class="grid gap-4 lg:grid-cols-[1.4fr_0.9fr]">
            <x-dashboard.section
                eyebrow="One-to-one ricevuti"
                title="Ultime richieste"
                :href="route('one-to-ones.index')"
                cta="Gestisci tutte"
            >
                @forelse ($receivedOneToOnes->take(4) as $r)
                    <div @class(['mt-2.5' => ! $loop->first])>
                        <x-dashboard.one-to-one-row :request="$r" :current-user-id="$user->id" />
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-6 text-center">
                        <p class="text-sm text-white/65">Nessuna richiesta ricevuta.</p>
                        <p class="mt-1 text-xs text-white/40">Apri la directory per scoprire altri membri e proporre un incontro.</p>
                        <a href="{{ route('directory.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-[color:var(--km-green-2)] hover:underline">
                            Vai alla directory &rarr;
                        </a>
                    </div>
                @endforelse
            </x-dashboard.section>

            <x-dashboard.section
                eyebrow="Eventi"
                title="Prossimi appuntamenti"
                :href="route('events.index')"
                cta="Vedi tutti"
            >
                @forelse ($upcomingEvents->take(4) as $event)
                    <div @class(['mt-2' => ! $loop->first])>
                        <x-dashboard.event-row :event="$event" />
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-6 text-center">
                        <p class="text-sm text-white/65">Nessun evento in calendario.</p>
                        <a href="{{ route('events.index') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-[color:var(--km-green-2)] hover:underline">
                            Crea o partecipa &rarr;
                        </a>
                    </div>
                @endforelse
            </x-dashboard.section>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════
             5. PROFILO BUSINESS (compatto, solo campi popolati)
             ═══════════════════════════════════════════════════════════════════ --}}
        <x-dashboard.section
            eyebrow="Profilo business"
            title="{{ $user->name }}"
            :href="route('profile.edit')"
            cta="Modifica"
        >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="flex shrink-0 items-center gap-3">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[rgba(139,197,63,.25)] bg-gradient-to-br from-[#0b1219] to-[#1e3128] text-2xl font-black text-white shadow-[0_0_28px_rgba(139,197,63,.16)]">
                        {{ \Illuminate\Support\Str::of($user->name ?? 'U')->substr(0, 1)->upper() }}
                    </div>
                    <div class="min-w-0">
                        @if (optional($mp)->company_name)
                            <p class="truncate text-sm font-bold text-white">{{ $mp->company_name }}</p>
                        @endif
                        @if ($heroArea)
                            <p class="truncate text-xs text-white/55">{{ $heroArea }}</p>
                        @endif
                        @if (! optional($mp)->company_name && ! $heroArea)
                            <p class="text-xs text-white/55">
                                <a href="{{ route('profile.edit') }}" class="font-semibold text-[color:var(--km-green-2)] hover:underline">
                                    Aggiungi azienda e zona
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                @if (! empty($businessFields))
                    <div class="grid flex-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($businessFields as [$lab, $val, $accent])
                            <div class="rounded-xl border border-white/[.06] bg-white/[.02] px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/45">{{ $lab }}</p>
                                <p class="mt-0.5 truncate text-sm font-bold {{ $accent ? 'text-[color:var(--km-green-2)]' : 'text-white' }}">{{ $val }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex-1 rounded-xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-3 text-sm text-white/65">
                        Profilo ancora vuoto.
                        <a href="{{ route('profile.edit') }}" class="font-semibold text-[color:var(--km-green-2)] hover:underline">Compilalo</a>
                        per essere visibile e ricevere richieste.
                    </div>
                @endif
            </div>
        </x-dashboard.section>

        {{-- ═══════════════════════════════════════════════════════════════════
             6. CONTENUTI SECONDARI (forum + referenze)
             ═══════════════════════════════════════════════════════════════════ --}}
        <section class="grid gap-4 lg:grid-cols-2">
            <x-dashboard.section
                eyebrow="Forum"
                title="Discussioni recenti"
                :href="route('forum.index')"
                cta="Apri forum"
            >
                @forelse ($latestThreads->take(4) as $thread)
                    <a href="{{ route('forum.show', $thread) }}"
                       @class([
                           'flex items-center gap-3 rounded-xl border border-white/[.08] bg-white/[.04] p-3 transition hover:border-[rgba(139,197,63,.30)]',
                           'mt-2' => ! $loop->first,
                       ])>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[rgba(139,197,63,.10)] text-sm font-black text-[color:var(--km-green-2)]">
                            #
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-white">{{ $thread->title }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-white/55">{{ $thread->category?->name ?? 'Generale' }}</p>
                        </div>
                        <svg class="h-4 w-4 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-5 text-center text-sm text-white/55">
                        Nessuna discussione attiva.
                        <a href="{{ route('forum.index') }}" class="ml-1 font-semibold text-[color:var(--km-green-2)] hover:underline">Aprine una</a>
                    </div>
                @endforelse
            </x-dashboard.section>

            <x-dashboard.section
                eyebrow="Referenze inviate"
                title="Pipeline relazionale"
                :href="route('referrals.index')"
                cta="Tutte le referenze"
            >
                @forelse ($sentReferrals->take(4) as $referral)
                    <div @class(['rounded-xl border border-white/[.08] bg-white/[.04] p-3', 'mt-2' => ! $loop->first])>
                        <div class="flex items-start justify-between gap-2">
                            <p class="truncate text-sm font-bold text-white">{{ $referral->title ?: 'Senza titolo' }}</p>
                            @php
                                $rs = $referral->status?->value;
                                $rsTone = match($rs) {
                                    'won' => ['#9AD84A','rgba(139,197,63,.14)'],
                                    'lost' => ['#FDA4AF','rgba(244,63,94,.12)'],
                                    'pending' => ['#FCD34D','rgba(245,158,11,.14)'],
                                    default => ['#94A3B8','rgba(148,163,184,.12)'],
                                };
                            @endphp
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                  style="color: {{ $rsTone[0] }}; background: {{ $rsTone[1] }};">
                                {{ $referral->status?->label() ?? '—' }}
                            </span>
                        </div>
                        @if ($referral->recipient?->name)
                            <p class="mt-1 text-xs text-white/55">A {{ $referral->recipient->name }}</p>
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-5 text-center text-sm text-white/55">
                        Non hai ancora inviato referenze.
                        <a href="{{ route('referrals.index') }}" class="ml-1 font-semibold text-[color:var(--km-green-2)] hover:underline">Invia la prima</a>
                    </div>
                @endforelse
            </x-dashboard.section>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════
             7. PERFORMANCE (analytics, gated; mostrato solo se ha dati)
             ═══════════════════════════════════════════════════════════════════ --}}
        @php
            $hasMeaningfulAnalytics = ! empty($analytics) && (
                ($analytics['one_to_ones']['completed'] ?? 0) > 0 ||
                ($analytics['referrals']['won'] ?? 0) > 0 ||
                ($analytics['referrals']['won_value'] ?? 0) > 0
            );
        @endphp
        @if ($hasMeaningfulAnalytics)
            @include('partials.dashboard-analytics', ['analytics' => $analytics])
        @endif

    </main>

    @include('onboarding._wizard')
</x-app-layout>
