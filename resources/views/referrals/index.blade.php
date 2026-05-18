<x-app-layout>
    @php
        $allReferrals = $receivedReferrals->getCollection()->concat($sentReferrals->getCollection())->sortByDesc('updated_at');
        $openStatuses = ['sent', 'in_charge', 'contacted', 'negotiating'];
        $receivedOpen = $receivedReferrals->getCollection()->filter(fn ($referral) => in_array($referral->status->value, $openStatuses, true));
        $archiveReferrals = $allReferrals->filter(fn ($referral) => ! in_array($referral->status->value, $openStatuses, true));
        $visibleArchive = $archiveReferrals->take(4);
        $receivedPercent = ($summary['sent'] + $summary['received']) > 0 ? round(($summary['received'] / max(1, $summary['sent'] + $summary['received'])) * 100) : 0;
        $openPercent = ($summary['sent'] + $summary['received']) > 0 ? round(($summary['open'] / max(1, $summary['sent'] + $summary['received'])) * 100) : 0;

        // Converte il valore priority (nuovo: '1'-'5', vecchio: 'low'/'medium'/'high') in stelle (1-5)
        $priorityStars = fn (?string $p) => match(true) {
            in_array($p, ['1','2','3','4','5'], true) => (int) $p,
            $p === 'high'   => 5,
            $p === 'low'    => 1,
            default         => 3,
        };

        $statusClass = fn ($status) => match ($status?->value ?? $status) {
            'sent' => 'kr-status-blue',
            'in_charge', 'contacted', 'negotiating' => 'kr-status-green',
            'won' => 'kr-status-green',
            'lost' => 'kr-status-red',
            'archived' => 'kr-status-slate',
            default => 'kr-status-slate',
        };
    @endphp

    <style>
        :root {
            --kr-bg: #001821;
            --kr-panel: rgba(4, 34, 45, .78);
            --kr-panel-2: rgba(7, 43, 55, .64);
            --kr-line: rgba(153, 194, 202, .17);
            --kr-line-strong: rgba(169, 214, 221, .26);
            --kr-text: #f5fbfd;
            --kr-muted: rgba(222, 235, 238, .68);
            --kr-soft: rgba(222, 235, 238, .48);
            --kr-green: #79c843;
            --kr-green-2: #55aa54;
            --kr-teal: #2dd4bf;
            --kr-amber: #f6c343;
            --kr-red: #ef6262;
        }

        body {
            background:
                radial-gradient(circle at 82% 0%, rgba(121, 200, 67, .16), transparent 28%),
                radial-gradient(circle at 8% 22%, rgba(45, 212, 191, .10), transparent 30%),
                linear-gradient(135deg, #00121a, var(--kr-bg) 48%, #042d31) !important;
            color: var(--kr-text);
        }

        .kr-shell {
            width: min(1500px, calc(100% - 48px));
            margin: 0 auto;
        }

        .kr-card {
            background: linear-gradient(145deg, rgba(4, 35, 46, .86), rgba(2, 25, 34, .74));
            border: 1px solid var(--kr-line);
            border-radius: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.025), 0 24px 80px rgba(0,0,0,.18);
            backdrop-filter: blur(16px);
            color: var(--kr-text);
        }

        .kr-green-text { color: var(--kr-green); }

        .kr-layout {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 20px;
            align-items: start;
        }

        .kr-hero {
            margin-bottom: 20px;
            padding: 24px 28px;
        }

        .kr-hero-grid {
            display: grid;
            grid-template-columns: 74px minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
        }

        .kr-filter-grid {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) 180px 170px 110px;
            gap: 14px;
            align-items: end;
        }

        .kr-stats-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
        }

        .kr-stat-card {
            min-height: 92px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .kr-input {
            border: 1px solid var(--kr-line-strong);
            background: rgba(2, 24, 33, .72);
            color: var(--kr-text);
            outline: none;
        }

        .kr-input:focus {
            border-color: rgba(121, 200, 67, .42);
            box-shadow: 0 0 0 3px rgba(121, 200, 67, .08);
        }

        .kr-primary {
            background: linear-gradient(135deg, var(--kr-green-2), var(--kr-green));
            color: #f8fff5;
        }

        .kr-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
        }

        .kr-icon-green { background: rgba(121, 200, 67, .18); color: var(--kr-green); }
        .kr-icon-teal { background: rgba(45, 212, 191, .14); color: var(--kr-teal); }
        .kr-icon-amber { background: rgba(246, 195, 67, .15); color: var(--kr-amber); }
        .kr-icon-violet { background: rgba(120, 105, 240, .18); color: #9ca3ff; }

        .kr-referral-row {
            border-top: 1px solid rgba(153, 194, 202, .12);
            position: relative;
            padding: 18px 20px 18px 76px !important;
        }

        .kr-referral-row::before {
            content: "";
            position: absolute;
            left: 34px;
            top: 20px;
            bottom: 20px;
            width: 3px;
            border-radius: 999px;
            background: linear-gradient(var(--kr-green), #6d7df0);
        }

        .kr-referral-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 210px 150px;
            gap: 18px;
            align-items: center;
        }

        .kr-referral-action {
            justify-self: end;
            position: relative;
        }

        .kr-referral-action form {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            z-index: 20;
            width: 300px;
        }

        .kr-referral-archive::before {
            background: linear-gradient(#6d7df0, #7b8794);
        }

        .kr-status {
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .kr-status-blue { background: rgba(113, 151, 255, .18); color: #adc0ff; }
        .kr-status-green { background: rgba(121, 200, 67, .22); color: #a7ea76; }
        .kr-status-red { background: rgba(239, 98, 98, .16); color: #ff8888; }
        .kr-status-slate { background: rgba(148, 163, 184, .18); color: #d0dae4; }

        .kr-priority-high { background: rgba(239, 98, 98, .16); color: #ff8888; }
        .kr-priority-medium { background: rgba(246, 195, 67, .16); color: #ffd56c; }
        .kr-priority-low { background: rgba(121, 200, 67, .16); color: #a7ea76; }

        @media (max-width: 1180px) {
            .kr-layout { grid-template-columns: 280px minmax(0, 1fr); gap: 16px; }
            .kr-filter-grid { grid-template-columns: minmax(220px, 1fr) 150px 145px 96px; }
            .kr-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .kr-referral-grid { grid-template-columns: minmax(0, 1fr); }
            .kr-referral-action { justify-self: stretch; }
            .kr-referral-action form { position: static; width: 100%; }
        }

        @media (max-width: 760px) {
            .kr-shell { width: min(100% - 28px, 760px); }
            .kr-layout,
            .kr-hero-grid,
            .kr-filter-grid,
            .kr-stats-grid {
                grid-template-columns: 1fr;
            }

            .kr-hero { padding: 20px; }
            .kr-referral-row { padding-left: 54px !important; }
            .kr-referral-row::before { left: 22px; }
        }
    </style>

    <div class="kr-shell py-7">
        <header class="kr-card kr-hero">
            <div class="kr-hero-grid">
                <div class="kr-stat-icon kr-icon-green h-[74px] w-[74px]">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.47 6.03.88-4.36 4.25 1.03 6-5.4-2.84-5.4 2.84 1.03-6-4.36-4.25 6.03-.88L12 3Z"/><path d="M9 12l2 2 4-5"/></svg>
                </div>

                <div>
                    <p class="kr-green-text text-sm font-semibold uppercase tracking-[.35em]">Referenze business</p>
                    <h1 class="mt-3 text-3xl font-semibold text-white">Opportunità e introduzioni tra membri</h1>
                    <p class="mt-2 text-base" style="color: var(--kr-muted);">Invia opportunità, traccia lo stato e tieni ordinata la pipeline relazionale della community.</p>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['sent'] }}</strong> <span style="color: var(--kr-muted);">Inviate</span></div>
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['received'] }}</strong> <span style="color: var(--kr-muted);">Ricevute</span></div>
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">{{ $summary['open'] }}</strong> <span style="color: var(--kr-muted);">Aperte</span></div>
                        <div class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm"><strong class="text-white">€{{ number_format($summary['value'], 0, ',', '.') }}</strong> <span style="color: var(--kr-muted);">Pipeline</span></div>
                    </div>
                </div>

                <div class="text-right text-sm" style="color: var(--kr-muted);">
                    <div class="kr-green-text text-2xl tracking-[.12em]">★★★★★</div>
                    <div>Referenze qualificate dalla rete Kommunity</div>
                </div>
            </div>
        </header>

        @if (session('status') === 'referral-created')
            <div class="mb-5 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">Referenza inviata correttamente.</div>
        @elseif (session('status') === 'referral-updated')
            <div class="mb-5 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">Stato referenza aggiornato.</div>
        @endif

        <main class="kr-layout">
            <aside class="kr-card p-5">
                <h2 class="text-xl font-semibold text-white">Nuova referenza</h2>
                <p class="mt-3 text-sm leading-6" style="color: var(--kr-muted);">Puoi inviare una referenza solo a membri con cui hai un one-to-one completato e confermato da entrambi.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">
                        <p class="font-semibold">Controlla i dati inseriti.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($members->isEmpty())
                    <div class="mt-5 flex min-h-[250px] flex-col items-center justify-center rounded-xl border border-dashed border-white/20 bg-white/[.025] px-6 text-center">
                        <svg class="kr-green-text" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                        <h3 class="mt-4 font-semibold text-white">Nessun membro idoneo</h3>
                        <p class="mt-3 text-sm leading-6" style="color: var(--kr-muted);">Completa e conferma un one-to-one da entrambe le parti per poter inviare una referenza.</p>
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
                        <input type="hidden" name="priority" value="3">
                        <button type="submit" class="kr-primary h-12 w-full rounded-xl font-semibold">Invia referenza</button>
                    </form>
                @endif
            </aside>

            <section class="min-w-0 space-y-5">
                <section class="kr-card p-5">
                    <form method="GET" action="{{ route('referrals.index') }}" class="kr-filter-grid">
                        <label>
                            <span class="mb-2 block text-sm text-white">Cerca</span>
                            <span class="relative block">
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="kr-input h-14 w-full rounded-xl px-4 pr-12" placeholder="Titolo, azienda, contatto...">
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            </span>
                        </label>

                        <label>
                            <span class="mb-2 block text-sm text-white">Stato</span>
                            <select name="status" class="kr-input h-14 w-full rounded-xl px-4">
                                <option value="">Tutti gli stati</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div>
                            <span class="mb-2 block text-sm text-white">Priorità min.</span>
                            <div id="kr-filter-stars" style="display:flex;gap:.25rem;margin-top:.35rem;">
                                @for ($s = 1; $s <= 5; $s++)
                                    <button type="button" data-val="{{ $s }}"
                                            onclick="krSetStar({{ $s }},'kr-filter-stars','kr-filter-priority')"
                                            style="background:none;border:none;cursor:pointer;padding:.1rem;">
                                        <svg width="24" height="24" viewBox="0 0 24 24"
                                             fill="{{ ($filters['priority'] ?? '0') >= $s ? '#FCD34D' : 'none' }}"
                                             stroke="#FCD34D" stroke-width="1.8" data-idx="{{ $s }}">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    </button>
                                @endfor
                                <button type="button" onclick="krClearStar('kr-filter-stars','kr-filter-priority')"
                                        style="background:none;border:none;cursor:pointer;font-size:.72rem;font-weight:700;color:rgba(222,235,238,.45);padding:.1rem .4rem;">✕</button>
                            </div>
                            <input type="hidden" name="priority" id="kr-filter-priority" value="{{ $filters['priority'] ?? '' }}">
                        </div>

                        <button type="submit" class="kr-primary h-14 rounded-xl px-6 font-semibold">Filtra</button>
                    </form>
                </section>

                <section class="kr-stats-grid">
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-green">▣</div><div><div class="text-sm text-white">Tutte le referenze</div><div class="text-2xl font-semibold">{{ $summary['sent'] + $summary['received'] }}</div><div style="color: var(--kr-muted);">Totali</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-teal">♙</div><div><div class="text-sm text-white">Inviate</div><div class="text-2xl font-semibold">{{ $summary['sent'] }}</div><div style="color: var(--kr-muted);">0%</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-teal">♧</div><div><div class="text-sm text-white">Ricevute</div><div class="text-2xl font-semibold">{{ $summary['received'] }}</div><div style="color: var(--kr-muted);">{{ $receivedPercent }}%</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-amber">▱</div><div><div class="text-sm text-white">Aperte</div><div class="text-2xl font-semibold">{{ $summary['open'] }}</div><div style="color: var(--kr-muted);">{{ $openPercent }}%</div></div></div>
                    <div class="kr-card kr-stat-card"><div class="kr-stat-icon kr-icon-violet">▤</div><div><div class="text-sm text-white">Pipeline</div><div class="text-2xl font-semibold">€{{ number_format($summary['value'], 0, ',', '.') }}</div><div style="color: var(--kr-muted);">Valore potenziale</div></div></div>
                </section>

                <section class="kr-card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Referenze ricevute</h2>
                        <div class="flex items-center gap-3 text-sm" style="color: var(--kr-muted);">
                            <span>Ordina per</span>
                            <button class="rounded-full border border-white/10 px-4 py-2 text-white" type="button">Piu recenti</button>
                        </div>
                    </div>

                    @forelse ($receivedOpen as $referral)
                        @php $actor = $referral->sender; @endphp
                        <div class="kr-referral-row">
                            <div class="kr-referral-grid">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="kr-status {{ $statusClass($referral->status) }}">{{ $referral->status->label() }}</span>
                                        @php $stars = $priorityStars($referral->priority); @endphp
                                        <span style="display:inline-flex;gap:.1rem;align-items:center;">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $s <= $stars ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            @endfor
                                        </span>
                                    </div>
                                    <h3 class="mt-3 text-lg font-semibold text-white">{{ $referral->title }}</h3>
                                    <div class="mt-1 flex items-center gap-1.5 text-sm">
                                        @php $stars = $priorityStars($referral->priority); @endphp
                                        @for ($s = 1; $s <= 5; $s++)
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $s <= $stars ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @endfor
                                        <span style="color: var(--kr-muted);">Da {{ $actor?->name ?? 'Utente eliminato' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--kr-muted);">{{ $referral->description }}</p>
                                    <p class="mt-2 text-sm" style="color: var(--kr-soft);">{{ $referral->company_name ?: 'Azienda non indicata' }} · {{ $referral->contact_name ?: 'Contatto non indicato' }} · {{ $referral->created_at->format('d F Y') }}</p>
                                </div>

                                <div class="text-sm" style="color: var(--kr-muted);">
                                    <div class="flex items-center gap-3"><span>▣</span><span>Ultimo aggiornamento<br>{{ $referral->updated_at->format('d/m/Y H:i') }}</span></div>
                                </div>

                                <button type="button"
                                    class="kr-referral-action rounded-xl border border-white/10 px-6 py-3 text-sm font-semibold text-white"
                                    data-action="{{ route('referrals.status', $referral) }}"
                                    data-title="{{ e($referral->title) }}"
                                    data-status="{{ $referral->status->value }}"
                                    data-outcome="{{ e($referral->outcome) }}"
                                    data-notes="{{ e($referral->notes) }}"
                                    data-description="{{ e($referral->description) }}"
                                    data-company="{{ e($referral->company_name) }}"
                                    data-contact="{{ e($referral->contact_name) }}"
                                    data-sender="{{ e($referral->sender?->name ?? 'Utente eliminato') }}"
                                    data-date="{{ $referral->created_at->format('d F Y') }}"
                                    onclick="openReferralModal(this)">
                                    ▶ Visualizza
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm" style="color: var(--kr-muted);">Nessuna referenza ricevuta.</div>
                    @endforelse
                </section>

                <section class="kr-card overflow-hidden">
                    <div class="border-b border-white/10 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white">Archivio</h2>
                    </div>

                    @forelse ($visibleArchive as $referral)
                        @php $actor = $referral->sender_id === auth()->id() ? $referral->recipient : $referral->sender; @endphp
                        <div class="kr-referral-row kr-referral-archive">
                            <div class="kr-referral-grid">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="kr-status {{ $statusClass($referral->status) }}">{{ $referral->status->label() }}</span>
                                    </div>
                                    <h3 class="mt-3 text-lg font-semibold text-white">{{ $referral->title }}</h3>
                                    <div class="mt-1 flex items-center gap-1.5 text-sm">
                                        @php $stars = $priorityStars($referral->priority); @endphp
                                        @for ($s = 1; $s <= 5; $s++)
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $s <= $stars ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @endfor
                                        <span style="color: var(--kr-muted);">Da {{ $actor?->name ?? 'Utente eliminato' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--kr-muted);">{{ $referral->description }}</p>
                                    <p class="mt-2 text-sm" style="color: var(--kr-soft);">{{ $referral->company_name ?: 'Azienda non indicata' }} · {{ $referral->contact_name ?: 'Contatto non indicato' }} · {{ $referral->created_at->format('d F Y') }}</p>
                                </div>
                                <div class="text-sm" style="color: var(--kr-muted);">Ultimo aggiornamento<br>{{ $referral->updated_at->format('d/m/Y H:i') }}</div>
                                <button type="button" class="kr-referral-action rounded-xl border border-white/10 px-6 py-3 text-sm font-semibold text-white">Visualizza</button>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm" style="color: var(--kr-muted);">Nessuna referenza in archivio.</div>
                    @endforelse
                </section>
            </section>
        </main>
    </div>

    {{-- ── MODAL REFERENZA ─────────────────────────────────────── --}}
    <div id="kr-modal-backdrop" style="display:none; position:fixed; inset:0; z-index:100; background:rgba(0,0,0,.65); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:16px;">
        <div class="kr-card w-full p-7" style="max-width:520px; max-height:90vh; overflow-y:auto; position:relative;">
            <button type="button" onclick="closeReferralModal()" style="position:absolute; top:16px; right:18px; background:none; border:none; color:rgba(255,255,255,.45); font-size:22px; cursor:pointer; line-height:1;" title="Chiudi">✕</button>

            <p id="kr-modal-sender" class="text-xs font-semibold uppercase tracking-widest" style="color: var(--kr-green);"></p>
            <h2 id="kr-modal-title" class="mt-2 text-xl font-semibold text-white" style="padding-right:32px;"></h2>
            <p id="kr-modal-meta" class="mt-1 text-sm" style="color: var(--kr-soft);"></p>
            <p id="kr-modal-desc" class="mt-3 text-sm leading-6" style="color: var(--kr-muted);"></p>

            <div style="border-top:1px solid rgba(153,194,202,.18); margin:20px 0;"></div>

            <form id="kr-modal-form" method="POST" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <label class="mb-1 block text-sm text-white">Stato</label>
                    <select id="kr-modal-status" name="status" class="kr-input h-11 w-full rounded-lg px-3">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm text-white">Esito</label>
                    <textarea id="kr-modal-outcome" name="outcome" rows="2" class="kr-input w-full rounded-lg px-3 py-2" placeholder="Esito dell'opportunità..."></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm text-white">Note</label>
                    <textarea id="kr-modal-notes" name="notes" rows="2" class="kr-input w-full rounded-lg px-3 py-2" placeholder="Note interne..."></textarea>
                </div>
                <button type="submit" class="kr-primary h-11 w-full rounded-xl text-sm font-semibold">Salva modifiche</button>
            </form>
        </div>
    </div>

    <script>
        // ── Star rating helper (referral priority) ─────────────────────────
        function krSetStar(val, groupId, inputId) {
            const group = document.getElementById(groupId);
            const input = document.getElementById(inputId);
            if (!group || !input) return;
            input.value = val;
            group.querySelectorAll('svg').forEach(svg => {
                const idx = parseInt(svg.dataset.idx, 10);
                svg.setAttribute('fill', idx <= val ? '#FCD34D' : 'none');
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
                const input = document.getElementById(iId);
                if (input && input.value) krSetStar(parseInt(input.value, 10), gId, iId);
            });
        });

        function decodeHtml(str) {
            const el = document.createElement('textarea');
            el.innerHTML = str;
            return el.value;
        }
        function openReferralModal(btn) {
            document.getElementById('kr-modal-form').action = btn.dataset.action;
            document.getElementById('kr-modal-title').textContent = decodeHtml(btn.dataset.title);
            document.getElementById('kr-modal-sender').textContent = 'Da ' + decodeHtml(btn.dataset.sender);
            document.getElementById('kr-modal-desc').textContent = decodeHtml(btn.dataset.description || '');
            document.getElementById('kr-modal-meta').textContent =
                (btn.dataset.company || 'Azienda non indicata') + ' · ' +
                (btn.dataset.contact || 'Contatto non indicato') + ' · ' +
                btn.dataset.date;
            document.getElementById('kr-modal-status').value = btn.dataset.status;
            document.getElementById('kr-modal-outcome').value = btn.dataset.outcome || '';
            document.getElementById('kr-modal-notes').value = btn.dataset.notes || '';
            const bd = document.getElementById('kr-modal-backdrop');
            bd.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeReferralModal() {
            document.getElementById('kr-modal-backdrop').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('kr-modal-backdrop').addEventListener('click', function(e) {
            if (e.target === this) closeReferralModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeReferralModal();
        });
    </script>
</x-app-layout>
