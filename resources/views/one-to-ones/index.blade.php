<x-app-layout>
    @push('body-class') km-bg-dark @endpush

    @push('styles')
        <style>
            .km-oto-hero::before{
                content:"1:1";
                position:absolute;
                right:clamp(1rem,4vw,4rem);
                top:-3.4rem;
                color:rgba(255,255,255,.035);
                font-size:clamp(5.5rem,13vw,12rem);
                font-weight:900;
                letter-spacing:-.08em;
                pointer-events:none;
            }
            .km-oto-layout{
                display:grid;
                grid-template-columns:minmax(0,1fr) 23rem;
                gap:1rem;
                align-items:start;
            }
            .km-oto-status{
                display:inline-flex;
                align-items:center;
                gap:.35rem;
                border-radius:999px;
                border:1px solid var(--status-border);
                background:var(--status-bg);
                color:var(--status-color);
                padding:.28rem .68rem;
                font-size:.66rem;
                font-weight:900;
                letter-spacing:.12em;
                line-height:1;
                text-transform:uppercase;
                white-space:nowrap;
            }
            .km-oto-status::before{
                content:"";
                width:.38rem;
                height:.38rem;
                border-radius:999px;
                background:currentColor;
                box-shadow:0 0 12px currentColor;
            }
            .km-oto-table{
                width:100%;
                table-layout:fixed;
                border-collapse:separate;
                border-spacing:0 .55rem;
            }
            .km-oto-table th{
                padding:.2rem 1rem .45rem;
                color:rgba(255,255,255,.45);
                font-size:.64rem;
                font-weight:900;
                letter-spacing:.18em;
                text-align:left;
                text-transform:uppercase;
            }
            .km-oto-table td{
                padding:.9rem .8rem;
                background:rgba(255,255,255,.035);
                border-top:1px solid rgba(255,255,255,.07);
                border-bottom:1px solid rgba(255,255,255,.07);
                color:var(--km-text);
                vertical-align:middle;
            }
            .km-oto-table td:first-child{
                border-left:1px solid rgba(255,255,255,.07);
                border-radius:1.05rem 0 0 1.05rem;
            }
            .km-oto-table td:last-child{
                border-right:1px solid rgba(255,255,255,.07);
                border-radius:0 1.05rem 1.05rem 0;
            }
            .km-oto-table tr:hover td{
                background:rgba(255,255,255,.06);
                border-color:rgba(139,197,63,.22);
            }
            .km-oto-goal{
                display:-webkit-box;
                -webkit-line-clamp:2;
                -webkit-box-orient:vertical;
                overflow:hidden;
                white-space:normal;
                word-break:break-word;
                line-height:1.45;
            }
            .km-oto-avatar{
                width:2.25rem;
                height:2.25rem;
                flex-shrink:0;
                overflow:hidden;
                border-radius:999px;
                border:1px solid rgba(255,255,255,.18);
                background:linear-gradient(145deg, rgba(139,197,63,.18), rgba(45,212,191,.10));
                box-shadow:0 0 22px rgba(139,197,63,.10);
            }
            .km-oto-avatar img{
                width:100%;
                height:100%;
                object-fit:cover;
            }
            .km-oto-avatar-fallback{
                display:flex;
                width:100%;
                height:100%;
                align-items:center;
                justify-content:center;
                color:var(--km-green-2);
                font-size:.82rem;
                font-weight:900;
            }
            .km-oto-filter-menu{
                position:absolute;
                right:0;
                top:calc(100% + .65rem);
                z-index:30;
                width:min(58rem, calc(100vw - 2rem));
                background:#061923;
                border:1px solid rgba(139,197,63,.22);
                box-shadow:0 28px 90px rgba(0,0,0,.72), 0 0 0 1px rgba(255,255,255,.04) inset;
            }
            .km-oto-filter-summary::-webkit-details-marker{display:none;}
            .km-oto-filter-menu .km-dark-input{
                background:#102832;
                border-color:rgba(255,255,255,.16);
            }
            .km-oto-filter-menu .km-dark-input:focus{
                border-color:rgba(139,197,63,.55);
                box-shadow:0 0 0 3px rgba(139,197,63,.10);
            }
            .km-oto-filter-menu .km-dark-input option{
                background:#102832;
                color:var(--km-text);
            }
            .km-oto-filter-form{
                display:grid;
                grid-template-columns:repeat(6,minmax(0,1fr));
                gap:.85rem;
                align-items:end;
            }
            .km-oto-mobile-list{display:none;}
            .km-dark-modal{
                background:linear-gradient(135deg,#031822,#052532);
                border:1px solid rgba(255,255,255,.12);
                box-shadow:0 30px 80px rgba(0,0,0,.65);
            }
            .km-oto-detail-modal{
                position:fixed;
                inset:1rem;
                z-index:50;
                margin:auto;
                width:min(980px, calc(100% - 2rem));
                max-height:calc(100vh - 2rem);
                overflow:hidden;
                border-radius:1.5rem;
                display:flex;
                flex-direction:column;
            }
            .km-oto-detail-body{
                padding:1rem;
                overflow-y:auto;
                display:flex;
                flex-direction:column;
                gap:.8rem;
            }
            .km-oto-detail-grid{
                display:grid;
                gap:.7rem;
                grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
            }
            .km-oto-detail-form{
                padding:1rem!important;
                gap:.65rem!important;
            }
            @media (max-width:1180px){
                .km-oto-layout{grid-template-columns:1fr;}
                .km-oto-availability{position:static!important;}
            }
            @media (max-width:767px){
                .km-oto-table-wrap{display:none;}
                .km-oto-mobile-list{display:grid;gap:.75rem;}
                .km-oto-hero::before{top:-1.8rem;right:.75rem;}
                .km-oto-filter-menu{position:static;width:100%;margin-top:.75rem;}
                .km-oto-filter-form{grid-template-columns:1fr;}
            }
            @media (max-width:640px){
                .km-oto-modal-grid{grid-template-columns:1fr!important;}
                .km-oto-modal-sidebar{border-right:0!important;border-bottom:1px solid var(--km-line-dark);}
            }
        </style>
    @endpush

    {{-- $memberSearchItems rimosso: la ricerca avviene via AJAX /members/search --}}

    <main class="km-shell-wide space-y-4 py-5 sm:py-6">
        <section class="km-dark-panel km-oto-hero px-5 py-4 sm:px-6">
            <div class="relative z-[1] flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <p class="km-eyebrow">One-to-one · {{ auth()->user()->memberProfile?->chapter?->name ?? 'Kommunity' }}</p>
                    <h1 class="km-display mt-1 text-2xl text-white sm:text-3xl">Agenda relazionale</h1>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <div class="grid grid-cols-3 gap-2 sm:min-w-[23rem]">
                        <div class="rounded-2xl border border-white/[.08] bg-white/[.045] px-3 py-2">
                            <p class="text-[9px] font-black uppercase tracking-[.16em] text-white/45">Totale</p>
                            <p class="mt-0.5 text-xl font-black text-white">{{ $summary['total'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-[rgba(139,197,63,.18)] bg-[rgba(139,197,63,.07)] px-3 py-2">
                            <p class="text-[9px] font-black uppercase tracking-[.16em] text-white/45">Ricevuti</p>
                            <p class="mt-0.5 text-xl font-black text-[color:var(--km-green-2)]">{{ $summary['received'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-[rgba(45,212,191,.18)] bg-[rgba(45,212,191,.07)] px-3 py-2">
                            <p class="text-[9px] font-black uppercase tracking-[.16em] text-white/45">Inviati</p>
                            <p class="mt-0.5 text-xl font-black text-[#5EEAD4]">{{ $summary['sent'] }}</p>
                        </div>
                    </div>
                    <button type="button" id="open-one-to-one-create-modal" class="km-cta-primary justify-center text-sm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Nuova richiesta
                    </button>
                </div>
            </div>
        </section>

        <div class="km-oto-layout">
            <section class="min-w-0 space-y-4">
                @if ($selectedMember)
                    <div class="km-glass-box flex flex-col gap-2 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-white/80">Vista filtrata tra te e <strong class="text-white">{{ $selectedMember->name }}</strong>.</span>
                        <a href="{{ route('one-to-ones.index') }}" class="text-xs font-black uppercase tracking-[.14em] text-[color:var(--km-green-2)] hover:underline">Rimuovi filtro</a>
                    </div>
                @endif

                <section class="km-dark-card p-4 sm:p-5">
                    <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="km-eyebrow">Richieste one-to-one</p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-white">Ricevute e inviate</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                            <p class="text-xs text-white/45">Mostra {{ $requests->firstItem() ?? 0 }}-{{ $requests->lastItem() ?? 0 }} di {{ $requests->total() }}</p>
                            <a href="{{ route('one-to-ones.index') }}" class="rounded-full border border-white/[.10] bg-white/[.04] px-3 py-2 text-xs font-bold text-white/70 transition hover:border-[rgba(139,197,63,.35)]">Reset</a>
                            <details class="relative">
                                <summary class="km-oto-filter-summary inline-flex cursor-pointer list-none items-center gap-2 rounded-full border border-[rgba(139,197,63,.28)] bg-[rgba(139,197,63,.09)] px-3 py-2 text-xs font-black text-[color:var(--km-green-2)] transition hover:border-[rgba(139,197,63,.46)]">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                                    Filtri
                                </summary>
                                <div class="km-oto-filter-menu rounded-2xl p-4">
                                    <form method="GET" class="km-oto-filter-form">
                                        <label class="col-span-6 md:col-span-3">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">Cerca</span>
                                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-dark-input" placeholder="Membro o obiettivo">
                                        </label>
                                        <label class="col-span-6 md:col-span-3">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">Membro</span>
                                            <select name="member" class="km-dark-input">
                                                <option value="">Tutti</option>
                                                @foreach ($members as $member)
                                                    <option value="{{ $member->id }}" @selected((string)($filters['member'] ?? '') === (string)$member->id)>{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="col-span-6 md:col-span-2">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">Tipo</span>
                                            <select name="type" class="km-dark-input">
                                                <option value="">Tutti</option>
                                                @foreach ($typeOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(($filters['type'] ?? null) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="col-span-6 md:col-span-2">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">Stato</span>
                                            <select name="status" class="km-dark-input">
                                                <option value="">Tutti</option>
                                                @foreach ($statusOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="col-span-6 md:col-span-2">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">Modalita'</span>
                                            <select name="meeting_mode" class="km-dark-input">
                                                <option value="">Tutte</option>
                                                @foreach ($modeOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected(($filters['meeting_mode'] ?? null) === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="col-span-6 md:col-span-2">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">Da</span>
                                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="km-dark-input">
                                        </label>
                                        <label class="col-span-6 md:col-span-2">
                                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-[.16em] text-white/45">A</span>
                                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="km-dark-input">
                                        </label>
                                        <div class="col-span-6 flex items-end gap-2 md:col-span-2">
                                            <button type="submit" class="km-button-primary flex-1">Applica filtri</button>
                                            <a href="{{ route('one-to-ones.index') }}" class="km-button-secondary">Reset</a>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="km-oto-table-wrap">
                        <table class="km-oto-table">
                            <colgroup>
                                <col style="width:8%">
                                <col style="width:13%">
                                <col style="width:27%">
                                <col style="width:12%">
                                <col style="width:10%">
                                <col style="width:13%">
                                <col style="width:17%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Membro</th>
                                    <th>Obiettivo</th>
                                    <th>Data</th>
                                    <th>Modalita'</th>
                                    <th>Stato</th>
                                    <th class="text-right">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $requestItem)
                                    @php
                                        $sv = $requestItem->status->value;
                                        $statusVars = match($sv) {
                                            'pending', 'rescheduled' => '--status-bg:rgba(245,158,11,.14);--status-color:#FCD34D;--status-border:rgba(245,158,11,.32);',
                                            'accepted' => '--status-bg:rgba(139,197,63,.14);--status-color:#9AD84A;--status-border:rgba(139,197,63,.32);',
                                            'declined' => '--status-bg:rgba(244,63,94,.14);--status-color:#FDA4AF;--status-border:rgba(244,63,94,.32);',
                                            'cancelled' => '--status-bg:rgba(148,163,184,.10);--status-color:#94A3B8;--status-border:rgba(148,163,184,.22);',
                                            'completed' => '--status-bg:rgba(45,212,191,.14);--status-color:#5EEAD4;--status-border:rgba(45,212,191,.30);',
                                            default => '--status-bg:rgba(96,165,250,.14);--status-color:#93C5FD;--status-border:rgba(96,165,250,.30);',
                                        };
                                        $isOpen = (int)($filters['request'] ?? 0) === $requestItem->id;
                                        $isReceived = $requestItem->recipient_id === auth()->id();
                                        $isPending  = in_array($requestItem->status->value, ['pending','rescheduled'], true);
                                        $counterpartUser = $requestItem->requester_id === auth()->id()
                                            ? $requestItem->recipient
                                            : $requestItem->requester;
                                        $counterpartName = $counterpartUser?->name ?? 'Utente eliminato';
                                        $counterpartAvatar = $counterpartUser?->memberProfile?->avatarUrl();
                                        $counterpartInitial = \Illuminate\Support\Str::of($counterpartName)->substr(0, 1)->upper();
                                        $detailUrl = $isOpen
                                            ? route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id]))
                                            : route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id,'request'=>$requestItem->id]));
                                    @endphp
                                    <tr>
                                        <td><span class="text-[10px] font-black uppercase tracking-[.16em] {{ $isReceived ? 'text-[color:var(--km-green-2)]' : 'text-[#5EEAD4]' }}">{{ $isReceived ? 'Ricevuta' : 'Inviata' }}</span></td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="km-oto-avatar">
                                                    @if ($counterpartAvatar)
                                                        <img src="{{ $counterpartAvatar }}" alt="{{ $counterpartName }}">
                                                    @else
                                                        <span class="km-oto-avatar-fallback">{{ $counterpartInitial }}</span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-black text-white">{{ $counterpartName }}</p>
                                                    <p class="mt-0.5 text-xs text-white/45">
                                                        @php
                                                            $subtitleLabel = match($requestItem->status->value) {
                                                                'pending'     => $isReceived ? 'Da gestire'       : 'In attesa di risposta',
                                                                'rescheduled' => $isReceived ? 'Nuova proposta'   : 'Riprogrammazione inviata',
                                                                'accepted'    => 'Incontro confermato',
                                                                'declined'    => $isReceived ? 'Hai rifiutato'    : 'Richiesta rifiutata',
                                                                'cancelled'   => $isReceived ? 'Annullata dall\'altro' : 'Annullata da te',
                                                                'completed'   => 'Completato',
                                                                default       => $isReceived ? 'Ricevuta'         : 'Inviata',
                                                            };
                                                        @endphp
                                                        {{ $subtitleLabel }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><p class="km-oto-goal text-sm text-white/75">{{ $requestItem->goal ?: 'Obiettivo non indicato' }}</p></td>
                                        <td><p class="text-sm text-white/65">{{ optional($requestItem->requested_at)->format('d/m/Y') ?: 'Da confermare' }}</p><p class="text-xs text-white/40">{{ optional($requestItem->requested_at)->format('H:i') }}</p></td>
                                        <td><span class="rounded-full border border-white/[.10] bg-white/[.04] px-2.5 py-1 text-xs font-bold text-white/70">{{ $requestItem->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</span></td>
                                        <td><span class="km-oto-status" style="{{ $statusVars }}">{{ $requestItem->status->label() }}</span></td>
                                        <td>
                                            <div class="flex flex-wrap justify-end gap-1.5">
                                                @if ($requestItem->canRespondTo(auth()->id()))
                                                    <form method="POST" action="{{ route('one-to-ones.status', $requestItem) }}" class="m-0">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="accepted">
                                                        <button type="submit" class="rounded-full border border-[rgba(139,197,63,.42)] bg-[rgba(139,197,63,.12)] px-2.5 py-1.5 text-xs font-black text-[color:var(--km-green-2)]">Accetta</button>
                                                    </form>
                                                @endif
                                                <a href="{{ $detailUrl }}" class="rounded-full border px-2.5 py-1.5 text-xs font-black transition {{ $isOpen ? 'border-[rgba(139,197,63,.5)] bg-[rgba(139,197,63,.10)] text-[color:var(--km-green-2)]' : 'border-white/[.12] bg-white/[.04] text-white/80 hover:border-[rgba(139,197,63,.35)]' }}">
                                                    {{ $isOpen ? 'Chiudi' : 'Dettagli' }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="rounded-2xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-10 text-center text-sm text-white/55">
                                            Nessuna richiesta trovata. Crea un nuovo one-to-one o rimuovi i filtri.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="km-oto-mobile-list">
                        @forelse ($requests as $requestItem)
                            @php
                                $sv = $requestItem->status->value;
                                $statusVars = match($sv) {
                                    'pending', 'rescheduled' => '--status-bg:rgba(245,158,11,.14);--status-color:#FCD34D;--status-border:rgba(245,158,11,.32);',
                                    'accepted' => '--status-bg:rgba(139,197,63,.14);--status-color:#9AD84A;--status-border:rgba(139,197,63,.32);',
                                    'declined' => '--status-bg:rgba(244,63,94,.14);--status-color:#FDA4AF;--status-border:rgba(244,63,94,.32);',
                                    'cancelled' => '--status-bg:rgba(148,163,184,.10);--status-color:#94A3B8;--status-border:rgba(148,163,184,.22);',
                                    'completed' => '--status-bg:rgba(45,212,191,.14);--status-color:#5EEAD4;--status-border:rgba(45,212,191,.30);',
                                    default => '--status-bg:rgba(96,165,250,.14);--status-color:#93C5FD;--status-border:rgba(96,165,250,.30);',
                                };
                                $isOpen = (int)($filters['request'] ?? 0) === $requestItem->id;
                                $isReceived = $requestItem->recipient_id === auth()->id();
                                $isPending  = in_array($requestItem->status->value, ['pending','rescheduled'], true);
                                $counterpartUser = $requestItem->requester_id === auth()->id()
                                    ? $requestItem->recipient
                                    : $requestItem->requester;
                                $counterpartName = $counterpartUser?->name ?? 'Utente eliminato';
                                $counterpartAvatar = $counterpartUser?->memberProfile?->avatarUrl();
                                $counterpartInitial = \Illuminate\Support\Str::of($counterpartName)->substr(0, 1)->upper();
                                $detailUrl = $isOpen
                                    ? route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id]))
                                    : route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id,'request'=>$requestItem->id]));
                            @endphp
                            <article class="rounded-2xl border border-white/[.08] bg-white/[.04] p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="km-oto-avatar">
                                            @if ($counterpartAvatar)
                                                <img src="{{ $counterpartAvatar }}" alt="{{ $counterpartName }}">
                                            @else
                                                <span class="km-oto-avatar-fallback">{{ $counterpartInitial }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-black uppercase tracking-[.16em] {{ $isReceived ? 'text-[color:var(--km-green-2)]' : 'text-[#5EEAD4]' }}">{{ $isReceived ? 'Ricevuta' : 'Inviata' }}</p>
                                            <h3 class="mt-1 truncate text-base font-black text-white">{{ $counterpartName }}</h3>
                                        </div>
                                    </div>
                                    <span class="km-oto-status" style="{{ $statusVars }}">{{ $requestItem->status->label() }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-white/70">{{ $requestItem->goal ?: 'Obiettivo non indicato' }}</p>
                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-white/50">
                                    <span>{{ optional($requestItem->requested_at)->format('d/m/Y H:i') ?: 'Da confermare' }}</span>
                                    <span class="text-right">{{ $requestItem->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</span>
                                </div>
                                <div class="mt-4 flex flex-wrap justify-end gap-2">
                                    @if ($requestItem->canRespondTo(auth()->id()))
                                        <form method="POST" action="{{ route('one-to-ones.status', $requestItem) }}" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="rounded-full border border-[rgba(139,197,63,.42)] bg-[rgba(139,197,63,.12)] px-3 py-1.5 text-xs font-black text-[color:var(--km-green-2)]">Accetta</button>
                                        </form>
                                    @endif
                                    <a href="{{ $detailUrl }}" class="rounded-full border px-3 py-1.5 text-xs font-black transition {{ $isOpen ? 'border-[rgba(139,197,63,.5)] bg-[rgba(139,197,63,.10)] text-[color:var(--km-green-2)]' : 'border-white/[.12] bg-white/[.04] text-white/80 hover:border-[rgba(139,197,63,.35)]' }}">
                                        {{ $isOpen ? 'Chiudi' : 'Dettagli' }}
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/[.10] bg-white/[.02] px-4 py-8 text-center text-sm text-white/55">
                                Nessuna richiesta trovata.
                            </div>
                        @endforelse
                    </div>

                    @if ($requests->hasPages())
                        <div class="mt-4 rounded-2xl border border-white/[.06] bg-white/[.025] px-4 py-3">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </section>
            </section>

            <aside class="km-oto-availability sticky top-5 space-y-4">
                <section class="km-dark-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="km-eyebrow">Le mie disponibilita'</p>
                            <h2 class="mt-1 text-lg font-black tracking-tight text-white">Slot prenotabili</h2>
                        </div>
                        <span class="rounded-full border border-[rgba(139,197,63,.22)] bg-[rgba(139,197,63,.09)] px-2.5 py-1 text-xs font-black text-[color:var(--km-green-2)]">{{ $availabilitySlots->count() }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-white/55">Gli slot attivi rendono piu' immediata la prenotazione e riducono richieste fuori agenda.</p>

                    <div class="mt-4 space-y-2">
                        @forelse ($availabilitySlots as $slot)
                            <div class="rounded-2xl border border-white/[.08] bg-white/[.04] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-white">{{ $weekdayOptions[$slot->weekday] ?? 'Giorno' }}</p>
                                        <p class="mt-1 text-sm text-white/65">{{ substr($slot->starts_at, 0, 5) }} - {{ substr($slot->ends_at, 0, 5) }}</p>
                                        <p class="mt-0.5 text-xs text-white/40">{{ $slot->meeting_mode === 'online' ? 'Online' : 'In presenza' }}{{ $slot->location ? ' · '.$slot->location : '' }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('one-to-ones.availability.destroy', $slot) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-[#FDA4AF]">Elimina</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/[.12] bg-white/[.02] p-4 text-sm leading-6 text-white/55">
                                Nessuna disponibilita' pubblicata. Aggiungi almeno uno slot settimanale.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="km-dark-card p-5">
                    <p class="km-eyebrow">Nuovo slot</p>
                    {{-- FIX #4: slot di esattamente 1 ora — ends_at calcolato automaticamente lato server --}}
                    <form method="POST" action="{{ route('one-to-ones.availability.store') }}" class="mt-4 space-y-3">
                        @csrf
                        <select name="weekday" class="km-dark-input" required>
                            <option value="">Giorno della settimana</option>
                            @foreach ($weekdayOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div>
                            <input type="time" name="starts_at" class="km-dark-input w-full" required placeholder="Ora inizio">
                            <p class="mt-1 text-xs" style="color:var(--km-text-muted);">Lo slot dura sempre 1 ora (es. 10:00 → 11:00)</p>
                        </div>
                        <select name="meeting_mode" class="km-dark-input">
                            <option value="online">Online</option>
                            <option value="in_person">In presenza</option>
                        </select>
                        <input type="text" name="location" class="km-dark-input" placeholder="Luogo o nota logistica">
                        <button type="submit" class="km-button-secondary w-full">Aggiungi disponibilita'</button>
                    </form>
                </section>
            </aside>
        </div>
    </main>

    {{-- DETAIL PANEL --}}
    @if ($selectedRequest)
        @php
            $isRecipient = $selectedRequest->recipient_id === auth()->id();
            $isRequester = $selectedRequest->requester_id === auth()->id();
            $counterpart = $isRequester ? $selectedRequest->recipient : $selectedRequest->requester;
            $privateNote = $selectedRequest->notes->first()?->note;
            $sharedFollowUp = $selectedRequest->followUps->first()?->content ?: $selectedRequest->follow_up_notes;
            $dsv = $selectedRequest->status->value;
            $dBg  = match($dsv) { 'pending'=>'rgba(245,158,11,.15)', 'accepted'=>'rgba(139,197,63,.15)', 'declined'=>'rgba(244,63,94,.15)', 'cancelled'=>'rgba(148,163,184,.10)', 'completed'=>'rgba(45,212,191,.15)', default=>'rgba(96,165,250,.15)' };
            $dCol = match($dsv) { 'pending'=>'#FCD34D', 'accepted'=>'#9AD84A', 'declined'=>'#FDA4AF', 'cancelled'=>'#94A3B8', 'completed'=>'#5EEAD4', default=>'#93C5FD' };
            $dBor = match($dsv) { 'pending'=>'rgba(245,158,11,.30)', 'accepted'=>'rgba(139,197,63,.30)', 'declined'=>'rgba(244,63,94,.30)', 'cancelled'=>'rgba(148,163,184,.20)', 'completed'=>'rgba(45,212,191,.25)', default=>'rgba(96,165,250,.25)' };
        @endphp
        <div style="position:fixed;inset:0;z-index:40;background:rgba(2,11,18,.75);backdrop-filter:blur(4px);">
            <a href="{{ route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id])) }}" style="position:absolute;inset:0;" aria-label="Chiudi"></a>
        </div>
        <div class="km-dark-modal km-oto-detail-modal">
            <div style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--km-line-dark);background:rgba(3,24,34,.92);backdrop-filter:blur(20px);">
                <div>
                    <p class="km-eyebrow">Dettaglio richiesta</p>
                    <h3 style="margin-top:.25rem;font-size:1.05rem;font-weight:800;color:var(--km-text);">Gestione one-to-one</h3>
                </div>
                <a href="{{ route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id])) }}"
                   style="text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:.85rem;font-size:.82rem;font-weight:700;color:#f8fafc;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.22);transition:background .18s;"
                   onmouseover="this.style.background='rgba(255,255,255,.18)'"
                   onmouseout="this.style.background='rgba(255,255,255,.10)'">
                    ← Chiudi
                </a>
            </div>

            <div class="km-oto-detail-body">
                <div>
                    <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                        <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:rgba(255,255,255,.08);color:var(--km-text-muted);border:1px solid var(--km-line-dark);">{{ $isRequester ? 'Inviata' : 'Ricevuta' }}</span>
                        <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:rgba(255,255,255,.08);color:var(--km-text-muted);border:1px solid var(--km-line-dark);">{{ $selectedRequest->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</span>
                        <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:{{ $dBg }};color:{{ $dCol }};border:1px solid {{ $dBor }};">{{ $selectedRequest->status->label() }}</span>
                    </div>
                    <h3 style="margin-top:.65rem;font-size:1.25rem;font-weight:800;color:var(--km-text);">{{ $isRequester ? 'Richiesta a '.$counterpart->name : 'Richiesta da '.$counterpart->name }}</h3>
                    <div style="margin-top:.4rem;display:flex;flex-wrap:wrap;gap:1.25rem;">
                        <span class="km-muted" style="font-size:.8rem;">{{ optional($selectedRequest->requested_at)->format('d/m/Y H:i') ?: 'Data da confermare' }}</span>
                        @if ($counterpart?->memberProfile?->city?->name)
                            <span class="km-muted" style="font-size:.8rem;">{{ $counterpart->memberProfile?->city?->name ?? '' }}</span>
                        @endif
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:.8rem;">
                    {{-- Conferma completamento — visibile a entrambi quando status è accepted --}}
                    @if ($selectedRequest->status->value === 'accepted')
                        @php
                            $iCanConfirm      = $selectedRequest->canBeConfirmedBy(auth()->id());
                            $iAlreadyConfirmed = $selectedRequest->completionConfirmedBy(auth()->id());
                        @endphp
                        @if ($iCanConfirm)
                            <div class="km-glass-box" style="padding:1rem 1.25rem;border-color:rgba(45,212,191,.35);background:rgba(45,212,191,.07);">
                                <p class="km-eyebrow" style="color:#5EEAD4;">Conferma completamento</p>
                                <p style="margin-top:.4rem;font-size:.88rem;line-height:1.55;color:var(--km-text);">
                                    L'incontro si è svolto? Confermalo qui. Quando entrambi i partecipanti confermano, il one-to-one viene segnato come <strong style="color:#5EEAD4;">Completato</strong>.
                                </p>
                                <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" style="margin-top:.85rem;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="confirm_completed" value="1">
                                    <button type="submit" style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.4rem;border-radius:1rem;background:rgba(45,212,191,.22);border:1px solid rgba(45,212,191,.5);color:#5EEAD4;font-size:.85rem;font-weight:700;cursor:pointer;">
                                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
                                        Conferma completamento
                                    </button>
                                </form>
                            </div>
                        @elseif ($iAlreadyConfirmed)
                            <div class="km-glass-box" style="padding:.85rem 1.25rem;border-color:rgba(45,212,191,.18);background:rgba(45,212,191,.04);">
                                <p class="km-eyebrow" style="color:#5EEAD4;">⏳ In attesa dell'altra conferma</p>
                                <p style="margin-top:.3rem;font-size:.82rem;color:var(--km-text-muted);line-height:1.5;">
                                    Hai già confermato il completamento. Appena anche l'altra parte conferma, l'incontro verrà chiuso automaticamente.
                                </p>
                            </div>
                        @endif
                    @endif

                    {{-- Dettagli logistici: data/ora, modalità, link/luogo --}}
                    <div class="km-glass-box" style="padding:.85rem;border-color:rgba(139,197,63,.18);">
                        <p class="km-eyebrow" style="color:var(--km-green-2);">Dettagli incontro</p>
                        <div style="margin-top:.6rem;display:grid;gap:.45rem;">
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                <span style="font-size:.75rem;font-weight:700;color:var(--km-text-muted);min-width:5.5rem;">Data e ora</span>
                                <span style="font-size:.86rem;color:var(--km-text);">
                                    {{ optional($selectedRequest->requested_at)->translatedFormat('l d F Y \a\l\l\e H:i') ?: 'Da concordare' }}
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                <span style="font-size:.75rem;font-weight:700;color:var(--km-text-muted);min-width:5.5rem;">Modalità</span>
                                <span style="font-size:.86rem;color:var(--km-text);">
                                    {{ $selectedRequest->meeting_mode === 'online' ? 'Online' : 'In presenza' }}
                                </span>
                            </div>
                            @if ($selectedRequest->meeting_mode === 'online' && $selectedRequest->meeting_link)
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <span style="font-size:.75rem;font-weight:700;color:var(--km-text-muted);min-width:5.5rem;">Link meeting</span>
                                    <a href="{{ $selectedRequest->meeting_link }}"
                                       target="_blank" rel="noopener noreferrer"
                                       style="font-size:.86rem;color:var(--km-green-2);font-weight:700;word-break:break-all;text-decoration:underline;text-underline-offset:2px;">
                                        {{ $selectedRequest->meeting_link }}
                                        <svg style="display:inline;margin-left:.25rem;vertical-align:middle;" width="12" height="12" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @elseif ($selectedRequest->meeting_mode === 'online' && !$selectedRequest->meeting_link)
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <span style="font-size:.75rem;font-weight:700;color:var(--km-text-muted);min-width:5.5rem;">Link meeting</span>
                                    <span style="font-size:.82rem;color:rgba(253,164,175,.7);font-style:italic;">Non ancora inserito</span>
                                </div>
                            @endif
                            @if ($selectedRequest->meeting_location)
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <span style="font-size:.75rem;font-weight:700;color:var(--km-text-muted);min-width:5.5rem;">Luogo</span>
                                    <span style="font-size:.86rem;color:var(--km-text);">{{ $selectedRequest->meeting_location }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="km-oto-detail-grid">
                        <div class="km-glass-box" style="padding:.85rem;">
                            <p class="km-eyebrow">Obiettivo incontro</p>
                            <p style="margin-top:.4rem;font-size:.86rem;line-height:1.5;color:var(--km-text);">{{ $selectedRequest->goal ?: '—' }}</p>
                        </div>
                        @if ($selectedRequest->pre_notes)
                            <div class="km-glass-box" style="padding:.85rem;">
                                <p class="km-eyebrow">Note pre-incontro</p>
                                <p style="margin-top:.4rem;font-size:.86rem;line-height:1.5;color:var(--km-text);">{{ $selectedRequest->pre_notes }}</p>
                            </div>
                        @endif
                        @if ($selectedRequest->post_notes)
                            <div class="km-glass-box" style="padding:.85rem;">
                                <p class="km-eyebrow">Resoconto condiviso</p>
                                <p style="margin-top:.4rem;font-size:.86rem;line-height:1.5;color:var(--km-text);">{{ $selectedRequest->post_notes }}</p>
                            </div>
                        @endif
                        @if ($sharedFollowUp)
                            <div class="km-glass-box" style="padding:.85rem;">
                                <p class="km-eyebrow">Follow-up condiviso</p>
                                <p style="margin-top:.4rem;font-size:.86rem;line-height:1.5;color:var(--km-text);">{{ $sharedFollowUp }}</p>
                            </div>
                        @endif
                        @if ($privateNote)
                            <div class="km-glass-box" style="padding:.85rem;border-color:rgba(139,197,63,.2);">
                                <p class="km-eyebrow">Nota privata</p>
                                <p class="km-muted" style="margin-top:.25rem;font-size:.72rem;">Visibile solo a te.</p>
                                <p style="margin-top:.4rem;font-size:.875rem;line-height:1.6;color:var(--km-text);">{{ $privateNote }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Barra azioni: Accetta / Rifiuta / Proponi nuovo orario — tutti sulla stessa riga --}}
                    @php
                        // Accetta/Rifiuta: lo vede chi DEVE rispondere (destinatario se pending,
                        // controparte di chi ha proposto se riprogrammato).
                        $showAcceptDecline = $selectedRequest->canRespondTo(auth()->id());
                        // Una volta che almeno un partecipante conferma il completamento,
                        // non è più possibile riprogrammare/modificare l'incontro.
                        $completionStarted = $selectedRequest->completionStarted();
                        $showReschedule    = ! in_array($selectedRequest->status->value, ['completed','cancelled'], true)
                                             && ! $completionStarted;
                    @endphp
                    @if ($showAcceptDecline || $showReschedule)
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.6rem;">
                            @if ($showAcceptDecline)
                                <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" style="margin:0;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="km-button-primary" style="padding:.55rem 1.25rem!important;">Accetta</button>
                                </form>
                                <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" style="margin:0;" onsubmit="return confirm('Rifiutare la richiesta?');">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="declined">
                                    <button type="submit" style="padding:.53rem 1.1rem;border-radius:1rem;border:1px solid rgba(244,63,94,.35);background:transparent;color:#FDA4AF;font-size:.82rem;font-weight:700;cursor:pointer;">Rifiuta</button>
                                </form>
                            @endif
                            @if ($showReschedule)
                                <button type="button" onclick="document.getElementById('km-reschedule-modal').style.display='flex'"
                                        style="padding:.53rem 1rem;border-radius:1rem;background:rgba(245,158,11,.13);border:1px solid rgba(245,158,11,.35);color:#FCD34D;font-size:.82rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>
                                    Proponi nuovo orario
                                </button>
                            @endif
                        </div>
                    @endif

                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" class="km-dark-card km-oto-detail-form" style="display:flex;flex-direction:column;">
                        @csrf
                        @method('PATCH')
                        @if ($isRecipient)
                            <div>
                                <p class="km-eyebrow">Resoconto condiviso</p>
                                <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Visibile a entrambi i partecipanti.</p>
                            </div>
                            <textarea name="post_notes" rows="2" class="km-dark-input" placeholder="Scrivi un resoconto dell'incontro..." style="resize:vertical;">{{ $selectedRequest->post_notes }}</textarea>
                        @endif
                        @if ($isRequester)
                            {{-- Il mittente può aggiornare il link meeting e il luogo --}}
                            @if ($selectedRequest->meeting_mode === 'online')
                                <div>
                                    <p class="km-eyebrow">Link meeting</p>
                                    <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">URL della videochiamata, visibile ad entrambi.</p>
                                </div>
                                <input type="url" name="meeting_link" value="{{ $selectedRequest->meeting_link }}"
                                       class="km-dark-input" placeholder="https://meet.google.com/... o Zoom/Teams">
                            @else
                                <div>
                                    <p class="km-eyebrow">Luogo incontro</p>
                                    <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Indirizzo o indicazioni per il punto d'incontro.</p>
                                </div>
                                <input type="text" name="meeting_location" value="{{ $selectedRequest->meeting_location }}"
                                       class="km-dark-input" placeholder="Es. Via Roma 12, Milano">
                            @endif
                            <div>
                                <p class="km-eyebrow">Follow-up condiviso</p>
                                <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Prossima azione concordata dopo il contatto.</p>
                            </div>
                            <textarea name="follow_up_notes" rows="2" class="km-dark-input" placeholder="Es. invio proposta entro venerdi, richiamo lunedi" style="resize:vertical;">{{ $sharedFollowUp }}</textarea>
                            @if (!in_array($selectedRequest->status->value, ['completed','cancelled']) && ! $completionStarted)
                                {{-- Annulla in form separato: evita che il salvataggio delle note avvenga insieme all'annullamento --}}
                                <div style="background:rgba(244,63,94,.08);border:1px solid rgba(244,63,94,.2);border-radius:1.2rem;padding:.8rem;">
                                    <p style="font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#FDA4AF;">Annulla richiesta</p>
                                    <p style="margin-top:.25rem;font-size:.75rem;color:rgba(253,164,175,.7);line-height:1.5;">Puoi annullare finche' non e' completata.</p>
                                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" style="margin:0;display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" onclick="return confirm('Sei sicuro di voler annullare questa richiesta?')" style="margin-top:.65rem;display:inline-block;padding:.35rem .9rem;border-radius:999px;border:1px solid rgba(244,63,94,.35);background:transparent;color:#FDA4AF;font-size:.73rem;font-weight:700;cursor:pointer;">
                                            Annulla richiesta
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endif
                        <div>
                            <p class="km-eyebrow">Nota privata</p>
                            <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Promemoria personale, visibile solo a te.</p>
                        </div>
                        <textarea name="private_note" rows="2" class="km-dark-input" placeholder="Nota privata solo per te" style="resize:vertical;">{{ $privateNote }}</textarea>
                        <button type="submit" class="km-button-primary" style="width:100%;">Aggiorna</button>
                    </form>

                    {{-- ══════════════════════════════════════════════════════════ --}}
                    {{-- ── RECENSIONE (solo status=completed) ─────────────────── --}}
                    {{-- Flusso distinto dalle referenze business (/referenze).    --}}
                    {{-- Qui si valuta la PERSONA e l'INCONTRO, non un'opportunità.--}}
                    {{-- ══════════════════════════════════════════════════════════ --}}
                    @if ($selectedRequest->status->value === 'completed')
                        @php
                            $myRef        = $selectedRequest->references->firstWhere('author_id', auth()->id());
                            $counterpart  ??= $isRequester ? $selectedRequest->recipient : $selectedRequest->requester;
                            $theirRef     = $selectedRequest->references->firstWhere('recipient_id', auth()->id());
                        @endphp

                        {{-- Separatore visivo --}}
                        <div style="display:flex;align-items:center;gap:.75rem;margin:.25rem 0;">
                            <div style="flex:1;height:1px;background:rgba(251,191,36,.18);"></div>
                            <span style="font-size:.65rem;font-weight:900;letter-spacing:.2em;text-transform:uppercase;color:rgba(251,191,36,.55);">Recensione</span>
                            <div style="flex:1;height:1px;background:rgba(251,191,36,.18);"></div>
                        </div>
                        <div style="font-size:.72rem;color:rgba(255,255,255,.35);margin-bottom:.25rem;line-height:1.5;padding:0 .1rem;">
                            💬 La recensione riguarda la <strong style="color:rgba(255,255,255,.5);">persona e l'incontro</strong>, non un'opportunità business.
                            Per passare un contatto commerciale usa la sezione <a href="{{ route('referrals.index') }}" style="color:rgba(121,200,67,.7);text-decoration:underline;">Referenze business</a>.
                        </div>

                        {{-- Recensione già lasciata: mostra riepilogo --}}
                        @if ($myRef)
                            <div class="km-dark-card" style="padding:1rem;border-color:rgba(251,191,36,.25);background:rgba(251,191,36,.04);">
                                <p class="km-eyebrow" style="color:#FCD34D;margin-bottom:.55rem;">✔ La tua recensione per {{ $counterpart?->name }}</p>

                                {{-- Stelle --}}
                                @if ($myRef->rating)
                                    <div style="display:flex;gap:.2rem;margin-bottom:.45rem;">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $s <= $myRef->rating ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                            </svg>
                                        @endfor
                                    </div>
                                @endif

                                {{-- Raccomandazione --}}
                                @if ($myRef->is_recommended !== null)
                                    <p style="font-size:.78rem;color:var(--km-text-muted);margin-bottom:.4rem;">
                                        {{ $myRef->is_recommended ? '👍 Consiglieresti questa persona' : '👎 Non consiglieresti questa persona' }}
                                    </p>
                                @endif

                                {{-- Tag --}}
                                @if (!empty($myRef->tags))
                                    <div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.5rem;">
                                        @foreach ($myRef->tags as $tag)
                                            <span style="padding:.2rem .65rem;border-radius:999px;background:rgba(251,191,36,.15);color:#FCD34D;border:1px solid rgba(251,191,36,.3);font-size:.72rem;font-weight:700;">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Testo --}}
                                @if ($myRef->content)
                                    <p style="font-size:.84rem;line-height:1.6;color:var(--km-text);">{{ $myRef->content }}</p>
                                @endif
                            </div>
                        @else
                            {{-- Form recensione --}}
                            <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" class="km-dark-card" style="padding:1rem;display:flex;flex-direction:column;gap:.7rem;border-color:rgba(251,191,36,.25);background:rgba(251,191,36,.03);">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <p class="km-eyebrow" style="color:#FCD34D;">Lascia una recensione</p>
                                    <p class="km-muted" style="margin-top:.2rem;font-size:.75rem;">Per {{ $counterpart?->name }} — visibile sul suo profilo pubblico.</p>
                                </div>

                                {{-- Rating stelle interattivo --}}
                                <div>
                                    <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:var(--km-text-muted);margin-bottom:.4rem;">Valutazione</p>
                                    <div style="display:flex;gap:.3rem;" id="ref-stars">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <button type="button" data-star="{{ $s }}"
                                                    style="background:none;border:none;cursor:pointer;padding:.1rem;"
                                                    onclick="setRefRating({{ $s }})">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FCD34D" stroke-width="1.8" data-idx="{{ $s }}">
                                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                </svg>
                                            </button>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="ref_rating" id="ref-rating-input" value="">
                                </div>

                                {{-- Raccomandazione --}}
                                <div>
                                    <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:var(--km-text-muted);margin-bottom:.4rem;">Consiglieresti questa persona?</p>
                                    <div style="display:flex;gap:.5rem;">
                                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .8rem;border-radius:.8rem;border:1px solid rgba(139,197,63,.3);background:rgba(139,197,63,.07);font-size:.82rem;color:var(--km-green-2);font-weight:700;">
                                            <input type="radio" name="ref_is_recommended" value="1" style="accent-color:var(--km-green-2);"> Sì
                                        </label>
                                        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;padding:.4rem .8rem;border-radius:.8rem;border:1px solid rgba(244,63,94,.25);background:rgba(244,63,94,.06);font-size:.82rem;color:#FDA4AF;font-weight:700;">
                                            <input type="radio" name="ref_is_recommended" value="0" style="accent-color:#FDA4AF;"> No
                                        </label>
                                    </div>
                                </div>

                                {{-- Tag competenze --}}
                                <div>
                                    <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:var(--km-text-muted);margin-bottom:.4rem;">Competenze dimostrate</p>
                                    <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
                                        @foreach ($referenceTags as $tag)
                                            <label style="cursor:pointer;">
                                                <input type="checkbox" name="ref_tags[]" value="{{ $tag }}" style="display:none;" class="ref-tag-check">
                                                <span class="ref-tag-pill" style="display:inline-block;padding:.25rem .7rem;border-radius:999px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.04);font-size:.74rem;font-weight:700;color:var(--km-text-muted);transition:.15s;cursor:pointer;">{{ $tag }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Testo libero --}}
                                <textarea name="ref_content" rows="3" class="km-dark-input"
                                          placeholder="Scrivi la tua recensione per {{ $counterpart?->name }}…"
                                          style="resize:vertical;"></textarea>

                                <button type="submit" style="display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.6rem 1.2rem;border-radius:1rem;background:rgba(251,191,36,.18);border:1px solid rgba(251,191,36,.45);color:#FCD34D;font-size:.85rem;font-weight:700;cursor:pointer;">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
                                    Pubblica recensione
                                </button>
                            </form>
                        @endif

                        {{-- Recensione ricevuta dall'altra parte --}}
                        @if ($theirRef)
                            <div class="km-glass-box" style="padding:.9rem;border-color:rgba(251,191,36,.18);">
                                <p class="km-eyebrow" style="color:#FCD34D;margin-bottom:.5rem;">Recensione ricevuta da {{ $counterpart?->name }}</p>

                                @if ($theirRef->rating)
                                    <div style="display:flex;gap:.2rem;margin-bottom:.4rem;">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $s <= $theirRef->rating ? '#FCD34D' : 'none' }}" stroke="#FCD34D" stroke-width="1.8">
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                            </svg>
                                        @endfor
                                    </div>
                                @endif

                                @if ($theirRef->is_recommended !== null)
                                    <p style="font-size:.78rem;color:var(--km-text-muted);margin-bottom:.35rem;">
                                        {{ $theirRef->is_recommended ? '👍 Ti consiglia agli altri membri' : '👎 Non ti consiglia agli altri membri' }}
                                    </p>
                                @endif

                                @if (!empty($theirRef->tags))
                                    <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.4rem;">
                                        @foreach ($theirRef->tags as $tag)
                                            <span style="padding:.18rem .6rem;border-radius:999px;background:rgba(139,197,63,.13);color:var(--km-green-2);border:1px solid rgba(139,197,63,.28);font-size:.72rem;font-weight:700;">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($theirRef->content)
                                    <p style="font-size:.84rem;line-height:1.6;color:var(--km-text);">{{ $theirRef->content }}</p>
                                @endif
                            </div>
                        @endif
                    @endif
                    {{-- ── fine recensione ─────────────────────────────────────────── --}}

                </div>
            </div>
        </div>
    @endif

    {{-- RESCHEDULE MODAL --}}
    @if ($selectedRequest && ! in_array($selectedRequest->status->value, ['completed','cancelled'], true) && ! $selectedRequest->completionStarted())
        <div id="km-reschedule-modal" style="display:none;position:fixed;inset:0;z-index:60;background:rgba(2,11,18,.82);backdrop-filter:blur(5px);align-items:center;justify-content:center;padding:1.5rem;">
            <div class="km-dark-modal" style="width:100%;max-width:26rem;border-radius:1.5rem;overflow:hidden;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.9rem 1.25rem;border-bottom:1px solid var(--km-line-dark);background:rgba(3,24,34,.92);backdrop-filter:blur(20px);">
                    <div>
                        <p class="km-eyebrow" style="color:#FCD34D;">Variazione orario</p>
                        <h3 style="margin-top:.2rem;font-size:1.05rem;font-weight:800;color:var(--km-text);">Proponi nuovo orario</h3>
                    </div>
                    <button type="button" onclick="document.getElementById('km-reschedule-modal').style.display='none'"
                            style="display:inline-flex;align-items:center;gap:.3rem;padding:.45rem .9rem;border-radius:.8rem;font-size:.82rem;font-weight:700;color:#f8fafc;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.22);cursor:pointer;transition:background .18s;"
                            onmouseover="this.style.background='rgba(255,255,255,.18)'"
                            onmouseout="this.style.background='rgba(255,255,255,.10)'">
                        ✕ Chiudi
                    </button>
                </div>
                <div style="padding:1.25rem;">
                    <p style="font-size:.83rem;color:var(--km-text-muted);line-height:1.6;margin-bottom:1.1rem;">
                        @if($selectedRequest->status->value === 'rescheduled')
                            Una variazione orario è già in corso. Puoi controproporre un orario diverso qui sotto.
                        @else
                            Seleziona la nuova data e ora. Lo stato passerà a <strong style="color:#FCD34D;">Riprogrammato</strong> e l'altra parte riceverà una notifica.
                        @endif
                    </p>
                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}">
                        @csrf
                        @method('PATCH')
                        <label style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:var(--km-text-muted);margin-bottom:.5rem;">Nuova data e ora</label>
                        <input type="datetime-local" name="propose_new_datetime"
                               value="{{ old('propose_new_datetime', '') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="km-dark-input" style="width:100%;cursor:pointer;" required>
                        @error('propose_new_datetime')
                            <p style="margin-top:.4rem;font-size:.75rem;color:#FDA4AF;">{{ $message }}</p>
                        @enderror
                        <div style="display:flex;gap:.6rem;margin-top:1rem;">
                            <button type="button" onclick="document.getElementById('km-reschedule-modal').style.display='none'"
                                    class="km-button-secondary" style="flex:1;">
                                Annulla
                            </button>
                            <button type="submit" class="km-button-primary" style="flex:2;">
                                Conferma proposta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- CREATE MODAL --}}
    <div id="one-to-one-create-modal" style="display:none;position:fixed;inset:0;z-index:50;overflow-y:auto;background:rgba(2,11,18,.8);backdrop-filter:blur(4px);padding:1.5rem;">
        <div style="margin:auto;display:flex;min-height:100%;width:100%;max-width:72rem;align-items:center;justify-content:center;">
            <div class="km-dark-modal" style="display:flex;flex-direction:column;max-height:calc(100vh - 3rem);width:100%;overflow:hidden;border-radius:1.75rem;">
                <div style="display:flex;flex-shrink:0;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.5rem;border-bottom:1px solid var(--km-line-dark);">
                    <div>
                        <p class="km-eyebrow">Nuova richiesta</p>
                        <h2 style="margin-top:.3rem;font-size:1.3rem;font-weight:800;color:var(--km-text);">Cerca membro e invia one-to-one</h2>
                    </div>
                    <button type="button" data-close-one-to-one-modal class="km-button-secondary">Chiudi</button>
                </div>

                <div class="km-oto-modal-grid" style="display:grid;min-height:0;flex:1;grid-template-columns:300px 1fr;overflow:hidden;">
                    <div class="km-oto-modal-sidebar" style="display:flex;flex-direction:column;overflow:hidden;border-right:1px solid var(--km-line-dark);padding:1.25rem;">
                        <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.2em;color:var(--km-text-muted);">Ricerca membro</label>
                        <input id="one-to-one-member-query" type="text" class="km-dark-input" style="margin-top:.6rem;" placeholder="Nome, email, azienda, citta'">
                        <p class="km-muted" style="margin-top:.4rem;font-size:.73rem;">Almeno 2 caratteri per cercare.</p>
                        <div id="one-to-one-member-results" style="margin-top:.75rem;flex:1;min-height:0;overflow-y:auto;display:flex;flex-direction:column;gap:.35rem;"></div>
                        <p id="one-to-one-member-empty" style="display:none;margin-top:.75rem;padding:1rem;border-radius:1.2rem;border:1px dashed rgba(255,255,255,.15);font-size:.8rem;color:var(--km-text-muted);">Nessun membro trovato.</p>
                        <p id="one-to-one-member-idle" style="margin-top:.75rem;padding:1rem;border-radius:1.2rem;border:1px dashed rgba(255,255,255,.15);font-size:.8rem;color:var(--km-text-muted);">Scrivi almeno 2 caratteri.</p>
                    </div>

                    <div style="display:flex;flex-direction:column;padding:1.25rem;min-height:0;overflow:hidden;">
                        <form method="POST" action="{{ route('one-to-ones.store') }}" style="display:flex;flex-direction:column;flex:1;min-height:0;">
                            @csrf
                            <input type="hidden" id="one-to-one-recipient-id" name="recipient_id" value="{{ old('recipient_id', optional($selectedMember)->id) }}">
                            <div style="flex:1;min-height:0;overflow-y:auto;display:flex;flex-direction:column;gap:.7rem;padding-right:.2rem;">
                                <div id="one-to-one-selected-member" style="display:none;padding:.75rem 1rem;border-radius:1.1rem;border:1px solid rgba(139,197,63,.35);background:rgba(139,197,63,.08);font-size:.82rem;color:var(--km-text);"></div>
                                <div style="padding:.7rem 1rem;border-radius:1.1rem;border:1px solid var(--km-line-dark);background:rgba(255,255,255,.04);font-size:.78rem;color:var(--km-text-muted);">
                                    Slot libero = prenotazione immediata. Fuori slot = richiesta da confermare.
                                </div>
                                <div style="padding:.875rem 1rem;border-radius:1.1rem;border:1px solid var(--km-line-dark);background:rgba(255,255,255,.03);">
                                    <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.2em;color:var(--km-text-muted);">Disponibilita' del membro</p>
                                    <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Scegli uno slot o proponi un orario libero.</p>
                                    <div id="one-to-one-availability" style="margin-top:.75rem;"></div>
                                </div>
                                {{-- Proposta orario libero: sempre visibile, anche quando il membro ha slot prenotabili --}}
                                <div style="padding:.875rem 1rem;border-radius:1.1rem;border:1px solid rgba(245,158,11,.28);background:rgba(245,158,11,.06);">
                                    <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.2em;color:#FCD34D;">Oppure proponi data e ora</p>
                                    <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Fuori dagli slot: la richiesta verrà inviata e dovrà essere confermata dal membro.</p>
                                    <div style="margin-top:.65rem;display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
                                        <label style="display:flex;flex-direction:column;gap:.3rem;">
                                            <span style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;color:var(--km-text-muted);">Data e ora</span>
                                            <input type="datetime-local" id="one-to-one-requested-at" name="requested_at" value="{{ old('requested_at') }}" class="km-dark-input">
                                        </label>
                                        <label style="display:flex;flex-direction:column;gap:.3rem;">
                                            <span style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.14em;color:var(--km-text-muted);">Modalita'</span>
                                            <select id="one-to-one-meeting-mode" name="meeting_mode" class="km-dark-input">
                                                <option value="online" @selected(old('meeting_mode','online')==='online')>Online</option>
                                                <option value="in_person" @selected(old('meeting_mode')==='in_person')>In presenza</option>
                                            </select>
                                        </label>
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
                                    <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" class="km-dark-input" placeholder="Link meeting online">
                                    <input type="text" name="meeting_location" value="{{ old('meeting_location') }}" class="km-dark-input" placeholder="Luogo incontro">
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
                                    <textarea name="goal" rows="2" class="km-dark-input" placeholder="Obiettivo dell'incontro *" required style="resize:vertical;">{{ old('goal') }}</textarea>
                                    <textarea name="pre_notes" rows="2" class="km-dark-input" placeholder="Note pre-incontro" style="resize:vertical;">{{ old('pre_notes') }}</textarea>
                                </div>
                            </div>
                            <div style="margin-top:1rem;flex-shrink:0;display:flex;justify-content:flex-end;gap:.75rem;border-top:1px solid var(--km-line-dark);padding-top:1rem;">
                                <button type="button" data-close-one-to-one-modal class="km-button-secondary">Annulla</button>
                                <button type="submit" id="one-to-one-submit" class="km-button-primary" disabled>Invia richiesta</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('one-to-one-create-modal');
            const openButton = document.getElementById('open-one-to-one-create-modal');
            const closeButtons = document.querySelectorAll('[data-close-one-to-one-modal]');
            const queryInput = document.getElementById('one-to-one-member-query');
            const resultsContainer = document.getElementById('one-to-one-member-results');
            const emptyState = document.getElementById('one-to-one-member-empty');
            const idleState = document.getElementById('one-to-one-member-idle');
            const recipientInput = document.getElementById('one-to-one-recipient-id');
            const requestedAtInput = document.getElementById('one-to-one-requested-at');
            const meetingModeSelect = document.getElementById('one-to-one-meeting-mode');
            const submitButton = document.getElementById('one-to-one-submit');
            const availabilityContainer = document.getElementById('one-to-one-availability');
            const selectedMemberSummary = document.getElementById('one-to-one-selected-member');
            // ── AJAX member search — nessun precaricamento, max 20 risultati per query ──
            const weekdayLabels = @json($weekdayOptions);
            const searchUrl    = '{{ route('members.search') }}';
            const slotsBaseUrl = '{{ url('/members') }}';

            if (!modal || !openButton) return;

            // Cache locale: id → dati membro (slots aggiunti dopo la selezione)
            let memberCache = {};
            let lastSearchResults = [];
            let searchDebounce = null;

            const normalize = (v) => String(v||'').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'');
            const selectedMember = () => memberCache[recipientInput.value] || null;

            const fetchMembers = async (query) => {
                if (query.length < 2) return [];
                try {
                    const resp = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
                    });
                    if (!resp.ok) return [];
                    const data = await resp.json();
                    data.forEach((m) => { if (!memberCache[m.id]) memberCache[m.id] = m; });
                    return data;
                } catch (e) { return []; }
            };

            const fetchMemberSlots = async (memberId) => {
                if (memberCache[memberId] && memberCache[memberId].availability_slots !== undefined) return;
                try {
                    const resp = await fetch(`${slotsBaseUrl}/${memberId}/slots`, {
                        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}
                    });
                    if (!resp.ok) return;
                    const slots = await resp.json();
                    if (memberCache[memberId]) memberCache[memberId].availability_slots = slots;
                } catch (e) { /* silenzioso */ }
            };

            const nextOccurrenceFor = (slot) => {
                const now = new Date();
                const cw = ((now.getDay()+6)%7)+1;
                let delta = slot.weekday - cw;
                if (delta < 0) delta += 7;
                const [h,m] = slot.starts_at.split(':').map(Number);
                const d = new Date(now);
                d.setHours(h,m,0,0);
                d.setDate(d.getDate()+delta);
                if (delta===0 && d<=now) d.setDate(d.getDate()+7);
                return d;
            };

            const toDateTimeLocal = (date) => {
                const p = (n) => String(n).padStart(2,'0');
                return `${date.getFullYear()}-${p(date.getMonth()+1)}-${p(date.getDate())}T${p(date.getHours())}:${p(date.getMinutes())}`;
            };

            const updateSubmitState = () => { submitButton.disabled = !recipientInput.value; };

            const renderSelectedMemberSummary = () => {
                const m = selectedMember();
                if (!m) { selectedMemberSummary.style.display='none'; selectedMemberSummary.innerHTML=''; return; }
                selectedMemberSummary.style.display='block';
                selectedMemberSummary.innerHTML=`<div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:var(--km-text-muted);">Membro selezionato</div><div style="margin-top:.3rem;font-weight:700;color:var(--km-text);">${m.name}</div><div style="font-size:.78rem;color:var(--km-text-muted);">${m.email||''}</div><div style="font-size:.73rem;color:var(--km-text-muted);">${m.company||'Azienda non indicata'}${m.city?' · '+m.city:''}</div>`;
            };

            const renderAvailability = () => {
                const m = selectedMember();
                if (!m) { availabilityContainer.innerHTML='<div style="padding:.7rem 1rem;border-radius:1rem;border:1px dashed rgba(255,255,255,.15);font-size:.78rem;color:var(--km-text-muted);">Seleziona un membro per vedere gli slot.</div>'; return; }
                if (m.availability_slots === undefined) { availabilityContainer.innerHTML='<div style="padding:.7rem 1rem;border-radius:1rem;border:1px dashed rgba(255,255,255,.15);font-size:.78rem;color:var(--km-text-muted);">Caricamento disponibilità…</div>'; return; }
                if (!m.availability_slots.length) { availabilityContainer.innerHTML='<div style="padding:.7rem 1rem;border-radius:1rem;border:1px dashed rgba(255,255,255,.15);font-size:.78rem;color:var(--km-text-muted);">Nessuna disponibilita\' pubblicata. Proponi un altro orario.</div>'; return; }
                availabilityContainer.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.5rem;">${m.availability_slots.map((slot) => `
                    <button type="button" data-slot-id="${slot.id}" style="display:flex;flex-direction:column;gap:.5rem;align-items:flex-start;padding:.65rem .8rem;border-radius:1.1rem;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);cursor:pointer;text-align:left;transition:.2s;">
                        <div>
                            <div style="font-weight:700;font-size:.8rem;color:var(--km-text);">${weekdayLabels[slot.weekday]||'Giorno'}</div>
                            <div style="margin-top:.1rem;font-size:.78rem;color:var(--km-text);">${slot.starts_at} - ${slot.ends_at}</div>
                            <div style="margin-top:.15rem;font-size:.72rem;color:var(--km-text-muted);">${slot.meeting_mode==='online'?'Online':'In presenza'}${slot.location?' · '+slot.location:''}</div>
                        </div>
                        <span style="padding:.18rem .6rem;border-radius:999px;background:rgba(139,197,63,.15);color:var(--km-green-2);border:1px solid rgba(139,197,63,.3);font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;">Prenota</span>
                    </button>
                `).join('')}</div>`;
                availabilityContainer.querySelectorAll('[data-slot-id]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const slot = m.availability_slots.find((s) => String(s.id)===String(btn.dataset.slotId));
                        if (!slot) return;
                        meetingModeSelect.value = slot.meeting_mode;
                        requestedAtInput.value = toDateTimeLocal(nextOccurrenceFor(slot));
                    });
                });
            };

            const renderMemberList = (results) => {
                const cur = String(recipientInput.value);
                resultsContainer.innerHTML = results.map((m) => `
                    <button type="button" data-member-id="${m.id}" style="display:block;width:100%;padding:.6rem .875rem;border-radius:1.1rem;border:1px solid ${cur===String(m.id)?'rgba(139,197,63,.45)':'rgba(255,255,255,.12)'};background:${cur===String(m.id)?'rgba(139,197,63,.1)':'rgba(255,255,255,.04)'};cursor:pointer;text-align:left;transition:.15s;">
                        <div style="font-weight:700;font-size:.83rem;color:var(--km-text);">${m.name}</div>
                        <div style="font-size:.76rem;color:var(--km-text-muted);">${m.email||''}</div>
                        <div style="font-size:.72rem;color:var(--km-text-muted);">${m.company||'Azienda non indicata'}${m.city?' · '+m.city:''}</div>
                    </button>
                `).join('');
                idleState.style.display='none';
                emptyState.style.display=results.length?'none':'block';
                resultsContainer.querySelectorAll('[data-member-id]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        recipientInput.value = btn.dataset.memberId;
                        updateSubmitState(); renderSelectedMemberSummary();
                        renderMemberList(lastSearchResults);
                        renderAvailability(); // mostra "Caricamento…"
                        await fetchMemberSlots(btn.dataset.memberId);
                        renderAvailability(); // aggiorna con gli slot reali
                    });
                });
            };

            const renderMembers = () => {
                const query = normalize(queryInput.value.trim());
                if (query.length < 2) {
                    resultsContainer.innerHTML=''; emptyState.style.display='none'; idleState.style.display='block';
                    return;
                }
                // Debounce 300ms per ridurre richieste durante la digitazione
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(async () => {
                    lastSearchResults = await fetchMembers(query);
                    renderMemberList(lastSearchResults);
                }, 300);
            };

            const openModal = () => { modal.style.display='block'; renderMembers(); renderSelectedMemberSummary(); renderAvailability(); updateSubmitState(); };
            const closeModal = () => { modal.style.display='none'; };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
            modal.addEventListener('click', (e) => { if (e.target===modal) closeModal(); });
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;
                if (modal.style.display !== 'none') closeModal();
                const rsModal = document.getElementById('km-reschedule-modal');
                if (rsModal && rsModal.style.display !== 'none') rsModal.style.display = 'none';
            });
            queryInput.addEventListener('input', renderMembers);

            // Chiudi reschedule modal cliccando lo sfondo
            const rsModal = document.getElementById('km-reschedule-modal');
            if (rsModal) rsModal.addEventListener('click', (e) => { if (e.target === rsModal) rsModal.style.display = 'none'; });

            // Riapri il reschedule modal se c'è un errore di validazione su propose_new_datetime
            @if ($errors->has('propose_new_datetime'))
                if (rsModal) rsModal.style.display = 'flex';
            @endif

            renderMembers(); renderSelectedMemberSummary(); renderAvailability(); updateSubmitState();

            // ── Referenza: stelle interattive ─────────────────────────────────
            window.setRefRating = (val) => {
                document.getElementById('ref-rating-input').value = val;
                document.querySelectorAll('#ref-stars svg').forEach((svg, i) => {
                    svg.setAttribute('fill', i < val ? '#FCD34D' : 'none');
                });
            };
            // ── Referenza: tag pill toggle ────────────────────────────────────
            document.querySelectorAll('.ref-tag-check').forEach((cb) => {
                cb.addEventListener('change', () => {
                    const pill = cb.nextElementSibling;
                    if (cb.checked) {
                        pill.style.background = 'rgba(45,212,191,.18)';
                        pill.style.color = '#5EEAD4';
                        pill.style.borderColor = 'rgba(45,212,191,.45)';
                    } else {
                        pill.style.background = 'rgba(255,255,255,.04)';
                        pill.style.color = 'var(--km-text-muted)';
                        pill.style.borderColor = 'rgba(255,255,255,.18)';
                    }
                });
            });

            // Apri modale in base al contesto:
            // - ?compose=1 (arrivo dal profilo membro): apri SUBITO con destinatario precompilato.
            // - Solo se NON e' presente ?request=ID (cioe' quando vogliamo vedere il dettaglio della richiesta appena creata)
            //   non riapriamo il modale dopo un invio: cosi' UX e' chiusura+toast+dettaglio.
            @if ((int) request('compose'))
                (async () => {
                    @if ($selectedMember)
                        // Pre-popola la cache con il membro gia' selezionato (passato via ?member=ID)
                        memberCache[{{ $selectedMember->id }}] = {
                            id: {{ $selectedMember->id }},
                            name: @json($selectedMember->name),
                            email: @json($selectedMember->email ?? ''),
                            company: @json($selectedMember->memberProfile?->company_name ?? null),
                            city: @json($selectedMember->memberProfile?->city?->name ?? null),
                        };
                        openModal();
                        await fetchMemberSlots({{ $selectedMember->id }});
                        renderAvailability();
                    @else
                        openModal();
                    @endif
                })();
            @endif
        })();
    </script>
</x-app-layout>
