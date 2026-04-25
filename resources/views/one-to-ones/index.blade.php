<x-app-layout>

    {{-- HEADER --}}
    <x-slot name="header">
        <div class="km-panel p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Networking tra membri</p>
            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl lg:text-4xl">
                    Richieste one-to-one
                </h1>
                <button
                    type="button"
                    onclick="document.getElementById('modal-create-one-to-one').classList.remove('hidden')"
                    class="km-button-primary shrink-0"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuova richiesta
                </button>
            </div>

            {{-- STATS --}}
            <div class="mt-5 flex flex-wrap gap-3">
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">{{ $summary['total'] }}</span>
                    <span class="text-stone-500">totali</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">{{ $summary['sent'] }}</span>
                    <span class="text-stone-500">inviate</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">{{ $summary['received'] }}</span>
                    <span class="text-stone-500">ricevute</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-16">
        <div class="km-shell mt-6 grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">

            {{-- SIDEBAR --}}
            <aside class="space-y-6">
                <section class="km-panel p-6">
                    <h2 class="text-lg font-semibold text-stone-950">Le mie disponibilità</h2>
                    <p class="mt-1 text-sm text-stone-500">Indica quando sei libero per un incontro.</p>

                    <form method="POST" action="{{ route('one-to-ones.availability.store') }}" class="mt-5 space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Giorno</label>
                            <select name="weekday" class="km-input" required>
                                <option value="">Seleziona giorno…</option>
                                @foreach ($weekdayOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-stone-600">Dalle</label>
                                <input type="time" name="starts_at" class="km-input" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-stone-600">Alle</label>
                                <input type="time" name="ends_at" class="km-input" required>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Modalità</label>
                            <select name="meeting_mode" class="km-input" required>
                                <option value="">Seleziona…</option>
                                <option value="online">Online</option>
                                <option value="in_person">In presenza</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">
                                Luogo <span class="font-normal text-stone-400">(opzionale)</span>
                            </label>
                            <input type="text" name="location" class="km-input" placeholder="Es. Milano, Zoom, Teams…">
                        </div>
                        <button type="submit" class="km-button-secondary w-full">+ Aggiungi disponibilità</button>
                    </form>

                    <div class="mt-5 space-y-2">
                        @forelse ($availabilitySlots as $slot)
                            <div class="flex items-center gap-3 rounded-2xl bg-stone-50 px-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-stone-800">
                                        {{ $weekdayOptions[$slot->weekday] ?? '-' }}
                                        &mdash;
                                        {{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-stone-500">
                                        {{ $slot->meeting_mode === 'online' ? 'Online' : 'In presenza' }}
                                        @if ($slot->location) &bull; {{ $slot->location }} @endif
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('one-to-ones.availability.destroy', $slot) }}" class="shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Rimuovi"
                                        class="flex h-7 w-7 items-center justify-center rounded-full text-stone-400 transition hover:bg-red-50 hover:text-red-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-stone-400">Nessuna disponibilità impostata.</p>
                        @endforelse
                    </div>
                </section>
            </aside>

            {{-- MAIN --}}
            <section class="min-w-0 space-y-5">

                {{-- FLASH --}}
                @if (session('status') === 'one-to-one-booked')
                    <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                        ✓ Incontro confermato! La disponibilità coincideva, la richiesta è stata accettata automaticamente.
                    </div>
                @elseif (session('status') === 'one-to-one-created')
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800">
                        ✓ Richiesta inviata. Riceverai una risposta a breve.
                    </div>
                @elseif (session('status') === 'one-to-one-updated')
                    <div class="rounded-2xl border bg-stone-50 px-5 py-4 text-sm text-stone-700" style="border-color: var(--km-line);">
                        ✓ Aggiornamento salvato.
                    </div>
                @elseif (session('status') === 'availability-created')
                    <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                        ✓ Disponibilità aggiunta.
                    </div>
                @elseif (session('status') === 'availability-deleted')
                    <div class="rounded-2xl border bg-stone-50 px-5 py-4 text-sm text-stone-600" style="border-color: var(--km-line);">
                        Disponibilità rimossa.
                    </div>
                @endif

                {{-- FILTRI --}}
                <div class="km-panel p-5">
                    <form method="GET" action="{{ route('one-to-ones.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                        <div class="flex-1 min-w-[180px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Cerca</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-input" placeholder="Nome, email, obiettivo…">
                        </div>
                        <div class="min-w-[150px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Tipo</label>
                            <select name="type" class="km-input">
                                <option value="">Tutte</option>
                                @foreach ($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[150px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Stato</label>
                            <select name="status" class="km-input">
                                <option value="">Tutti</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[150px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Modalità</label>
                            <select name="meeting_mode" class="km-input">
                                <option value="">Tutte</option>
                                @foreach ($modeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['meeting_mode'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="km-button-primary">Filtra</button>
                            @if (array_filter($filters))
                                <a href="{{ route('one-to-ones.index') }}" class="km-button-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- LISTA RICHIESTE --}}
                <div class="km-panel overflow-hidden p-0">
                    @forelse ($requests as $requestItem)
                        @php
                            $isSent      = $requestItem->requester_id === auth()->id();
                            $otherUser   = $isSent ? $requestItem->recipient : $requestItem->requester;
                            $isSelected  = $selectedRequest?->id === $requestItem->id;
                            $statusColors = [
                                'pending'     => 'bg-amber-50 text-amber-700 border-amber-200',
                                'accepted'    => 'bg-green-50 text-green-700 border-green-200',
                                'declined'    => 'bg-red-50 text-red-700 border-red-200',
                                'rescheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'cancelled'   => 'bg-stone-100 text-stone-500 border-stone-200',
                                'completed'   => 'bg-teal-50 text-teal-700 border-teal-200',
                            ];
                            $statusClass = $statusColors[$requestItem->status?->value ?? ''] ?? 'bg-stone-100 text-stone-500 border-stone-200';
                            $rowUrl      = route('one-to-ones.index', array_merge(
                                array_diff_key(request()->query(), ['request' => null]),
                                ['request' => $requestItem->id]
                            ));
                        @endphp
                        <a
                            href="{{ $rowUrl }}"
                            class="flex items-start gap-4 border-b px-5 py-4 transition hover:bg-stone-50 {{ $isSelected ? 'bg-stone-50 ring-1 ring-inset ring-stone-200' : '' }}"
                            style="border-color: var(--km-line); text-decoration: none; color: inherit;"
                        >
                            <span class="mt-0.5 shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] {{ $isSent ? 'bg-[color:var(--km-soft)] text-[color:var(--km-deep-strong)]' : 'bg-[color:var(--km-accent-soft)] text-[color:var(--km-accent-strong)]' }}">
                                {{ $isSent ? 'Inviata' : 'Ricevuta' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-stone-900">{{ $otherUser?->name ?? 'Utente non disponibile' }}</p>
                                @if ($otherUser?->memberProfile?->company_name)
                                    <p class="text-xs text-stone-500">{{ $otherUser->memberProfile->company_name }}</p>
                                @endif
                                @if ($requestItem->goal)
                                    <p class="mt-1 line-clamp-1 text-sm text-stone-500">{{ $requestItem->goal }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $statusClass }}">
                                    {{ $requestItem->status?->label() ?? '-' }}
                                </span>
                                <p class="mt-1.5 text-xs text-stone-400">{{ optional($requestItem->requested_at)->format('d/m/Y H:i') ?? '-' }}</p>
                                <p class="mt-0.5 text-xs text-stone-400">{{ $requestItem->meeting_mode === 'online' ? '🔗 Online' : '📍 In presenza' }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-14 text-center">
                            <p class="text-stone-500">Nessuna richiesta trovata.</p>
                            @if (array_filter($filters))
                                <a href="{{ route('one-to-ones.index') }}" class="mt-3 inline-block text-sm text-stone-400 underline">Rimuovi i filtri</a>
                            @else
                                <p class="mt-2 text-sm text-stone-400">Inizia inviando la tua prima richiesta.</p>
                                <button type="button"
                                    onclick="document.getElementById('modal-create-one-to-one').classList.remove('hidden')"
                                    class="km-button-primary mt-4">Nuova richiesta</button>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if ($requests->hasPages())
                    <div class="px-1">{{ $requests->links() }}</div>
                @endif

            </section>
        </div>
    </div>

    {{-- PANNELLO DETTAGLIO --}}
    @if ($selectedRequest)
        @php
            $isRequester = $selectedRequest->requester_id === auth()->id();
            $isRecipient = $selectedRequest->recipient_id === auth()->id();
            $counterpart = $isRequester ? $selectedRequest->recipient : $selectedRequest->requester;
            $privateNote = $selectedRequest->notes->first();
            $followUp    = $selectedRequest->followUps->first();
            $remainingQuery = array_diff_key(request()->query(), ['request' => null]);
            $closeUrl = $remainingQuery ? route('one-to-ones.index', $remainingQuery) : route('one-to-ones.index');
            $detailStatusColors = [
                'pending'     => 'bg-amber-50 text-amber-700 border-amber-200',
                'accepted'    => 'bg-green-50 text-green-700 border-green-200',
                'declined'    => 'bg-red-50 text-red-700 border-red-200',
                'rescheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                'cancelled'   => 'bg-stone-100 text-stone-500 border-stone-200',
                'completed'   => 'bg-teal-50 text-teal-700 border-teal-200',
            ];
            $detailStatusClass = $detailStatusColors[$selectedRequest->status?->value ?? ''] ?? 'bg-stone-100 text-stone-500 border-stone-200';
        @endphp

        <div onclick="if(event.target===this) window.location.href='{{ $closeUrl }}'"
             class="fixed inset-0 z-40 bg-black/40"></div>

        <div class="fixed inset-y-0 right-0 z-50 flex w-full max-w-lg flex-col overflow-y-auto bg-white shadow-2xl lg:rounded-l-[2rem]">
            {{-- HEADER PANNELLO --}}
            <div class="flex items-start justify-between gap-4 border-b p-6" style="border-color: var(--km-line);">
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">
                        {{ $isRequester ? 'Richiesta inviata a' : 'Richiesta ricevuta da' }}
                    </p>
                    <h3 class="mt-1 text-xl font-semibold text-stone-950">{{ $counterpart?->name ?? 'Utente' }}</h3>
                    @if ($counterpart?->memberProfile?->company_name)
                        <p class="text-sm text-stone-500">{{ $counterpart->memberProfile->company_name }}</p>
                    @endif
                </div>
                <a href="{{ $closeUrl }}"
                   class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-500 transition hover:bg-stone-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>

            {{-- BODY PANNELLO --}}
            <div class="flex-1 space-y-6 p-6">

                {{-- RIEPILOGO --}}
                <div class="rounded-2xl bg-stone-50 p-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Stato</span>
                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $detailStatusClass }}">
                            {{ $selectedRequest->status?->label() ?? '-' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Data proposta</span>
                        <span class="font-medium text-stone-800">{{ optional($selectedRequest->requested_at)->format('d/m/Y \a\l\l\e H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Modalità</span>
                        <span class="font-medium text-stone-800">{{ $selectedRequest->meeting_mode === 'online' ? '🔗 Online' : '📍 In presenza' }}</span>
                    </div>
                    @if ($selectedRequest->meeting_link)
                        <div class="flex items-center justify-between gap-4">
                            <span class="shrink-0 text-stone-500">Link</span>
                            <a href="{{ $selectedRequest->meeting_link }}" target="_blank"
                               class="truncate text-xs underline" style="color: var(--km-accent-strong);">{{ $selectedRequest->meeting_link }}</a>
                        </div>
                    @endif
                    @if ($selectedRequest->meeting_location)
                        <div class="flex items-center justify-between">
                            <span class="text-stone-500">Luogo</span>
                            <span class="font-medium text-stone-800">{{ $selectedRequest->meeting_location }}</span>
                        </div>
                    @endif
                </div>

                @if ($selectedRequest->goal)
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Obiettivo</p>
                        <p class="text-sm leading-relaxed text-stone-700">{{ $selectedRequest->goal }}</p>
                    </div>
                @endif

                @if ($selectedRequest->pre_notes)
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Note iniziali</p>
                        <p class="text-sm leading-relaxed text-stone-700">{{ $selectedRequest->pre_notes }}</p>
                    </div>
                @endif

                @if ($selectedRequest->post_notes)
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Note post incontro</p>
                        <p class="text-sm leading-relaxed text-stone-700">{{ $selectedRequest->post_notes }}</p>
                    </div>
                @endif

                @if ($followUp)
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Follow-up</p>
                        <p class="text-sm leading-relaxed text-stone-700">{{ $followUp->content }}</p>
                    </div>
                @endif

                {{-- AZIONI DESTINATARIO --}}
                @if ($isRecipient && in_array($selectedRequest->status?->value, ['pending', 'rescheduled']))
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Risposta</p>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" class="flex-1">
                                @csrf @method('PATCH')
                                <button type="submit" name="status" value="accepted" class="km-button-primary w-full">✓ Accetta</button>
                            </form>
                            <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}" class="flex-1">
                                @csrf @method('PATCH')
                                <button type="submit" name="status" value="declined" class="km-button-secondary w-full">✗ Rifiuta</button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($isRecipient && $selectedRequest->status?->value === 'accepted')
                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" name="status" value="completed" class="km-button-secondary w-full">✓ Segna come completato</button>
                    </form>
                @endif

                @if ($isRequester && $selectedRequest->status?->value === 'pending')
                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}">
                        @csrf @method('PATCH')
                        <button type="submit" name="status" value="cancelled"
                            onclick="return confirm('Annullare la richiesta?')"
                            class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-100">
                            Annulla richiesta
                        </button>
                    </form>
                @endif

                {{-- NOTA PRIVATA --}}
                <div class="border-t pt-5" style="border-color: var(--km-line);">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">
                        Nota privata <span class="font-normal normal-case text-stone-400">(solo tu la vedi)</span>
                    </p>
                    <form method="POST" action="{{ route('one-to-ones.status', $selectedRequest) }}">
                        @csrf @method('PATCH')
                        <textarea name="private_note" rows="3" class="km-input text-sm"
                            placeholder="Appunti personali su questo incontro…">{{ $privateNote?->note }}</textarea>
                        <button type="submit" class="km-button-secondary mt-2 w-full">Salva nota</button>
                    </form>
                </div>

            </div>
        </div>
    @endif

    {{-- MODAL NUOVA RICHIESTA --}}
    <div id="modal-create-one-to-one"
         class="{{ $errors->hasAny(['recipient_id', 'goal', 'meeting_mode', 'meeting_link']) ? '' : 'hidden' }} fixed inset-0 z-[60] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50"
             onclick="document.getElementById('modal-create-one-to-one').classList.add('hidden')"></div>
        <div class="relative z-10 w-full max-w-xl overflow-y-auto rounded-[2rem] bg-white p-6 shadow-2xl" style="max-height: 90vh;">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-stone-950">Nuova richiesta one-to-one</h3>
                <button type="button"
                    onclick="document.getElementById('modal-create-one-to-one').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-stone-100 text-stone-500 hover:bg-stone-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('one-to-ones.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-stone-700">Membro *</label>
                    <select name="recipient_id" class="km-input" required>
                        <option value="">Seleziona un membro…</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected(old('recipient_id') == $member->id)>
                                {{ $member->name }}@if ($member->memberProfile?->company_name) — {{ $member->memberProfile->company_name }}@endif
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('recipient_id')" class="mt-1" />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-stone-700">Obiettivo dell'incontro *</label>
                    <textarea name="goal" rows="3" class="km-input" placeholder="Cosa vuoi ottenere da questo incontro?" required>{{ old('goal') }}</textarea>
                    <x-input-error :messages="$errors->get('goal')" class="mt-1" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-stone-700">Modalità *</label>
                        <select name="meeting_mode" class="km-input" required>
                            <option value="">Seleziona…</option>
                            <option value="online" @selected(old('meeting_mode') === 'online')>Online</option>
                            <option value="in_person" @selected(old('meeting_mode') === 'in_person')>In presenza</option>
                        </select>
                        <x-input-error :messages="$errors->get('meeting_mode')" class="mt-1" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-stone-700">Data proposta <span class="font-normal text-stone-400">(opz.)</span></label>
                        <input type="datetime-local" name="requested_at" value="{{ old('requested_at') }}" class="km-input">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-stone-700">Link meeting <span class="font-normal text-stone-400">(opzionale)</span></label>
                    <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" class="km-input" placeholder="https://meet.google.com/…">
                    <x-input-error :messages="$errors->get('meeting_link')" class="mt-1" />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-stone-700">Luogo <span class="font-normal text-stone-400">(opzionale)</span></label>
                    <input type="text" name="meeting_location" value="{{ old('meeting_location') }}" class="km-input" placeholder="Es. Caffè XY, Milano">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-stone-700">Note aggiuntive <span class="font-normal text-stone-400">(opzionale)</span></label>
                    <textarea name="pre_notes" rows="2" class="km-input" placeholder="Qualcosa da sapere prima dell'incontro?">{{ old('pre_notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="km-button-primary flex-1">Invia richiesta</button>
                    <button type="button"
                        onclick="document.getElementById('modal-create-one-to-one').classList.add('hidden')"
                        class="km-button-secondary">Annulla</button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
