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
                radial-gradient(circle at 80% 0%,rgba(139,197,63,.18),transparent 30%),
                radial-gradient(circle at 10% 25%,rgba(45,212,191,.12),transparent 35%),
                linear-gradient(135deg,var(--km-dark),var(--km-dark-2) 48%,#06111a)!important;
        }
        .km-shell{width:min(1480px,calc(100% - 48px));margin:0 auto;}
        .km-dark-panel{position:relative;overflow:hidden;border:1px solid var(--km-line);background:linear-gradient(135deg,rgba(255,255,255,.10),rgba(255,255,255,.045));box-shadow:0 24px 80px rgba(0,0,0,.35);backdrop-filter:blur(18px);border-radius:2rem;color:var(--km-text);}
        .km-dark-card{border:1px solid var(--km-line);background:linear-gradient(135deg,rgba(255,255,255,.085),rgba(255,255,255,.035));box-shadow:0 18px 60px rgba(0,0,0,.22);backdrop-filter:blur(18px);border-radius:1.8rem;color:var(--km-text);}
        .km-eyebrow{color:var(--km-green-2);font-size:.72rem;letter-spacing:.28em;text-transform:uppercase;font-weight:800;}
        .km-muted{color:var(--km-muted);}
        .km-glass-box{border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.055);border-radius:1.4rem;}
        .km-hero::before{content:"↔";position:absolute;right:6%;top:-30px;font-size:260px;font-weight:900;line-height:1;color:rgba(255,255,255,.04);pointer-events:none;}
        .km-button-primary{background:linear-gradient(135deg,var(--km-green),#5f9d42)!important;color:#061018!important;border:0!important;box-shadow:0 16px 42px rgba(139,197,63,.22);}
        .km-button-secondary{background:rgba(255,255,255,.08)!important;color:var(--km-text)!important;border:1px solid rgba(255,255,255,.14)!important;}
        .km-button-primary,.km-button-secondary{border-radius:1rem!important;min-height:42px;padding:.65rem 1.2rem!important;font-weight:700!important;font-size:.82rem;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:.22s ease;white-space:nowrap;text-decoration:none;}
        .km-button-primary:hover,.km-button-secondary:hover{transform:translateY(-2px);}
        .km-dark-input{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:1rem;color:var(--km-text);padding:.65rem 1rem;width:100%;font-size:.875rem;font-family:inherit;outline:none;transition:border-color .2s;}
        .km-dark-input::placeholder{color:var(--km-muted);}
        .km-dark-input:focus{border-color:rgba(139,197,63,.5);}
        .km-dark-input option{background:#052532;color:var(--km-text);}
        .km-dark-table{width:100%;border-collapse:collapse;}
        .km-dark-table th{text-align:left;padding:.875rem 1.25rem;color:var(--km-muted);font-size:.72rem;letter-spacing:.2em;text-transform:uppercase;font-weight:700;border-bottom:1px solid var(--km-line);}
        .km-dark-table td{padding:1rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.06);color:var(--km-text);font-size:.875rem;vertical-align:middle;}
        .km-dark-table tbody tr:last-child td{border-bottom:none;}
        .km-dark-table tbody tr:hover td{background:rgba(255,255,255,.04);}
        .km-two-col{display:flex;gap:1.5rem;align-items:flex-start;}
        .km-sidebar{width:340px;min-width:340px;flex-shrink:0;}
        .km-main-col{flex:1;min-width:0;}
        @media(max-width:1023px){.km-two-col{flex-direction:column;}.km-sidebar{width:100%;min-width:0;}}
        .km-dark-modal{background:linear-gradient(135deg,#031822,#052532);border:1px solid rgba(255,255,255,.12);box-shadow:0 30px 80px rgba(0,0,0,.65);}
    </style>

    <x-slot name="header">
        <div class="km-dark-panel km-hero" style="padding:2rem 2.5rem;">
            <div class="relative z-10" style="display:grid;gap:2rem;grid-template-columns:1.3fr 0.7fr;align-items:center;">
                <div>
                    <p class="km-eyebrow">Agenda relazionale · Pianeta Roma</p>
                    <h1 style="margin-top:1rem;font-size:clamp(1.8rem,3vw,2.6rem);font-weight:900;line-height:1.08;letter-spacing:-.04em;color:var(--km-text);">
                        Incontri <span style="color:var(--km-green);">one-to-one</span><br>tra professionisti
                    </h1>
                    <p class="km-muted" style="margin-top:1rem;font-size:.9rem;line-height:1.7;max-width:480px;">
                        Se l'orario rientra in uno slot attivo del destinatario e non ci sono conflitti, la prenotazione e' immediata. Altrimenti parte come richiesta da confermare.
                    </p>
                    <div style="margin-top:1.5rem;">
                        <button type="button" id="open-one-to-one-create-modal" class="km-button-primary" style="font-size:.9rem;padding:.8rem 1.6rem!important;">
                            + Nuova richiesta
                        </button>
                    </div>
                </div>
                <div class="km-dark-card" style="padding:1.5rem;">
                    <p class="km-eyebrow">Statistiche</p>
                    <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:.6rem;">
                        <div class="km-glass-box" style="padding:.875rem 1.1rem;display:flex;align-items:center;justify-content:space-between;">
                            <span class="km-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.18em;font-weight:700;">Totale</span>
                            <span style="font-size:1.6rem;font-weight:900;color:var(--km-text);">{{ $summary['total'] }}</span>
                        </div>
                        <div class="km-glass-box" style="padding:.875rem 1.1rem;display:flex;align-items:center;justify-content:space-between;">
                            <span class="km-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.18em;font-weight:700;">Ricevuti</span>
                            <span style="font-size:1.6rem;font-weight:900;color:var(--km-green);">{{ $summary['received'] }}</span>
                        </div>
                        <div class="km-glass-box" style="padding:.875rem 1.1rem;display:flex;align-items:center;justify-content:space-between;">
                            <span class="km-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.18em;font-weight:700;">Inviati</span>
                            <span style="font-size:1.6rem;font-weight:900;color:var(--km-teal);">{{ $summary['sent'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $memberSearchItems = $members->map(fn ($member) => [
            'id'   => $member->id,
            'name' => $member->name,
            'email'=> $member->email,
            'company' => $member->memberProfile?->company_name,
            'city'    => $member->memberProfile?->city?->name,
            'availability_slots' => $member->availabilitySlots->map(fn ($slot) => [
                'id'           => $slot->id,
                'weekday'      => $slot->weekday,
                'starts_at'    => substr($slot->starts_at, 0, 5),
                'ends_at'      => substr($slot->ends_at, 0, 5),
                'meeting_mode' => $slot->meeting_mode,
                'location'     => $slot->location,
            ])->values(),
        ])->values();
    @endphp

    <div style="padding-bottom:3rem;">
        <div class="km-shell" style="padding-top:2rem;">
            <div class="km-two-col">

                {{-- SIDEBAR --}}
                <aside class="km-sidebar" style="position:sticky;top:1.5rem;">
                    <div class="km-dark-card" style="padding:1.5rem;">
                        <p class="km-eyebrow">Le mie disponibilita'</p>
                        <p class="km-muted" style="margin-top:.5rem;font-size:.8rem;line-height:1.6;">Slot settimanali prenotabili dagli altri membri, solo senza conflitti.</p>
                        <form method="POST" action="{{ route('one-to-ones.availability.store') }}" style="margin-top:1.25rem;display:flex;flex-direction:column;gap:.65rem;">
                            @csrf
                            <select name="weekday" class="km-dark-input" required>
                                <option value="">Giorno della settimana</option>
                                @foreach ($weekdayOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;">
                                <input type="time" name="starts_at" class="km-dark-input" required>
                                <input type="time" name="ends_at" class="km-dark-input" required>
                            </div>
                            <select name="meeting_mode" class="km-dark-input">
                                <option value="online">Online</option>
                                <option value="in_person">In presenza</option>
                            </select>
                            <input type="text" name="location" class="km-dark-input" placeholder="Luogo o nota logistica">
                            <button type="submit" class="km-button-secondary" style="width:100%;">Aggiungi disponibilita'</button>
                        </form>

                        <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:.5rem;">
                            @forelse ($availabilitySlots as $slot)
                                <div class="km-glass-box" style="padding:.875rem 1rem;">
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                                        <div>
                                            <p style="font-size:.85rem;font-weight:700;color:var(--km-text);">{{ $weekdayOptions[$slot->weekday] ?? 'Giorno' }}</p>
                                            <p class="km-muted" style="margin-top:.2rem;font-size:.78rem;">{{ substr($slot->starts_at, 0, 5) }} – {{ substr($slot->ends_at, 0, 5) }} · {{ $slot->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</p>
                                            @if ($slot->location)
                                                <p class="km-muted" style="margin-top:.15rem;font-size:.73rem;">{{ $slot->location }}</p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('one-to-ones.availability.destroy', $slot) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:.73rem;font-weight:700;color:#F87171;padding:0;">Elimina</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="km-muted" style="font-size:.8rem;">Non hai ancora definito fasce disponibili.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>

                {{-- MAIN --}}
                <section class="km-main-col" style="display:flex;flex-direction:column;gap:1.25rem;">

                    <div>
                        <p class="km-eyebrow">Richieste</p>
                        <h2 style="margin-top:.4rem;font-size:1.5rem;font-weight:900;color:var(--km-text);">One-to-one ricevuti e inviati</h2>
                        @if ($selectedMember)
                            <p class="km-muted" style="margin-top:.3rem;font-size:.82rem;">Vista filtrata tra te e {{ $selectedMember->name }}.</p>
                        @else
                            <p class="km-muted" style="margin-top:.3rem;font-size:.82rem;">Filtra per membro, stato, modalita' o intervallo date.</p>
                        @endif
                    </div>

                    @if ($selectedMember)
                        <div class="km-glass-box" style="padding:.875rem 1.25rem;display:flex;align-items:center;justify-content:space-between;font-size:.875rem;">
                            <span style="color:var(--km-text);">Vista con {{ $selectedMember->name }}</span>
                            <a href="{{ route('one-to-ones.index') }}" style="font-weight:700;color:var(--km-green);text-decoration:none;">Rimuovi filtro</a>
                        </div>
                    @endif

                    {{-- Filtri --}}
                    <div class="km-dark-card" style="padding:1.25rem;">
                        <form method="GET" style="display:grid;gap:.65rem;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));">
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-dark-input" placeholder="Cerca membro o obiettivo" style="grid-column:span 2;">
                            <select name="member" class="km-dark-input">
                                <option value="">Tutti i membri</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" @selected((string)($filters['member'] ?? '') === (string)$member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                            <select name="type" class="km-dark-input">
                                <option value="">Tutti i tipi</option>
                                @foreach ($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="km-dark-input">
                                <option value="">Tutti gli stati</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="km-dark-input">
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="km-dark-input">
                            <select name="meeting_mode" class="km-dark-input">
                                <option value="">Modalita'</option>
                                @foreach ($modeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['meeting_mode'] ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;grid-column:span 2;">
                                <button type="submit" class="km-button-primary">Filtra</button>
                                <a href="{{ route('one-to-ones.index') }}" class="km-button-secondary">Reset</a>
                            </div>
                        </form>
                    </div>

                    {{-- Tabella --}}
                    <div class="km-dark-card" style="overflow:hidden;padding:0;">
                        <div style="overflow-x:auto;">
                            <table class="km-dark-table" style="min-width:580px;">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Membro</th>
                                        <th>Data</th>
                                        <th>Modalita'</th>
                                        <th>Stato</th>
                                        <th style="text-align:right;">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($requests as $requestItem)
                                        @php
                                            $sv = $requestItem->status->value;
                                            $bBg = match($sv) { 'pending'=>'rgba(245,158,11,.15)', 'accepted'=>'rgba(139,197,63,.15)', 'declined'=>'rgba(244,63,94,.15)', 'cancelled'=>'rgba(148,163,184,.10)', 'completed'=>'rgba(45,212,191,.15)', default=>'rgba(96,165,250,.15)' };
                                            $bCol = match($sv) { 'pending'=>'#FCD34D', 'accepted'=>'#9AD84A', 'declined'=>'#FDA4AF', 'cancelled'=>'#94A3B8', 'completed'=>'#5EEAD4', default=>'#93C5FD' };
                                            $bBor = match($sv) { 'pending'=>'rgba(245,158,11,.30)', 'accepted'=>'rgba(139,197,63,.30)', 'declined'=>'rgba(244,63,94,.30)', 'cancelled'=>'rgba(148,163,184,.20)', 'completed'=>'rgba(45,212,191,.25)', default=>'rgba(96,165,250,.25)' };
                                            $isOpen = (int)($filters['request'] ?? 0) === $requestItem->id;
                                            $detailUrl = $isOpen
                                                ? route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id]))
                                                : route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id,'request'=>$requestItem->id]));
                                        @endphp
                                        <tr style="{{ $isOpen ? 'background:rgba(139,197,63,.05);' : '' }}">
                                            <td><span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--km-muted);">{{ $requestItem->requester_id === auth()->id() ? 'Inviata' : 'Ricevuta' }}</span></td>
                                            <td>
                                                <div style="font-weight:700;color:var(--km-text);">{{ $requestItem->requester_id === auth()->id() ? $requestItem->recipient->name : $requestItem->requester->name }}</div>
                                                <div class="km-muted" style="font-size:.73rem;">{{ $requestItem->requester_id === auth()->id() ? 'Con' : 'Da' }}</div>
                                            </td>
                                            <td class="km-muted" style="font-size:.8rem;">{{ optional($requestItem->requested_at)->format('d/m/Y H:i') ?: 'Da confermare' }}</td>
                                            <td class="km-muted" style="font-size:.8rem;">{{ $requestItem->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</td>
                                            <td>
                                                <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:{{ $bBg }};color:{{ $bCol }};border:1px solid {{ $bBor }};">
                                                    {{ $requestItem->status->label() }}
                                                </span>
                                            </td>
                                            <td style="text-align:right;">
                                                <a href="{{ $detailUrl }}" style="display:inline-block;padding:.3rem .85rem;border-radius:999px;font-size:.73rem;font-weight:700;text-decoration:none;border:1px solid;transition:.2s;{{ $isOpen ? 'border-color:rgba(139,197,63,.5);color:var(--km-green);background:rgba(139,197,63,.08);' : 'border-color:rgba(255,255,255,.18);color:var(--km-text);' }}">
                                                    {{ $isOpen ? 'Chiudi' : 'Dettagli' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align:center;padding:2.5rem 1.25rem;color:var(--km-muted);font-size:.875rem;">
                                                Nessuna richiesta. Usa il pulsante in alto per inviare il primo invito.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Paginazione --}}
                    <div class="km-dark-card" style="padding:1rem 1.25rem;">
                        {{ $requests->links() }}
                    </div>

                </section>
            </div>
        </div>
    </div>

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
        <div class="km-dark-modal" style="position:fixed;inset-inline:0;bottom:0;top:1.5rem;z-index:50;margin:auto;width:min(1180px,calc(100% - 2rem));overflow-y:auto;border-radius:2rem 2rem 0 0;">
            <div style="position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.2rem 1.5rem;border-bottom:1px solid var(--km-line);background:rgba(3,24,34,.92);backdrop-filter:blur(20px);">
                <div>
                    <p class="km-eyebrow">Dettaglio richiesta</p>
                    <h3 style="margin-top:.35rem;font-size:1.15rem;font-weight:800;color:var(--km-text);">Gestione one-to-one</h3>
                    <p class="km-muted" style="margin-top:.25rem;font-size:.78rem;line-height:1.5;">Stato e resoconto aggiornati dal destinatario. Follow-up dal mittente. Nota privata solo per te.</p>
                </div>
                <a href="{{ route('one-to-ones.index', array_filter(['member'=>$selectedMember?->id])) }}" class="km-button-secondary" style="text-decoration:none;">Chiudi</a>
            </div>

            <div style="padding:1.5rem;display:flex;flex-direction:column;gap:1.25rem;">
                <div>
                    <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                        <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:rgba(255,255,255,.08);color:var(--km-muted);border:1px solid var(--km-line);">{{ $isRequester ? 'Inviata' : 'Ricevuta' }}</span>
                        <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:rgba(255,255,255,.08);color:var(--km-muted);border:1px solid var(--km-line);">{{ $selectedRequest->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</span>
                        <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;font-size:.67rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;background:{{ $dBg }};color:{{ $dCol }};border:1px solid {{ $dBor }};">{{ $selectedRequest->status->label() }}</span>
                    </div>
                    <h3 style="margin-top:.875rem;font-size:1.4rem;font-weight:800;color:var(--km-text);">{{ $isRequester ? 'Richiesta a '.$counterpart->name : 'Richiesta da '.$counterpart->name }}</h3>
                    <div style="margin-top:.4rem;display:flex;flex-wrap:wrap;gap:1.25rem;">
                        <span class="km-muted" style="font-size:.8rem;">{{ optional($selectedRequest->requested_at)->format('d/m/Y H:i') ?: 'Data da confermare' }}</span>
                        @if ($counterpart?->memberProfile?->city?->name)
                            <span class="km-muted" style="font-size:.8rem;">{{ $counterpart->memberProfile->city->name }}</span>
                        @endif
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <div style="display:grid;gap:.875rem;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
                        <div class="km-glass-box" style="padding:1rem;">
                            <p class="km-eyebrow">Obiettivo incontro</p>
                            <p style="margin-top:.5rem;font-size:.875rem;line-height:1.6;color:var(--km-text);">{{ $selectedRequest->goal }}</p>
                        </div>
                        @if ($selectedRequest->pre_notes)
                            <div class="km-glass-box" style="padding:1rem;">
                                <p class="km-eyebrow">Note pre-incontro</p>
                                <p style="margin-top:.5rem;font-size:.875rem;line-height:1.6;color:var(--km-text);">{{ $selectedRequest->pre_notes }}</p>
                            </div>
                        @endif
                        @if ($selectedRequest->post_notes)
                            <div class="km-glass-box" style="padding:1rem;">
                                <p class="km-eyebrow">Resoconto condiviso</p>
                                <p style="margin-top:.5rem;font-size:.875rem;line-height:1.6;color:var(--km-text);">{{ $selectedRequest->post_notes }}</p>
                            </div>
                        @endif
                        @if ($sharedFollowUp)
                            <div class="km-glass-box" style="padding:1rem;">
                                <p class="km-eyebrow">Follow-up condiviso</p>
                                <p style="margin-top:.5rem;font-size:.875rem;line-height:1.6;color:var(--km-text);">{{ $sharedFollowUp }}</p>
                            </div>
                        @endif
                        @if ($privateNote)
                            <div class="km-glass-box" style="padding:1rem;border-color:rgba(139,197,63,.2);">
                                <p class="km-eyebrow">Nota privata</p>
                                <p class="km-muted" style="margin-top:.25rem;font-size:.72rem;">Visibile solo a te.</p>
                                <p style="margin-top:.4rem;font-size:.875rem;line-height:1.6;color:var(--km-text);">{{ $privateNote }}</p>
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" class="km-dark-card" style="padding:1.25rem;display:flex;flex-direction:column;gap:.75rem;">
                        @csrf
                        @method('PATCH')
                        @if ($isRecipient)
                            <div>
                                <p class="km-eyebrow">Aggiorna stato</p>
                                <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Solo il destinatario puo' modificare stato e resoconto.</p>
                                <select name="status" class="km-dark-input" style="margin-top:.6rem;">
                                    @foreach (\App\Enums\OneToOneStatus::options() as $status => $label)
                                        <option value="{{ $status }}" @selected($selectedRequest->status->value === $status)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea name="post_notes" rows="3" class="km-dark-input" placeholder="Resoconto condiviso visibile a entrambi" style="resize:vertical;">{{ $selectedRequest->post_notes }}</textarea>
                        @endif
                        @if ($isRequester)
                            <div>
                                <p class="km-eyebrow">Follow-up condiviso</p>
                                <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Prossima azione concordata dopo il contatto.</p>
                            </div>
                            <textarea name="follow_up_notes" rows="3" class="km-dark-input" placeholder="Es. invio proposta entro venerdi, richiamo lunedi" style="resize:vertical;">{{ $sharedFollowUp }}</textarea>
                            @if (!in_array($selectedRequest->status->value, ['completed','cancelled']))
                                <div style="background:rgba(244,63,94,.08);border:1px solid rgba(244,63,94,.2);border-radius:1.2rem;padding:1rem;">
                                    <p style="font-size:.7rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#FDA4AF;">Annulla richiesta</p>
                                    <p style="margin-top:.25rem;font-size:.75rem;color:rgba(253,164,175,.7);line-height:1.5;">Puoi annullare finche' non e' completata.</p>
                                    <button type="submit" name="status" value="cancelled" onclick="return confirm('Sei sicuro di voler annullare questa richiesta?')" style="margin-top:.65rem;display:inline-block;padding:.35rem .9rem;border-radius:999px;border:1px solid rgba(244,63,94,.35);background:transparent;color:#FDA4AF;font-size:.73rem;font-weight:700;cursor:pointer;">
                                        Annulla richiesta
                                    </button>
                                </div>
                            @endif
                        @endif
                        <div>
                            <p class="km-eyebrow">Nota privata</p>
                            <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Promemoria personale, visibile solo a te.</p>
                        </div>
                        <textarea name="private_note" rows="3" class="km-dark-input" placeholder="Nota privata solo per te" style="resize:vertical;">{{ $privateNote }}</textarea>
                        <button type="submit" class="km-button-primary" style="width:100%;">Aggiorna</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- CREATE MODAL --}}
    <div id="one-to-one-create-modal" style="display:none;position:fixed;inset:0;z-index:50;overflow-y:auto;background:rgba(2,11,18,.8);backdrop-filter:blur(4px);padding:1.5rem;">
        <div style="margin:auto;display:flex;min-height:100%;width:100%;max-width:72rem;align-items:center;justify-content:center;">
            <div class="km-dark-modal" style="display:flex;flex-direction:column;max-height:calc(100vh - 3rem);width:100%;overflow:hidden;border-radius:1.75rem;">
                <div style="display:flex;flex-shrink:0;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.5rem;border-bottom:1px solid var(--km-line);">
                    <div>
                        <p class="km-eyebrow">Nuova richiesta</p>
                        <h2 style="margin-top:.3rem;font-size:1.3rem;font-weight:800;color:var(--km-text);">Cerca membro e invia one-to-one</h2>
                    </div>
                    <button type="button" data-close-one-to-one-modal class="km-button-secondary">Chiudi</button>
                </div>

                <div style="display:grid;min-height:0;flex:1;grid-template-columns:300px 1fr;overflow:hidden;">
                    <div style="display:flex;flex-direction:column;overflow:hidden;border-right:1px solid var(--km-line);padding:1.25rem;">
                        <label style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.2em;color:var(--km-muted);">Ricerca membro</label>
                        <input id="one-to-one-member-query" type="text" class="km-dark-input" style="margin-top:.6rem;" placeholder="Nome, email, azienda, citta'">
                        <p class="km-muted" style="margin-top:.4rem;font-size:.73rem;">Almeno 2 caratteri per cercare.</p>
                        <div id="one-to-one-member-results" style="margin-top:.75rem;flex:1;min-height:0;overflow-y:auto;display:flex;flex-direction:column;gap:.35rem;"></div>
                        <p id="one-to-one-member-empty" style="display:none;margin-top:.75rem;padding:1rem;border-radius:1.2rem;border:1px dashed rgba(255,255,255,.15);font-size:.8rem;color:var(--km-muted);">Nessun membro trovato.</p>
                        <p id="one-to-one-member-idle" style="margin-top:.75rem;padding:1rem;border-radius:1.2rem;border:1px dashed rgba(255,255,255,.15);font-size:.8rem;color:var(--km-muted);">Scrivi almeno 2 caratteri.</p>
                    </div>

                    <div style="display:flex;flex-direction:column;padding:1.25rem;min-height:0;overflow:hidden;">
                        @if (session('status') === 'one-to-one-booked')
                            <div style="flex-shrink:0;margin-bottom:.75rem;padding:.75rem 1rem;border-radius:1rem;background:rgba(45,212,191,.12);border:1px solid rgba(45,212,191,.25);color:#5EEAD4;font-size:.82rem;">One-to-one prenotato: slot disponibile e senza conflitti.</div>
                        @elseif (session('status') === 'one-to-one-created')
                            <div style="flex-shrink:0;margin-bottom:.75rem;padding:.75rem 1rem;border-radius:1rem;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);color:#FCD34D;font-size:.82rem;">Richiesta inviata: il destinatario dovra' confermare.</div>
                        @endif

                        <form method="POST" action="{{ route('one-to-ones.store') }}" style="display:flex;flex-direction:column;flex:1;min-height:0;">
                            @csrf
                            <input type="hidden" id="one-to-one-recipient-id" name="recipient_id" value="{{ old('recipient_id', optional($selectedMember)->id) }}">
                            <div style="flex:1;min-height:0;overflow-y:auto;display:flex;flex-direction:column;gap:.7rem;padding-right:.2rem;">
                                <div id="one-to-one-selected-member" style="display:none;padding:.75rem 1rem;border-radius:1.1rem;border:1px solid rgba(139,197,63,.35);background:rgba(139,197,63,.08);font-size:.82rem;color:var(--km-text);"></div>
                                <div style="padding:.7rem 1rem;border-radius:1.1rem;border:1px solid var(--km-line);background:rgba(255,255,255,.04);font-size:.78rem;color:var(--km-muted);">
                                    Slot libero = prenotazione immediata. Fuori slot = richiesta da confermare.
                                </div>
                                <div style="padding:.875rem 1rem;border-radius:1.1rem;border:1px solid var(--km-line);background:rgba(255,255,255,.03);">
                                    <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.2em;color:var(--km-muted);">Disponibilita' del membro</p>
                                    <p class="km-muted" style="margin-top:.25rem;font-size:.75rem;">Scegli uno slot o proponi un orario libero.</p>
                                    <div id="one-to-one-availability" style="margin-top:.75rem;"></div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem;">
                                    <input type="datetime-local" id="one-to-one-requested-at" name="requested_at" value="{{ old('requested_at') }}" class="km-dark-input">
                                    <select id="one-to-one-meeting-mode" name="meeting_mode" class="km-dark-input">
                                        <option value="online" @selected(old('meeting_mode','online')==='online')>Online</option>
                                        <option value="in_person" @selected(old('meeting_mode')==='in_person')>In presenza</option>
                                    </select>
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
                            <div style="margin-top:1rem;flex-shrink:0;display:flex;justify-content:flex-end;gap:.75rem;border-top:1px solid var(--km-line);padding-top:1rem;">
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
            const members = @json($memberSearchItems);
            const weekdayLabels = @json($weekdayOptions);

            if (!modal || !openButton) return;

            const normalize = (v) => String(v||'').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'');
            const selectedMember = () => members.find((m) => String(m.id) === String(recipientInput.value)) || null;

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
                selectedMemberSummary.innerHTML=`<div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.18em;color:var(--km-muted);">Membro selezionato</div><div style="margin-top:.3rem;font-weight:700;color:var(--km-text);">${m.name}</div><div style="font-size:.78rem;color:var(--km-muted);">${m.email}</div><div style="font-size:.73rem;color:var(--km-muted);">${m.company||'Azienda non indicata'}${m.city?' · '+m.city:''}</div>`;
            };

            const renderAvailability = () => {
                const m = selectedMember();
                if (!m) { availabilityContainer.innerHTML='<div style="padding:.7rem 1rem;border-radius:1rem;border:1px dashed rgba(255,255,255,.15);font-size:.78rem;color:var(--km-muted);">Seleziona un membro per vedere gli slot.</div>'; return; }
                if (!m.availability_slots.length) { availabilityContainer.innerHTML='<div style="padding:.7rem 1rem;border-radius:1rem;border:1px dashed rgba(255,255,255,.15);font-size:.78rem;color:var(--km-muted);">Nessuna disponibilita\' pubblicata. Proponi un altro orario.</div>'; return; }
                availabilityContainer.innerHTML = m.availability_slots.map((slot) => `
                    <button type="button" data-slot-id="${slot.id}" style="display:flex;width:100%;align-items:center;justify-content:space-between;gap:1rem;padding:.7rem 1rem;border-radius:1.1rem;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.04);cursor:pointer;text-align:left;transition:.2s;margin-bottom:.35rem;">
                        <div>
                            <div style="font-weight:700;font-size:.82rem;color:var(--km-text);">${weekdayLabels[slot.weekday]||'Giorno'} · ${slot.starts_at} - ${slot.ends_at}</div>
                            <div style="margin-top:.15rem;font-size:.75rem;color:var(--km-muted);">${slot.meeting_mode==='online'?'Online':'In presenza'}${slot.location?' · '+slot.location:''}</div>
                        </div>
                        <span style="flex-shrink:0;padding:.2rem .65rem;border-radius:999px;background:rgba(139,197,63,.15);color:var(--km-green-2);border:1px solid rgba(139,197,63,.3);font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;">Prenota</span>
                    </button>
                `).join('');
                availabilityContainer.querySelectorAll('[data-slot-id]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const slot = m.availability_slots.find((s) => String(s.id)===String(btn.dataset.slotId));
                        if (!slot) return;
                        meetingModeSelect.value = slot.meeting_mode;
                        requestedAtInput.value = toDateTimeLocal(nextOccurrenceFor(slot));
                    });
                });
            };

            const renderMembers = () => {
                const query = normalize(queryInput.value.trim());
                if (query.length < 2) { resultsContainer.innerHTML=''; emptyState.style.display='none'; idleState.style.display='block'; return; }
                const filtered = members.filter((m) => [m.name,m.email,m.company,m.city].some((v) => normalize(v).includes(query)));
                const cur = String(recipientInput.value);
                resultsContainer.innerHTML = filtered.map((m) => `
                    <button type="button" data-member-id="${m.id}" style="display:block;width:100%;padding:.6rem .875rem;border-radius:1.1rem;border:1px solid ${cur===String(m.id)?'rgba(139,197,63,.45)':'rgba(255,255,255,.12)'};background:${cur===String(m.id)?'rgba(139,197,63,.1)':'rgba(255,255,255,.04)'};cursor:pointer;text-align:left;transition:.15s;">
                        <div style="font-weight:700;font-size:.83rem;color:var(--km-text);">${m.name}</div>
                        <div style="font-size:.76rem;color:var(--km-muted);">${m.email}</div>
                        <div style="font-size:.72rem;color:var(--km-muted);">${m.company||'Azienda non indicata'}${m.city?' · '+m.city:''}</div>
                    </button>
                `).join('');
                idleState.style.display='none';
                emptyState.style.display=filtered.length?'none':'block';
                resultsContainer.querySelectorAll('[data-member-id]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        recipientInput.value = btn.dataset.memberId;
                        updateSubmitState(); renderSelectedMemberSummary(); renderMembers(); renderAvailability();
                    });
                });
            };

            const openModal = () => { modal.style.display='block'; renderMembers(); renderSelectedMemberSummary(); renderAvailability(); updateSubmitState(); };
            const closeModal = () => { modal.style.display='none'; };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
            modal.addEventListener('click', (e) => { if (e.target===modal) closeModal(); });
            document.addEventListener('keydown', (e) => { if (e.key==='Escape' && modal.style.display!=='none') closeModal(); });
            queryInput.addEventListener('input', renderMembers);

            renderMembers(); renderSelectedMemberSummary(); renderAvailability(); updateSubmitState();
        })();
    </script>
</x-app-layout>
