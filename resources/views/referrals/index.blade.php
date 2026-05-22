<x-app-layout>
    @php
        $openStatuses = ['sent', 'in_charge', 'contacted', 'negotiating'];
        $allItems     = $receivedReferrals->getCollection()->concat($sentReferrals->getCollection())->sortByDesc('updated_at');
        $receivedOpen = $receivedReferrals->getCollection()->filter(fn ($r) => in_array($r->status->value, $openStatuses, true));
        $sentOpen     = $sentReferrals->getCollection()->filter(fn ($r) => in_array($r->status->value, $openStatuses, true));
        $archiveAll   = $allItems->filter(fn ($r) => ! in_array($r->status->value, $openStatuses, true));

        $priorityStars = fn (?string $p) => match(true) {
            in_array($p, ['1','2','3','4','5'], true) => (int) $p,
            $p === 'high'   => 5,
            $p === 'low'    => 1,
            default         => 3,
        };

        $statusClass = fn ($status) => match ($status?->value ?? $status) {
            'sent'                              => 'kr-status-blue',
            'in_charge','contacted','negotiating' => 'kr-status-green',
            'won'                               => 'kr-status-won',
            'lost'                              => 'kr-status-red',
            'archived'                          => 'kr-status-slate',
            default                             => 'kr-status-slate',
        };
    @endphp

    <style>
        :root {
            --kr-bg: #001821;
            --kr-panel: rgba(4,34,45,.78);
            --kr-line: rgba(153,194,202,.17);
            --kr-line-strong: rgba(169,214,221,.26);
            --kr-text: #f5fbfd;
            --kr-muted: rgba(222,235,238,.68);
            --kr-soft: rgba(222,235,238,.48);
            --kr-green: #79c843;
            --kr-green-2: #55aa54;
            --kr-teal: #2dd4bf;
            --kr-amber: #f6c343;
            --kr-red: #ef6262;
        }
        body {
            background:
                radial-gradient(circle at 82% 0%, rgba(121,200,67,.16),transparent 28%),
                radial-gradient(circle at 8% 22%, rgba(45,212,191,.10),transparent 30%),
                linear-gradient(135deg,#00121a,var(--kr-bg) 48%,#042d31) !important;
            color: var(--kr-text);
        }
        .kr-shell { width: min(1500px,calc(100% - 48px)); margin: 0 auto; }
        .kr-card {
            background: linear-gradient(145deg,rgba(4,35,46,.86),rgba(2,25,34,.74));
            border: 1px solid var(--kr-line);
            border-radius: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.025),0 24px 80px rgba(0,0,0,.18);
            backdrop-filter: blur(16px);
            color: var(--kr-text);
        }
        .kr-layout { display:grid; grid-template-columns:300px minmax(0,1fr); gap:20px; align-items:start; }
        .kr-input { border:1px solid var(--kr-line-strong); background:rgba(2,24,33,.72); color:var(--kr-text); outline:none; }
        .kr-input:focus { border-color:rgba(121,200,67,.42); box-shadow:0 0 0 3px rgba(121,200,67,.08); }
        .kr-primary { background:linear-gradient(135deg,var(--kr-green-2),var(--kr-green)); color:#f8fff5; }
        .kr-green-text { color: var(--kr-green); }

        /* ── Tab nav ── */
        .kr-tabs { display:flex; gap:4px; background:rgba(2,24,33,.6); border:1px solid var(--kr-line); border-radius:14px; padding:5px; flex-wrap:wrap; }
        .kr-tab { padding:8px 18px; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; border-radius:10px; border:none; background:none; color:var(--kr-muted); cursor:pointer; transition:all .18s; white-space:nowrap; }
        .kr-tab:hover { color:var(--kr-text); background:rgba(255,255,255,.06); }
        .kr-tab.active { background:rgba(121,200,67,.16); color:var(--kr-green); border:1px solid rgba(121,200,67,.28); }
        .kr-tab.admin-tab { color:rgba(246,195,67,.7); }
        .kr-tab.admin-tab.active { background:rgba(246,195,67,.12); color:var(--kr-amber); border:1px solid rgba(246,195,67,.28); }
        .kr-tab-count { display:inline-flex; align-items:center; justify-content:center; min-width:18px; height:18px; border-radius:999px; background:rgba(255,255,255,.1); font-size:.65rem; padding:0 5px; margin-left:5px; }
        .kr-tab.active .kr-tab-count { background:rgba(121,200,67,.25); }

        /* ── Referral rows ── */
        .kr-referral-row { border-top:1px solid rgba(153,194,202,.12); position:relative; padding:18px 20px 18px 68px !important; }
        .kr-referral-row::before { content:""; position:absolute; left:30px; top:20px; bottom:20px; width:3px; border-radius:999px; background:linear-gradient(var(--kr-green),#6d7df0); }
        .kr-referral-row.sent-row::before { background:linear-gradient(#2dd4bf,#6d7df0); }
        .kr-referral-row.archive-row::before { background:linear-gradient(#6d7df0,#7b8794); }
        .kr-referral-grid { display:grid; grid-template-columns:minmax(0,1fr) 200px 160px; gap:18px; align-items:center; }

        /* ── Status badges ── */
        .kr-status { border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        .kr-status-blue   { background:rgba(113,151,255,.18); color:#adc0ff; }
        .kr-status-green  { background:rgba(121,200,67,.22); color:#a7ea76; }
        .kr-status-won    { background:rgba(46,213,115,.22); color:#6ffaac; }
        .kr-status-red    { background:rgba(239,98,98,.16); color:#ff8888; }
        .kr-status-slate  { background:rgba(148,163,184,.18); color:#d0dae4; }
        .kr-dir-badge { border-radius:999px; padding:2px 9px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; }
        .kr-dir-in  { background:rgba(45,212,191,.14); color:#5eead4; }
        .kr-dir-out { background:rgba(120,105,240,.14); color:#a5b4fc; }

        /* ── Endorsement badge ── */
        .kr-endorse-badge { display:inline-flex; align-items:center; gap:5px; border-radius:999px; padding:3px 10px; font-size:11px; font-weight:700; }
        .kr-endorse-on  { background:rgba(246,195,67,.18); color:var(--kr-amber); border:1px solid rgba(246,195,67,.28); }
        .kr-endorse-off { background:rgba(255,255,255,.06); color:var(--kr-muted); border:1px solid rgba(255,255,255,.08); cursor:pointer; }

        /* ── Stat cards ── */
        .kr-stats-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; }
        .kr-stat-card { min-height:92px; padding:16px; display:flex; align-items:center; gap:14px; }
        .kr-stat-icon { width:50px; height:50px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; flex:none; font-size:22px; }
        .kr-icon-green  { background:rgba(121,200,67,.18); color:var(--kr-green); }
        .kr-icon-teal   { background:rgba(45,212,191,.14); color:var(--kr-teal); }
        .kr-icon-amber  { background:rgba(246,195,67,.15); color:var(--kr-amber); }
        .kr-icon-violet { background:rgba(120,105,240,.18); color:#9ca3ff; }

        /* ── Filter grid ── */
        .kr-filter-grid { display:grid; grid-template-columns:minmax(240px,1fr) 170px 160px 100px; gap:14px; align-items:end; }

        /* ── Hero ── */
        .kr-hero { margin-bottom:20px; padding:24px 28px; }
        .kr-hero-grid { display:grid; grid-template-columns:74px minmax(0,1fr) auto; gap:24px; align-items:center; }

        /* ── Admin table ── */
        .kr-admin-row { border-top:1px solid rgba(153,194,202,.10); padding:14px 20px; display:grid; grid-template-columns:minmax(0,1fr) 130px 120px 90px 110px; gap:12px; align-items:center; }

        @media(max-width:1180px) {
            .kr-layout { grid-template-columns:280px minmax(0,1fr); }
            .kr-stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .kr-referral-grid { grid-template-columns:minmax(0,1fr); }
            .kr-filter-grid { grid-template-columns:minmax(200px,1fr) 140px 100px; }
        }
        @media(max-width:760px) {
            .kr-shell { width:min(100% - 28px,760px); }
            .kr-layout,.kr-hero-grid,.kr-filter-grid,.kr-stats-grid { grid-template-columns:1fr; }
            .kr-referral-row { padding-left:48px !important; }
            .kr-referral-row::before { left:18px; }
            .kr-admin-row { grid-template-columns:1fr; }
        }
    </style>

    <div class="kr-shell py-7">

        {{-- ── HERO ──────────────────────────────────────────────────────── --}}
        <header class="kr-card kr-hero">
            <div class="kr-hero-grid">
                <div class="kr-stat-icon kr-icon-green" style="width:74px;height:74px;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.47 6.03.88-4.36 4.25 1.03 6-5.4-2.84-5.4 2.84 1.03-6-4.36-4.25 6.03-.88L12 3Z"/><path d="M9 12l2 2 4-5"/></svg>
                </div>
                <div>
                    <p class="kr-green-text text-sm font-semibold uppercase tracking-[.35em]">Referenze business</p>
                    <h1 class="mt-3 text-3xl font-semibold text-white">Opportunità e introduzioni tra membri</h1>
                    <p class="mt-2 text-base" style="color:var(--kr-muted);">Invia opportunità, traccia lo stato e costruisci relazioni significative in Kommunity.</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['sent'] }}</strong> <span style="color:var(--kr-muted);">Inviate</span></div>
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['received'] }}</strong> <span style="color:var(--kr-muted);">Ricevute</span></div>
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['open'] }}</strong> <span style="color:var(--kr-muted);">Aperte</span></div>
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['won'] }}</strong> <span style="color:var(--kr-muted);">Chiuse ✓</span></div>
                    </div>
                </div>
                <div class="text-right text-sm" style="color:var(--kr-muted);">
                    <div class="kr-green-text text-2xl tracking-[.12em]">★★★★★</div>
                    <div>Referenze qualificate dalla rete Kommunity</div>
                </div>
            </div>
        </header>

        {{-- ── FLASH ──────────────────────────────────────────────────────── --}}
        @if (session('status') === 'referral-created')
            <div class="mb-4 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-4">
                <p class="font-semibold text-emerald-200">Referenza inviata!</p>
                @if (session('suggest_id'))
                    <p class="mt-1 text-sm" style="color:var(--kr-muted);">
                        Vuoi mandare un messaggio di accompagnamento a <strong class="text-white">{{ session('suggest_name') }}</strong>?
                        Un breve intro aumenta le probabilità che la referenza vada a buon fine.
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <a href="{{ route('conversations.index', ['to' => session('suggest_id')]) }}"
                           class="kr-primary inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Scrivi a {{ session('suggest_name') }}
                        </a>
                        <span class="text-xs italic" style="color:var(--kr-soft);">
                            Suggerimento: "Ciao, ti ho inviato una referenza riguardo '{{ session('suggest_title') }}'. Fammi sapere se hai bisogno di dettagli!"
                        </span>
                    </div>
                @endif
            </div>
        @elseif (session('status') === 'referral-updated')
            <div class="mb-4 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">Stato referenza aggiornato.</div>
        @elseif (session('status') === 'referral-acknowledged')
            <div class="mb-4 rounded-xl border border-teal-400/30 bg-teal-400/10 px-4 py-3 text-sm text-teal-200">Referenza presa in carico. Il mittente è stato notificato.</div>
        @elseif (session('status') === 'referral-public')
            <div class="mb-4 rounded-xl border border-amber-400/30 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">Referenza ora visibile sul tuo profilo pubblico.</div>
        @elseif (session('status') === 'referral-private')
            <div class="mb-4 rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-sm" style="color:var(--kr-muted);">Referenza rimossa dal profilo pubblico.</div>
        @elseif (session('status') === 'referral-deleted')
            <div class="mb-4 rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">Referenza eliminata.</div>
        @endif

        <main class="kr-layout">

            {{-- ── SIDEBAR FORM ──────────────────────────────────────────── --}}
            <aside class="kr-card p-5">
                <h2 class="text-xl font-semibold text-white">Nuova referenza</h2>
                <p class="mt-3 text-sm leading-6" style="color:var(--kr-muted);">Puoi inviare una referenza solo a membri con cui hai un one-to-one completato e confermato da entrambi.</p>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">
                        <p class="font-semibold">Controlla i dati inseriti.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                @if ($members->isEmpty())
                    <div class="mt-5 flex min-h-[220px] flex-col items-center justify-center rounded-xl border border-dashed border-white/20 bg-white/[.025] px-6 text-center">
                        <svg class="kr-green-text" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                        <h3 class="mt-4 font-semibold text-white">Nessun membro idoneo</h3>
                        <p class="mt-2 text-sm leading-6" style="color:var(--kr-muted);">Completa un one-to-one per sbloccare questa funzione.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('referrals.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <select name="recipient_id" class="kr-input h-12 w-full rounded-xl px-4" required>
                            <option value="">Seleziona destinatario</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected((string) old('recipient_id') === (string) $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="title" value="{{ old('title') }}" class="kr-input h-12 w-full rounded-xl px-4" placeholder="Titolo opportunità" required>
                        <textarea name="description" rows="4" class="kr-input w-full rounded-xl px-4 py-3" placeholder="Descrivi l'opportunità — contesto, obiettivo, perché questo membro può aiutare" required>{{ old('description') }}</textarea>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" class="kr-input h-12 w-full rounded-xl px-4" placeholder="Azienda (opzionale)">
                        <div>
                            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.16em;color:rgba(222,235,238,.55);margin-bottom:.5rem;">Qualità dell'opportunità</p>
                            <div id="kr-new-stars" style="display:flex;gap:.3rem;">
                                @for ($s = 1; $s <= 5; $s++)
                                    <button type="button" data-val="{{ $s }}" onclick="krSetStar({{ $s }},'kr-new-stars','kr-new-priority')" style="background:none;border:none;cursor:pointer;padding:.15rem;">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="{{ old('priority','3') >= $s ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8" data-idx="{{ $s }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="priority" id="kr-new-priority" value="{{ old('priority','3') }}">
                        </div>
                        <button type="submit" class="kr-primary h-12 w-full rounded-xl font-semibold">Invia referenza</button>
                    </form>
                @endif
            </aside>

            {{-- ── MAIN CONTENT ───────────────────────────────────────────── --}}
            <section class="min-w-0 space-y-5">

                {{-- Tab navigation --}}
                <div class="kr-tabs">
                    <button class="kr-tab {{ $activeTab === 'ricevute' ? 'active' : '' }}" data-tab="ricevute" onclick="switchTab('ricevute')">
                        Ricevute <span class="kr-tab-count">{{ $receivedOpen->count() }}</span>
                    </button>
                    <button class="kr-tab {{ $activeTab === 'inviate' ? 'active' : '' }}" data-tab="inviate" onclick="switchTab('inviate')">
                        Inviate <span class="kr-tab-count">{{ $sentOpen->count() }}</span>
                    </button>
                    <button class="kr-tab {{ $activeTab === 'archivio' ? 'active' : '' }}" data-tab="archivio" onclick="switchTab('archivio')">
                        Archivio <span class="kr-tab-count">{{ $archiveAll->count() }}</span>
                    </button>
                    @if ($isAdmin)
                        <button class="kr-tab admin-tab {{ $activeTab === 'moderazione' ? 'active' : '' }}" data-tab="moderazione" onclick="switchTab('moderazione')" style="margin-left:auto;">
                            ★ Moderazione <span class="kr-tab-count">{{ $adminReferrals?->total() ?? 0 }}</span>
                        </button>
                    @endif
                </div>

                {{-- Filter (comune a tutti i tab) --}}
                <section class="kr-card p-5">
                    <form method="GET" action="{{ route('referrals.index') }}" class="kr-filter-grid">
                        <input type="hidden" name="tab" id="kr-active-tab-input" value="{{ $activeTab }}">
                        <label>
                            <span class="mb-2 block text-sm text-white">Cerca</span>
                            <span class="relative block">
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="kr-input h-14 w-full rounded-xl px-4 pr-12" placeholder="Titolo, azienda...">
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            </span>
                        </label>
                        <label>
                            <span class="mb-2 block text-sm text-white">Stato</span>
                            <select name="status" class="kr-input h-14 w-full rounded-xl px-4">
                                <option value="">Tutti</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div>
                            <span class="mb-2 block text-sm text-white">Qualità min.</span>
                            <div id="kr-filter-stars" style="display:flex;gap:.25rem;margin-top:.35rem;">
                                @for ($s = 1; $s <= 5; $s++)
                                    <button type="button" data-val="{{ $s }}" onclick="krSetStar({{ $s }},'kr-filter-stars','kr-filter-priority')" style="background:none;border:none;cursor:pointer;padding:.1rem;">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="{{ ($filters['priority'] ?? '0') >= $s ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8" data-idx="{{ $s }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </button>
                                @endfor
                                <button type="button" onclick="krClearStar('kr-filter-stars','kr-filter-priority')" style="background:none;border:none;cursor:pointer;font-size:.72rem;font-weight:700;color:rgba(222,235,238,.45);padding:.1rem .4rem;">✕</button>
                            </div>
                            <input type="hidden" name="priority" id="kr-filter-priority" value="{{ $filters['priority'] ?? '' }}">
                        </div>
                        <button type="submit" class="kr-primary h-14 rounded-xl px-5 font-semibold">Filtra</button>
                    </form>
                </section>

                {{-- Stats --}}
                <section class="kr-stats-grid">
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3"/></svg></div><div><div class="text-sm text-white">Totali</div><div class="text-2xl font-semibold">{{ $summary['sent'] + $summary['received'] }}</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-teal"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div><div><div class="text-sm text-white">Inviate</div><div class="text-2xl font-semibold">{{ $summary['sent'] }}</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-teal"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></div><div><div class="text-sm text-white">Ricevute</div><div class="text-2xl font-semibold">{{ $summary['received'] }}</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-amber"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></div><div><div class="text-sm text-white">Aperte</div><div class="text-2xl font-semibold">{{ $summary['open'] }}</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div><div class="text-sm text-white">Chiuse positivamente</div><div class="text-2xl font-semibold">{{ $summary['won'] }}</div></div></div>
                </section>

                {{-- ════════════════ TAB: RICEVUTE ════════════════ --}}
                <section id="tab-ricevute" class="kr-card overflow-hidden kr-tab-pane" style="{{ $activeTab !== 'ricevute' ? 'display:none' : '' }}">
                    <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Referenze ricevute</h2>
                        <span class="text-sm" style="color:var(--kr-muted);">{{ $receivedOpen->count() }} aperte</span>
                    </div>
                    @forelse ($receivedOpen as $referral)
                        @php $stars = $priorityStars($referral->priority); @endphp
                        <div class="kr-referral-row">
                            <div class="kr-referral-grid">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="kr-dir-badge kr-dir-in">← Ricevuta</span>
                                        <span class="kr-status {{ $statusClass($referral->status) }}">{{ $referral->status->label() }}</span>
                                        <span style="display:inline-flex;gap:.1rem;">
                                            @for ($s=1;$s<=5;$s++)<svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $s<=$stars?'#FCD34D':'none' }}" stroke="#FCD34D" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>@endfor
                                        </span>
                                        @if ($referral->is_public)
                                            <span class="kr-endorse-badge kr-endorse-on">★ Pubblica</span>
                                        @endif
                                    </div>
                                    <h3 class="mt-2 text-base font-semibold text-white">{{ $referral->title }}</h3>
                                    <p class="mt-1 text-sm" style="color:var(--kr-muted);">Da <strong class="text-white">{{ $referral->sender?->name ?? 'Utente eliminato' }}</strong>{{ $referral->company_name ? ' · '.$referral->company_name : '' }} · {{ $referral->created_at->format('d M Y') }}</p>
                                    <p class="mt-2 text-sm leading-6" style="color:var(--kr-soft);">{{ Str::limit($referral->description, 140) }}</p>
                                </div>

                                <div class="space-y-2 text-sm" style="color:var(--kr-muted);">
                                    <div>Aggiornata {{ $referral->updated_at->diffForHumans() }}</div>
                                    @if ($referral->acknowledged_at)
                                        <div style="color:var(--kr-teal);">✓ Presa in carico {{ $referral->acknowledged_at->format('d M') }}</div>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2">
                                    {{-- Quick acknowledge --}}
                                    @if ($referral->status->value === 'sent')
                                        <form method="POST" action="{{ route('referrals.acknowledge', $referral) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="w-full rounded-xl border border-teal-400/30 bg-teal-400/10 px-4 py-2.5 text-sm font-semibold text-teal-300 hover:bg-teal-400/20 transition">
                                                ✓ Prendi in carico
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Gestisci (modal) --}}
                                    <button type="button"
                                        class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/5 transition"
                                        data-role="recipient"
                                        data-action="{{ route('referrals.status', $referral) }}"
                                        data-toggle-action="{{ route('referrals.toggle-public', $referral) }}"
                                        data-title="{{ e($referral->title) }}"
                                        data-status="{{ $referral->status->value }}"
                                        data-outcome="{{ e($referral->outcome) }}"
                                        data-notes="{{ e($referral->notes) }}"
                                        data-description="{{ e($referral->description) }}"
                                        data-company="{{ e($referral->company_name) }}"
                                        data-sender="{{ e($referral->sender?->name ?? 'Utente eliminato') }}"
                                        data-date="{{ $referral->created_at->format('d F Y') }}"
                                        data-is-public="{{ $referral->is_public ? '1' : '0' }}"
                                        onclick="openModal(this)">
                                        Gestisci
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm" style="color:var(--kr-muted);">Nessuna referenza ricevuta aperta.</div>
                    @endforelse
                    @if ($receivedReferrals->hasPages())
                        <div class="px-6 py-4 border-t border-white/10">{{ $receivedReferrals->links() }}</div>
                    @endif
                </section>

                {{-- ════════════════ TAB: INVIATE ════════════════ --}}
                <section id="tab-inviate" class="kr-card overflow-hidden kr-tab-pane" style="{{ $activeTab !== 'inviate' ? 'display:none' : '' }}">
                    <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Referenze inviate</h2>
                        <span class="text-sm" style="color:var(--kr-muted);">{{ $sentOpen->count() }} aperte</span>
                    </div>
                    @forelse ($sentOpen as $referral)
                        @php $stars = $priorityStars($referral->priority); @endphp
                        <div class="kr-referral-row sent-row">
                            <div class="kr-referral-grid">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="kr-dir-badge kr-dir-out">→ Inviata</span>
                                        <span class="kr-status {{ $statusClass($referral->status) }}">{{ $referral->status->label() }}</span>
                                        <span style="display:inline-flex;gap:.1rem;">
                                            @for ($s=1;$s<=5;$s++)<svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $s<=$stars?'#FCD34D':'none' }}" stroke="#FCD34D" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>@endfor
                                        </span>
                                    </div>
                                    <h3 class="mt-2 text-base font-semibold text-white">{{ $referral->title }}</h3>
                                    <p class="mt-1 text-sm" style="color:var(--kr-muted);">A <strong class="text-white">{{ $referral->recipient?->name ?? 'Utente eliminato' }}</strong>{{ $referral->company_name ? ' · '.$referral->company_name : '' }} · {{ $referral->created_at->format('d M Y') }}</p>
                                    <p class="mt-2 text-sm leading-6" style="color:var(--kr-soft);">{{ Str::limit($referral->description, 140) }}</p>
                                </div>
                                <div class="text-sm" style="color:var(--kr-muted);">
                                    <div>Aggiornata {{ $referral->updated_at->diffForHumans() }}</div>
                                    @if ($referral->acknowledged_at)
                                        <div class="mt-1" style="color:var(--kr-teal);">✓ Presa in carico</div>
                                    @endif
                                </div>
                                <button type="button"
                                    class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/5 transition"
                                    data-role="sender"
                                    data-action="{{ route('referrals.status', $referral) }}"
                                    data-title="{{ e($referral->title) }}"
                                    data-status="{{ $referral->status->value }}"
                                    data-outcome="{{ e($referral->outcome) }}"
                                    data-notes="{{ e($referral->notes) }}"
                                    data-description="{{ e($referral->description) }}"
                                    data-company="{{ e($referral->company_name) }}"
                                    data-sender="{{ e($referral->recipient?->name ?? 'Utente eliminato') }}"
                                    data-date="{{ $referral->created_at->format('d F Y') }}"
                                    data-is-public="0"
                                    onclick="openModal(this)">
                                    Dettaglio
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm" style="color:var(--kr-muted);">Nessuna referenza inviata aperta.</div>
                    @endforelse
                    @if ($sentReferrals->hasPages())
                        <div class="px-6 py-4 border-t border-white/10">{{ $sentReferrals->links() }}</div>
                    @endif
                </section>

                {{-- ════════════════ TAB: ARCHIVIO ════════════════ --}}
                <section id="tab-archivio" class="kr-card overflow-hidden kr-tab-pane" style="{{ $activeTab !== 'archivio' ? 'display:none' : '' }}">
                    <div class="border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Archivio</h2>
                    </div>
                    @forelse ($archiveAll as $referral)
                        @php
                            $isSent = $referral->sender_id === auth()->id();
                            $actor  = $isSent ? $referral->recipient : $referral->sender;
                            $stars  = $priorityStars($referral->priority);
                        @endphp
                        <div class="kr-referral-row archive-row">
                            <div class="kr-referral-grid">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="kr-dir-badge {{ $isSent ? 'kr-dir-out' : 'kr-dir-in' }}">{{ $isSent ? '→ Inviata' : '← Ricevuta' }}</span>
                                        <span class="kr-status {{ $statusClass($referral->status) }}">{{ $referral->status->label() }}</span>
                                    </div>
                                    <h3 class="mt-2 text-base font-semibold text-white">{{ $referral->title }}</h3>
                                    <p class="mt-1 text-sm" style="color:var(--kr-muted);">{{ $isSent ? 'A' : 'Da' }} <strong class="text-white">{{ $actor?->name ?? 'Utente eliminato' }}</strong> · {{ $referral->created_at->format('d M Y') }}</p>
                                    @if ($referral->outcome)
                                        <p class="mt-1 text-sm italic" style="color:var(--kr-soft);">Esito: {{ Str::limit($referral->outcome, 100) }}</p>
                                    @endif
                                </div>
                                <div class="text-sm" style="color:var(--kr-muted);">{{ $referral->updated_at->format('d/m/Y') }}</div>
                                <button type="button" class="rounded-xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/5 transition"
                                    data-role="{{ $isSent ? 'sender' : 'recipient' }}"
                                    data-action="{{ route('referrals.status', $referral) }}"
                                    data-toggle-action="{{ route('referrals.toggle-public', $referral) }}"
                                    data-title="{{ e($referral->title) }}"
                                    data-status="{{ $referral->status->value }}"
                                    data-outcome="{{ e($referral->outcome) }}"
                                    data-notes="{{ e($referral->notes) }}"
                                    data-description="{{ e($referral->description) }}"
                                    data-company="{{ e($referral->company_name) }}"
                                    data-sender="{{ e($actor?->name ?? 'Utente eliminato') }}"
                                    data-date="{{ $referral->created_at->format('d F Y') }}"
                                    data-is-public="{{ $referral->is_public ? '1' : '0' }}"
                                    onclick="openModal(this)">Visualizza</button>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm" style="color:var(--kr-muted);">Nessuna referenza in archivio.</div>
                    @endforelse
                </section>

                {{-- ════════════════ TAB: MODERAZIONE (ADMIN) ════════════════ --}}
                @if ($isAdmin)
                <section id="tab-moderazione" class="kr-card overflow-hidden kr-tab-pane" style="{{ $activeTab !== 'moderazione' ? 'display:none' : '' }}">
                    <div class="border-b border-white/10 px-6 py-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold" style="color:var(--kr-amber);">★ Moderazione — tutte le referenze</h2>
                        <span class="text-sm" style="color:var(--kr-muted);">{{ $adminReferrals?->total() ?? 0 }} totali</span>
                    </div>
                    <div class="px-4 py-2 text-xs font-semibold uppercase tracking-widest border-b border-white/10 kr-admin-row" style="color:var(--kr-muted);padding-top:8px;padding-bottom:8px;">
                        <span>Titolo</span><span>Da → A</span><span>Stato</span><span>Data</span><span>Azioni</span>
                    </div>
                    @forelse ($adminReferrals ?? [] as $referral)
                        <div class="kr-admin-row hover:bg-white/[.02]">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $referral->title }}</p>
                                @if ($referral->company_name)
                                    <p class="text-xs mt-0.5" style="color:var(--kr-soft);">{{ $referral->company_name }}</p>
                                @endif
                            </div>
                            <div class="text-xs" style="color:var(--kr-muted);">
                                <span class="text-white">{{ $referral->sender?->name ?? '?' }}</span>
                                <span class="mx-1">→</span>
                                <span class="text-white">{{ $referral->recipient?->name ?? '?' }}</span>
                            </div>
                            <span class="kr-status {{ $statusClass($referral->status) }}">{{ $referral->status->label() }}</span>
                            <span class="text-xs" style="color:var(--kr-muted);">{{ $referral->created_at->format('d/m/Y') }}</span>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="rounded-lg border border-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/5 transition"
                                    data-role="admin"
                                    data-action="{{ route('referrals.status', $referral) }}"
                                    data-delete-action="{{ route('referrals.destroy', $referral) }}"
                                    data-title="{{ e($referral->title) }}"
                                    data-status="{{ $referral->status->value }}"
                                    data-outcome="{{ e($referral->outcome) }}"
                                    data-notes="{{ e($referral->notes) }}"
                                    data-description="{{ e($referral->description) }}"
                                    data-company="{{ e($referral->company_name) }}"
                                    data-sender="{{ e($referral->sender?->name ?? '?') }} → {{ e($referral->recipient?->name ?? '?') }}"
                                    data-date="{{ $referral->created_at->format('d F Y') }}"
                                    data-is-public="0"
                                    onclick="openModal(this)">Modifica</button>
                                <form method="POST" action="{{ route('referrals.destroy', $referral) }}" onsubmit="return confirm('Eliminare questa referenza?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-400/20 bg-rose-400/10 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-400/20 transition">Elimina</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm" style="color:var(--kr-muted);">Nessuna referenza nel sistema.</div>
                    @endforelse
                    @if ($adminReferrals?->hasPages())
                        <div class="px-6 py-4 border-t border-white/10">{{ $adminReferrals->links() }}</div>
                    @endif
                </section>
                @endif

            </section>
        </main>
    </div>

    {{-- ── MODAL ─────────────────────────────────────────────────────── --}}
    <div id="kr-modal-backdrop" style="display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.65);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:16px;">
        <div class="kr-card w-full p-7" style="max-width:540px;max-height:90vh;overflow-y:auto;position:relative;">
            <button type="button" onclick="closeModal()" style="position:absolute;top:16px;right:18px;background:none;border:none;color:rgba(255,255,255,.45);font-size:22px;cursor:pointer;" title="Chiudi">✕</button>

            <p id="kr-m-sender" class="text-xs font-semibold uppercase tracking-widest" style="color:var(--kr-green);"></p>
            <h2 id="kr-m-title" class="mt-2 text-xl font-semibold text-white pr-8"></h2>
            <p id="kr-m-meta" class="mt-1 text-sm" style="color:var(--kr-soft);"></p>
            <p id="kr-m-desc" class="mt-3 text-sm leading-6" style="color:var(--kr-muted);"></p>

            <div style="border-top:1px solid rgba(153,194,202,.18);margin:20px 0;"></div>

            {{-- Sezione gestione (recipient + admin) --}}
            <div id="kr-m-manage-section">
                <form id="kr-m-form" method="POST" class="space-y-3">
                    @csrf @method('PATCH')
                    <div>
                        <label class="mb-1 block text-sm text-white">Stato</label>
                        <select id="kr-m-status" name="status" class="kr-input h-11 w-full rounded-lg px-3">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-white">Esito</label>
                        <textarea id="kr-m-outcome" name="outcome" rows="2" class="kr-input w-full rounded-lg px-3 py-2" placeholder="Esito dell'opportunità..."></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-white">Note</label>
                        <textarea id="kr-m-notes" name="notes" rows="2" class="kr-input w-full rounded-lg px-3 py-2" placeholder="Note interne..."></textarea>
                    </div>
                    <button type="submit" class="kr-primary h-11 w-full rounded-xl text-sm font-semibold">Salva modifiche</button>
                </form>

                {{-- Endorsement (solo recipient) --}}
                <div id="kr-m-endorse-section" class="mt-4" style="display:none;">
                    <div style="border-top:1px solid rgba(153,194,202,.14);padding-top:16px;">
                        <p class="text-sm font-semibold text-white">Visibilità sul profilo</p>
                        <p class="mt-1 text-xs leading-5" style="color:var(--kr-muted);">Puoi scegliere di rendere questa referenza visibile sul tuo profilo pubblico come endorsement. Solo il titolo, il mittente e le stelle vengono mostrati.</p>
                        <form id="kr-m-endorse-form" method="POST" class="mt-3">
                            @csrf @method('PATCH')
                            <button id="kr-m-endorse-btn" type="submit" class="kr-endorse-badge kr-endorse-off text-sm px-4 py-2">★ Rendi pubblica</button>
                        </form>
                    </div>
                </div>

                {{-- Admin: elimina --}}
                <div id="kr-m-admin-delete" class="mt-4" style="display:none;">
                    <div style="border-top:1px solid rgba(239,98,98,.2);padding-top:16px;">
                        <form id="kr-m-delete-form" method="POST" onsubmit="return confirm('Eliminare questa referenza definitivamente?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full rounded-xl border border-rose-400/30 bg-rose-400/10 py-2.5 text-sm font-semibold text-rose-300 hover:bg-rose-400/20 transition">
                                Elimina referenza (admin)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sezione sola lettura (sender) --}}
            <div id="kr-m-readonly-section" style="display:none;">
                <p class="text-sm" style="color:var(--kr-muted);">Hai inviato questa referenza. Il destinatario gestisce lo stato.</p>
                <div id="kr-m-status-display" class="mt-3 inline-flex items-center gap-2"></div>
            </div>
        </div>
    </div>

    <script>
        // ── Tab switching ──────────────────────────────────────────────────
        function switchTab(tab) {
            document.querySelectorAll('.kr-tab').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
            document.querySelectorAll('.kr-tab-pane').forEach(p => p.style.display = p.id === 'tab-' + tab ? '' : 'none');
            document.getElementById('kr-active-tab-input').value = tab;
        }

        // ── Star rating helpers ────────────────────────────────────────────
        function krSetStar(val, groupId, inputId) {
            const group = document.getElementById(groupId);
            const input = document.getElementById(inputId);
            if (!group || !input) return;
            input.value = val;
            group.querySelectorAll('svg').forEach(svg => {
                svg.setAttribute('fill', parseInt(svg.dataset.idx, 10) <= val ? '#FCD34D' : 'none');
            });
        }
        function krClearStar(groupId, inputId) {
            const group = document.getElementById(groupId);
            const input = document.getElementById(inputId);
            if (!group || !input) return;
            input.value = '';
            group.querySelectorAll('svg').forEach(svg => svg.setAttribute('fill', 'none'));
        }
        document.addEventListener('DOMContentLoaded', () => {
            [['kr-new-stars','kr-new-priority'],['kr-filter-stars','kr-filter-priority']].forEach(([gId, iId]) => {
                const v = document.getElementById(iId)?.value;
                if (v) krSetStar(parseInt(v, 10), gId, iId);
            });
        });

        // ── Modal ──────────────────────────────────────────────────────────
        function decodeHtml(str) {
            const el = document.createElement('textarea');
            el.innerHTML = str;
            return el.value;
        }

        function openModal(btn) {
            const role = btn.dataset.role; // 'recipient' | 'sender' | 'admin'
            document.getElementById('kr-m-title').textContent  = decodeHtml(btn.dataset.title);
            document.getElementById('kr-m-sender').textContent = role === 'sender' ? 'A ' + decodeHtml(btn.dataset.sender) : 'Da ' + decodeHtml(btn.dataset.sender);
            document.getElementById('kr-m-meta').textContent   = (btn.dataset.company || '') + (btn.dataset.company ? ' · ' : '') + btn.dataset.date;
            document.getElementById('kr-m-desc').textContent   = decodeHtml(btn.dataset.description || '');

            const manageSection   = document.getElementById('kr-m-manage-section');
            const readonlySection = document.getElementById('kr-m-readonly-section');
            const endorseSection  = document.getElementById('kr-m-endorse-section');
            const adminDelete     = document.getElementById('kr-m-admin-delete');

            if (role === 'sender') {
                manageSection.style.display   = 'none';
                readonlySection.style.display = '';
                const statusBadge = document.getElementById('kr-m-status-display');
                statusBadge.innerHTML = '<span style="background:rgba(113,151,255,.18);color:#adc0ff;border-radius:999px;padding:3px 12px;font-size:12px;font-weight:700;">' + decodeHtml(btn.dataset.status).replace(/_/g,' ') + '</span>';
            } else {
                manageSection.style.display   = '';
                readonlySection.style.display = 'none';
                document.getElementById('kr-m-form').action  = btn.dataset.action;
                document.getElementById('kr-m-status').value = btn.dataset.status;
                document.getElementById('kr-m-outcome').value = decodeHtml(btn.dataset.outcome || '');
                document.getElementById('kr-m-notes').value   = decodeHtml(btn.dataset.notes || '');

                // Endorsement (solo recipient)
                if (role === 'recipient' && btn.dataset.toggleAction) {
                    endorseSection.style.display = '';
                    const isPublic = btn.dataset.isPublic === '1';
                    const endorseForm = document.getElementById('kr-m-endorse-form');
                    const endorseBtn  = document.getElementById('kr-m-endorse-btn');
                    endorseForm.action = btn.dataset.toggleAction;
                    if (isPublic) {
                        endorseBtn.textContent = '★ Rimuovi dal profilo';
                        endorseBtn.className = 'kr-endorse-badge kr-endorse-on text-sm px-4 py-2';
                    } else {
                        endorseBtn.textContent = '☆ Rendi pubblica sul profilo';
                        endorseBtn.className = 'kr-endorse-badge kr-endorse-off text-sm px-4 py-2';
                    }
                } else {
                    endorseSection.style.display = 'none';
                }

                // Admin delete
                if (role === 'admin' && btn.dataset.deleteAction) {
                    adminDelete.style.display = '';
                    document.getElementById('kr-m-delete-form').action = btn.dataset.deleteAction;
                } else {
                    adminDelete.style.display = 'none';
                }
            }

            document.getElementById('kr-modal-backdrop').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('kr-modal-backdrop').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('kr-modal-backdrop').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    </script>
</x-app-layout>
