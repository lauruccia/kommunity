<x-app-layout>
    @php
        $memberSearchItems = $members->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'company' => $member->memberProfile?->company_name,
            'city' => $member->memberProfile?->city?->name,
            'availability_slots' => $member->availabilitySlots->map(fn ($slot) => [
                'id' => $slot->id,
                'weekday' => $slot->weekday,
                'starts_at' => substr($slot->starts_at, 0, 5),
                'ends_at' => substr($slot->ends_at, 0, 5),
                'meeting_mode' => $slot->meeting_mode,
                'location' => $slot->location,
            ])->values(),
        ])->values();
    @endphp
    <div class="pb-12">
        <div class="w-full px-4 pt-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <aside class="space-y-6 lg:sticky lg:top-6 lg:w-[340px] lg:min-w-[340px]">
                <section class="km-panel p-6">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Agenda relazionale</p>
                    <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Richieste one-to-one</h1>
                    <p class="mt-3 text-sm leading-7 text-stone-600">Se l'orario richiesto rientra in una disponibilita' attiva del destinatario e non ci sono conflitti, il one-to-one viene prenotato subito. Altrimenti parte come richiesta da confermare.</p>
                    <button type="button" id="open-one-to-one-create-modal" class="km-button-primary mt-5 w-full">Nuova richiesta</button>
                </section>

                <section class="km-panel p-6">
                    <h2 class="font-serif text-2xl font-semibold text-stone-950">Le mie disponibilita'</h2>
                    <p class="mt-2 text-sm leading-7 text-stone-600">Definisci slot settimanali prenotabili. Gli altri membri potranno bloccare direttamente un one-to-one solo dentro questi orari e solo se non esiste gia' un'altra prenotazione sullo stesso momento.</p>
                    <form method="POST" action="{{ route('one-to-ones.availability.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <select name="weekday" class="km-input" required>
                            <option value="">Giorno</option>
                            @foreach ($weekdayOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="grid gap-4 md:grid-cols-2">
                            <input type="time" name="starts_at" class="km-input" required>
                            <input type="time" name="ends_at" class="km-input" required>
                        </div>
                        <select name="meeting_mode" class="km-input">
                            <option value="online">Online</option>
                            <option value="in_person">In presenza</option>
                        </select>
                        <input type="text" name="location" class="km-input" placeholder="Luogo o nota logistica">
                        <button type="submit" class="km-button-secondary w-full">Aggiungi disponibilita'</button>
                    </form>

                    <div class="mt-5 space-y-3">
                        @forelse ($availabilitySlots as $slot)
                            <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-stone-950">{{ $weekdayOptions[$slot->weekday] ?? 'Giorno' }}</p>
                                        <p class="mt-1 text-sm text-stone-600">{{ substr($slot->starts_at, 0, 5) }} - {{ substr($slot->ends_at, 0, 5) }} · {{ $slot->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</p>
                                        @if ($slot->location)
                                            <p class="mt-1 text-xs text-stone-500">{{ $slot->location }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('one-to-ones.availability.destroy', $slot) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-600">Elimina</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-stone-600">Non hai ancora definito fasce disponibili.</p>
                        @endforelse
                    </div>
                </section>
            </aside>

            <section class="min-w-0 flex-1 space-y-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Richieste</p>
                        <h2 class="mt-2 font-serif text-3xl font-semibold text-stone-950">One-to-one ricevuti e inviati</h2>
                        @if ($selectedMember)
                            <p class="mt-2 text-sm text-stone-600">Vista filtrata tra te e {{ $selectedMember->name }}.</p>
                        @else
                            <p class="mt-2 text-sm text-stone-600">Usa i filtri per cercare richieste per membro, tipo, stato, modalita' o intervallo date.</p>
                        @endif
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-[1.5rem] bg-white/85 px-4 py-3 shadow-[0_12px_28px_rgba(60,79,94,0.08)]">
                            <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Totale</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $summary['total'] }}</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-white/85 px-4 py-3 shadow-[0_12px_28px_rgba(60,79,94,0.08)]">
                            <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Ricevuti</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $summary['received'] }}</p>
                        </div>
                        <div class="rounded-[1.5rem] bg-white/85 px-4 py-3 shadow-[0_12px_28px_rgba(60,79,94,0.08)]">
                            <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Inviati</p>
                            <p class="mt-2 text-2xl font-semibold text-stone-950">{{ $summary['sent'] }}</p>
                        </div>
                    </div>
                </div>

                @if ($selectedMember)
                    <div class="flex items-center justify-between rounded-[1.6rem] border border-stone-200 bg-white px-5 py-4 text-sm text-stone-700 shadow-[0_10px_24px_rgba(60,79,94,0.06)]">
                        <span>Stai gestendo i one-to-one con {{ $selectedMember->name }}.</span>
                        <a href="{{ route('one-to-ones.index') }}" class="font-semibold text-[color:var(--km-accent-strong)]">Rimuovi filtro</a>
                    </div>
                @endif

                <div class="km-panel p-5">
                    <form method="GET" class="grid gap-3 lg:grid-cols-4 2xl:grid-cols-8">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-input lg:col-span-2 2xl:col-span-2" placeholder="Cerca membro, email o obiettivo">
                        <select name="member" class="km-input">
                            <option value="">Tutti i membri</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected((string) ($filters['member'] ?? '') === (string) $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <select name="type" class="km-input">
                            <option value="">Tutti i tipi</option>
                            @foreach ($typeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? null) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="km-input">
                            <option value="">Tutti gli stati</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="km-input">
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="km-input">
                        <select name="meeting_mode" class="km-input">
                            <option value="">Modalita'</option>
                            @foreach ($modeOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['meeting_mode'] ?? null) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="grid grid-cols-2 gap-3 lg:col-span-1 2xl:col-span-1">
                            <button type="submit" class="km-button-primary whitespace-nowrap">Filtra</button>
                            <a href="{{ route('one-to-ones.index') }}" class="km-button-secondary whitespace-nowrap">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="km-panel overflow-hidden p-0">
                    <div class="overflow-x-auto">
                        <table class="min-w-[56rem] divide-y divide-stone-200 text-sm xl:min-w-full">
                            <thead class="bg-stone-50">
                                <tr class="text-left text-xs uppercase tracking-[0.16em] text-stone-500">
                                    <th class="px-5 py-4">Tipo</th>
                                    <th class="px-5 py-4">Membro</th>
                                    <th class="px-5 py-4">Data</th>
                                    <th class="px-5 py-4">Modalita'</th>
                                    <th class="px-5 py-4">Stato</th>
                                    <th class="px-5 py-4 text-right">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 bg-white">
                                @forelse ($requests as $requestItem)
                                    <tr class="{{ (int) ($filters['request'] ?? 0) === $requestItem->id ? 'bg-stone-50' : '' }}">
                                        <td class="px-5 py-4 font-medium text-stone-700">{{ $requestItem->requester_id === auth()->id() ? 'Inviata' : 'Ricevuta' }}</td>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-stone-950">{{ $requestItem->requester_id === auth()->id() ? $requestItem->recipient->name : $requestItem->requester->name }}</div>
                                            <div class="text-xs text-stone-500">{{ $requestItem->requester_id === auth()->id() ? 'Con' : 'Da' }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-stone-600">{{ optional($requestItem->requested_at)->format('d/m/Y H:i') ?: 'Da confermare' }}</td>
                                        <td class="px-5 py-4 text-stone-600">{{ $requestItem->meeting_mode === 'online' ? 'Online' : 'In presenza' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-800">
                                                {{ $requestItem->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            @php
                                                $isOpen = (int) ($filters['request'] ?? 0) === $requestItem->id;
                                                $detailUrl = $isOpen
                                                    ? route('one-to-ones.index', array_filter(['member' => $selectedMember?->id]))
                                                    : route('one-to-ones.index', array_filter(['member' => $selectedMember?->id, 'request' => $requestItem->id]));
                                            @endphp
                                            <a href="{{ $detailUrl }}" class="inline-flex rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold {{ $isOpen ? 'border-[color:var(--km-accent)] text-[color:var(--km-accent-strong)]' : 'text-stone-700' }} transition hover:bg-stone-50">
                                                {{ $isOpen ? 'Chiudi' : 'Dettagli' }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-8 text-center text-sm text-stone-500">
                                            Nessuna richiesta presente. Usa la sidebar per inviare il primo invito o definire le tue disponibilita'.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="km-panel p-4">
                    {{ $requests->links() }}
                </div>
            </section>
            </div>
        </div>

        @if ($selectedRequest)
            @php
                $isRecipient = $selectedRequest->recipient_id === auth()->id();
                $isRequester = $selectedRequest->requester_id === auth()->id();
                $counterpart = $isRequester ? $selectedRequest->recipient : $selectedRequest->requester;
                $privateNote = $selectedRequest->notes->first()?->note;
                $sharedFollowUp = $selectedRequest->followUps->first()?->content ?: $selectedRequest->follow_up_notes;
            @endphp
            <div class="fixed inset-0 z-40 bg-stone-950/45">
                <a href="{{ route('one-to-ones.index', array_filter(['member' => $selectedMember?->id])) }}" class="absolute inset-0" aria-label="Chiudi dettaglio"></a>
            </div>
            <div class="fixed inset-x-0 bottom-0 top-3 z-50 mx-auto w-[min(1180px,calc(100%-1rem))] overflow-y-auto rounded-t-[2rem] bg-white shadow-[0_30px_80px_rgba(17,24,39,0.22)] sm:top-6 sm:w-[min(1180px,calc(100%-2rem))] lg:top-10 lg:rounded-[2rem]">
                <div class="sticky top-0 z-10 flex flex-col gap-4 border-b border-stone-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Dettaglio richiesta</p>
                        <h3 class="mt-2 text-xl font-semibold text-stone-950">Gestione one-to-one selezionato</h3>
                        <p class="mt-2 text-sm leading-7 text-stone-600">
                            I dettagli iniziali dell'invito sono condivisi. Stato e resoconto condiviso vengono aggiornati dal destinatario, il follow-up condiviso dal mittente. La nota privata resta visibile solo a te.
                        </p>
                    </div>
                    <a href="{{ route('one-to-ones.index', array_filter(['member' => $selectedMember?->id])) }}" class="inline-flex rounded-full border border-stone-300 px-4 py-2 text-xs font-semibold text-stone-700 transition hover:bg-stone-50">
                        Chiudi dettaglio
                    </a>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">
                                    {{ $isRequester ? 'Inviata' : 'Ricevuta' }}
                                </span>
                                <span class="rounded-full bg-[color:var(--km-soft)] px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">
                                    {{ $selectedRequest->meeting_mode === 'online' ? 'Online' : 'In presenza' }}
                                </span>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-800">
                                    {{ $selectedRequest->status->label() }}
                                </span>
                            </div>

                            <h3 class="mt-4 text-2xl font-semibold text-stone-950">
                                {{ $isRequester ? 'Richiesta inviata a '.$counterpart->name : 'Richiesta da '.$counterpart->name }}
                            </h3>

                            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-stone-500">
                                <span>{{ optional($selectedRequest->requested_at)->format('d/m/Y H:i') ?: 'Data da confermare' }}</span>
                                @if ($counterpart?->memberProfile?->city?->name)
                                    <span>{{ $counterpart->memberProfile->city->name }}</span>
                                @endif
                            </div>

                            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Obiettivo incontro</p>
                                    <p class="mt-2 text-sm leading-7 text-stone-700">{{ $selectedRequest->goal }}</p>
                                </div>
                                @if ($selectedRequest->pre_notes)
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Note pre-incontro</p>
                                        <p class="mt-2 text-sm leading-7 text-stone-700">{{ $selectedRequest->pre_notes }}</p>
                                    </div>
                                @endif
                                @if ($selectedRequest->post_notes)
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Resoconto condiviso</p>
                                        <p class="mt-2 text-sm leading-7 text-stone-700">{{ $selectedRequest->post_notes }}</p>
                                    </div>
                                @endif
                                @if ($sharedFollowUp)
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Follow-up condiviso</p>
                                        <p class="mt-1 text-xs leading-6 text-stone-500">Definisce il prossimo passo concordato tra i due utenti: richiamare, inviare documenti, confermare una data o chiudere l'azione.</p>
                                        <p class="mt-2 text-sm leading-7 text-stone-700">{{ $sharedFollowUp }}</p>
                                    </div>
                                @endif
                                @if ($privateNote)
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Nota privata</p>
                                        <p class="mt-1 text-xs leading-6 text-stone-500">Questa nota e' personale e non viene mostrata all'altro utente.</p>
                                        <p class="mt-2 text-sm leading-7 text-stone-700">{{ $privateNote }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" class="grid gap-3 rounded-[1.6rem] border border-stone-200 bg-stone-50 p-4 xl:w-[320px]">
                            @csrf
                            @method('PATCH')
                            @if ($isRecipient)
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Aggiorna stato</p>
                                    <p class="mt-1 text-xs leading-6 text-stone-500">Solo il destinatario puo' modificare stato e resoconto condiviso.</p>
                                    <select name="status" class="km-input mt-2">
                                        @foreach (\App\Enums\OneToOneStatus::options() as $status => $label)
                                            <option value="{{ $status }}" @selected($selectedRequest->status->value === $status)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <textarea name="post_notes" rows="4" class="km-input" placeholder="Resoconto condiviso visibile a entrambi">{{ $selectedRequest->post_notes }}</textarea>
                            @endif
                            @if ($isRequester)
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Follow-up condiviso</p>
                                    <p class="mt-1 text-xs leading-6 text-stone-500">Usalo per segnare la prossima azione condivisa da fare dopo il contatto o l'incontro.</p>
                                </div>
                                <textarea name="follow_up_notes" rows="4" class="km-input" placeholder="Es. invio proposta entro venerdi, richiamo lunedi, conferma nuova data">{{ $sharedFollowUp }}</textarea>
                            @endif
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Nota privata</p>
                                <p class="mt-1 text-xs leading-6 text-stone-500">Promemoria personale visibile solo a te, utile per valutazioni e prossime mosse interne.</p>
                            </div>
                            <textarea name="private_note" rows="4" class="km-input" placeholder="Nota privata solo per te">{{ $privateNote }}</textarea>
                            <button type="submit" class="km-button-secondary w-full">Aggiorna</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div
            id="one-to-one-create-modal"
            class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-950/45 p-4 sm:p-6"
        >
            <div class="mx-auto flex min-h-full w-full max-w-[72rem] items-center justify-center">
                <div class="flex max-h-[calc(100vh-1rem)] w-full flex-col overflow-hidden rounded-[1.75rem] bg-white shadow-[0_30px_80px_rgba(17,24,39,0.22)] sm:max-h-[calc(100vh-2rem)]">
                    <div class="flex shrink-0 items-center justify-between gap-4 border-b border-stone-200 px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Nuova richiesta</p>
                            <h2 class="mt-1 font-serif text-2xl font-semibold text-stone-950">Cerca membro e invia one-to-one</h2>
                        </div>
                        <button type="button" data-close-one-to-one-modal class="shrink-0 rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50">Chiudi</button>
                    </div>

                    <div class="grid min-h-0 flex-1 gap-0 lg:grid-cols-[320px_minmax(0,1fr)]">
                        <div class="flex min-h-0 flex-col overflow-hidden border-b border-stone-200 p-5 lg:border-b-0 lg:border-r">
                            <label class="block text-xs uppercase tracking-[0.18em] text-stone-500">Ricerca membro</label>
                            <input id="one-to-one-member-query" type="text" class="km-input mt-2" placeholder="Nome, cognome, email, azienda">
                            <p class="mt-2 text-xs leading-5 text-stone-500">Cerca per nome, email, azienda o citta'.</p>

                            <div id="one-to-one-member-results" class="mt-3 min-h-0 flex-1 space-y-2 overflow-y-auto pr-1"></div>
                            <p id="one-to-one-member-empty" class="hidden rounded-[1.4rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-5 text-sm text-stone-500">
                                Nessun membro trovato con questa ricerca.
                            </p>
                            <p id="one-to-one-member-idle" class="rounded-[1.4rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-5 text-sm text-stone-500">
                                Scrivi almeno 2 caratteri per cercare un membro.
                            </p>
                        </div>

                        <div class="flex min-h-0 flex-col p-5">
                            @if (session('status') === 'one-to-one-booked')
                                <div class="shrink-0 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                                    One-to-one prenotato: lo slot richiesto era disponibile e senza conflitti.
                                </div>
                            @elseif (session('status') === 'one-to-one-created')
                                <div class="shrink-0 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">
                                    Richiesta inviata: il destinatario dovra' confermare perche' non risultava uno slot prenotabile in quel momento.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('one-to-ones.store') }}" class="mt-3 flex min-h-0 flex-1 flex-col">
                                @csrf
                                <input type="hidden" id="one-to-one-recipient-id" name="recipient_id" value="{{ old('recipient_id', optional($selectedMember)->id) }}">
                                <div class="min-h-0 flex-1 space-y-2.5 overflow-y-auto pr-1">
                                    <div id="one-to-one-selected-member" class="hidden rounded-[1.1rem] border border-[color:var(--km-accent)] bg-[color:var(--km-soft)]/45 px-4 py-2 text-sm text-stone-700"></div>
                                    <div class="rounded-[1.1rem] border border-stone-200 bg-stone-50 px-4 py-2 text-sm text-stone-600">
                                        Se lo slot e' libero, la prenotazione parte subito. Altrimenti invii una proposta da confermare.
                                    </div>
                                    <div class="rounded-[1.1rem] border border-stone-200 bg-white p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Disponibilita' del membro</p>
                                                <p class="mt-1 text-sm text-stone-600">Scegli uno slot o proponi un altro orario.</p>
                                            </div>
                                        </div>
                                        <div id="one-to-one-availability" class="mt-3"></div>
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <input type="datetime-local" id="one-to-one-requested-at" name="requested_at" value="{{ old('requested_at') }}" class="km-input">
                                        <select id="one-to-one-meeting-mode" name="meeting_mode" class="km-input">
                                            <option value="online" @selected(old('meeting_mode', 'online') === 'online')>Online</option>
                                            <option value="in_person" @selected(old('meeting_mode') === 'in_person')>In presenza</option>
                                        </select>
                                    </div>
                                    <p class="text-xs leading-5 text-stone-500">Fuori slot libero: invii una richiesta da confermare.</p>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" class="km-input" placeholder="Link meeting online">
                                        <input type="text" name="meeting_location" value="{{ old('meeting_location') }}" class="km-input" placeholder="Luogo incontro">
                                    </div>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <textarea name="goal" rows="2" class="km-input" placeholder="Obiettivo dell'incontro" required>{{ old('goal') }}</textarea>
                                        <textarea name="pre_notes" rows="2" class="km-input" placeholder="Note pre-incontro">{{ old('pre_notes') }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-3 flex shrink-0 justify-end gap-3 border-t border-stone-200 bg-white pt-3">
                                    <button type="button" data-close-one-to-one-modal class="km-button-secondary">Annulla</button>
                                    <button type="submit" id="one-to-one-submit" class="km-button-primary" disabled>Invia richiesta</button>
                                </div>
                            </form>
                        </div>
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

            if (!modal || !openButton || !queryInput || !resultsContainer || !emptyState || !idleState || !recipientInput || !requestedAtInput || !meetingModeSelect || !submitButton || !availabilityContainer || !selectedMemberSummary) {
                return;
            }

            const normalize = (value) => String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');

            const selectedMember = () => members.find((member) => String(member.id) === String(recipientInput.value)) || null;

            const nextOccurrenceFor = (slot) => {
                const now = new Date();
                const currentWeekday = ((now.getDay() + 6) % 7) + 1;
                let deltaDays = slot.weekday - currentWeekday;

                if (deltaDays < 0) {
                    deltaDays += 7;
                }

                const [hours, minutes] = slot.starts_at.split(':').map(Number);
                const candidate = new Date(now);
                candidate.setHours(hours, minutes, 0, 0);
                candidate.setDate(candidate.getDate() + deltaDays);

                if (deltaDays === 0 && candidate <= now) {
                    candidate.setDate(candidate.getDate() + 7);
                }

                return candidate;
            };

            const toDateTimeLocal = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');

                return `${year}-${month}-${day}T${hours}:${minutes}`;
            };

            const updateSubmitState = () => {
                submitButton.disabled = !recipientInput.value;
            };

            const renderSelectedMemberSummary = () => {
                const member = selectedMember();

                if (!member) {
                    selectedMemberSummary.classList.add('hidden');
                    selectedMemberSummary.innerHTML = '';
                    return;
                }

                selectedMemberSummary.classList.remove('hidden');
                selectedMemberSummary.innerHTML = `
                    <div class="text-xs uppercase tracking-[0.18em] text-stone-500">Membro selezionato</div>
                    <div class="mt-1 font-semibold text-stone-950">${member.name}</div>
                    <div class="text-sm text-stone-600">${member.email}</div>
                    <div class="text-xs text-stone-500">${member.company || 'Azienda non indicata'}${member.city ? ` · ${member.city}` : ''}</div>
                `;
            };

            const renderAvailability = () => {
                const member = selectedMember();

                if (!member) {
                    availabilityContainer.innerHTML = '<div class="rounded-[1.1rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-500">Seleziona un membro per vedere gli slot.</div>';
                    return;
                }

                if (!member.availability_slots.length) {
                    availabilityContainer.innerHTML = '<div class="rounded-[1.1rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-500">Nessuna disponibilita\' pubblicata. Puoi proporre un altro orario.</div>';
                    return;
                }

                availabilityContainer.innerHTML = member.availability_slots.map((slot) => `
                    <button
                        type="button"
                        class="mb-3 flex w-full items-center justify-between gap-4 rounded-[1.2rem] border border-stone-200 px-4 py-3 text-left transition hover:bg-stone-50"
                        data-slot-id="${slot.id}"
                    >
                        <div>
                            <div class="font-semibold text-stone-950">
                                ${weekdayLabels[slot.weekday] || 'Giorno'} · ${slot.starts_at} - ${slot.ends_at}
                            </div>
                            <div class="mt-1 text-sm text-stone-500">
                                ${slot.meeting_mode === 'online' ? 'Online' : 'In presenza'}${slot.location ? ` · ${slot.location}` : ''}
                            </div>
                        </div>
                        <span class="rounded-full bg-[color:var(--km-soft)] px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">
                            Prenota slot
                        </span>
                    </button>
                `).join('');

                availabilityContainer.querySelectorAll('[data-slot-id]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const slot = member.availability_slots.find((item) => String(item.id) === String(button.dataset.slotId));
                        if (!slot) return;
                        meetingModeSelect.value = slot.meeting_mode;
                        requestedAtInput.value = toDateTimeLocal(nextOccurrenceFor(slot));
                    });
                });
            };

            const renderMembers = () => {
                const query = normalize(queryInput.value.trim());

                if (query.length < 2) {
                    resultsContainer.innerHTML = '';
                    emptyState.classList.add('hidden');
                    idleState.classList.remove('hidden');
                    return;
                }

                const filtered = members.filter((member) => [member.name, member.email, member.company, member.city]
                    .some((value) => normalize(value).includes(query)));

                resultsContainer.innerHTML = filtered.map((member) => `
                    <button
                        type="button"
                        class="w-full rounded-[1.2rem] border px-4 py-2.5 text-left transition ${String(recipientInput.value) === String(member.id) ? 'border-[color:var(--km-accent)] bg-[color:var(--km-soft)]/60' : 'border-stone-200 hover:bg-stone-50'}"
                        data-member-id="${member.id}"
                    >
                        <div class="font-semibold text-stone-950">${member.name}</div>
                        <div class="text-sm text-stone-500">${member.email}</div>
                        <div class="text-xs text-stone-500">${member.company || 'Azienda non indicata'}${member.city ? ` · ${member.city}` : ''}</div>
                    </button>
                `).join('');

                idleState.classList.add('hidden');
                emptyState.classList.toggle('hidden', filtered.length > 0);

                resultsContainer.querySelectorAll('[data-member-id]').forEach((button) => {
                    button.addEventListener('click', () => {
                        recipientInput.value = button.dataset.memberId;
                        updateSubmitState();
                        renderSelectedMemberSummary();
                        renderMembers();
                        renderAvailability();
                    });
                });
            };

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                renderMembers();
                renderSelectedMemberSummary();
                renderAvailability();
                updateSubmitState();
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
            queryInput.addEventListener('input', renderMembers);

            renderMembers();
            renderSelectedMemberSummary();
            renderAvailability();
            updateSubmitState();
        })();
    </script>
</x-app-layout>
