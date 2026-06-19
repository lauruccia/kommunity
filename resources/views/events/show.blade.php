<x-app-layout>
    <x-slot name="header">
        <div class="km-portal-panel overflow-hidden">
            {{-- Cover image a tutta larghezza --}}
            @if ($event->coverImageUrl())
            <div class="relative h-52 w-full overflow-hidden sm:h-64">
                <img src="{{ $event->coverImageUrl() }}" alt="{{ $event->title }}" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
            </div>
            @endif

            <div class="{{ $event->coverImageUrl() ? 'bg-transparent -mt-16 relative z-10' : '' }} bg-[linear-gradient(135deg,rgba(74,97,118,0.98),rgba(102,138,91,0.92))] px-6 py-7 text-white">
                <p class="text-xs uppercase tracking-[0.24em] text-white/70">Dettaglio evento</p>
                <div class="mt-4 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <h1 class="font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl">{{ $event->title }}</h1>
                        <p class="mt-3 max-w-4xl text-sm leading-7 text-white/80">{{ $event->description }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/15 px-4 py-2 text-sm">{{ $event->type->label() }}</span>
                        <span class="rounded-full bg-white/15 px-4 py-2 text-sm">{{ $event->chapter?->name ?? 'Kommunity' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="km-portal-bg km-portal-page pb-12 pt-6">
        <div class="km-shell grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">

            {{-- ════════════════════════════════════════
                 COLONNA SINISTRA: info + partecipanti
            ════════════════════════════════════════ --}}
            <section class="space-y-6">

                {{-- Flash messages --}}
                @if (session('status') === 'invitations-sent')
                <div class="rounded-[1.75rem] border border-emerald-400/30 bg-emerald-500/15 px-5 py-4 text-sm font-semibold text-emerald-200">
                    ✓ Inviti inviati con successo ({{ session('invite_count', 0) }} notifiche).
                </div>
                @endif

                {{-- Schede: quando / dove / capienza --}}
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="km-portal-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/60">Quando</p>
                        <p class="mt-2 text-sm text-white">{{ $event->starts_at->format('d/m/Y H:i') }}</p>
                        @if ($event->ends_at)
                        <p class="mt-1 text-sm text-white/60">Fine: {{ $event->ends_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                    <div class="km-portal-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/60">Dove</p>
                        <p class="mt-2 text-sm text-white">{{ $event->location ?: 'Online' }}</p>
                        @if ($event->meeting_url)
                        <a href="{{ $event->meeting_url }}" target="_blank" class="mt-2 inline-block text-sm font-medium text-emerald-300 underline decoration-emerald-300/40 underline-offset-4">
                            Apri link meeting
                        </a>
                        @endif
                    </div>
                    <div class="km-portal-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/60">Capienza</p>
                        <p class="mt-2 text-sm text-white">{{ $event->capacity ?: 'Aperta' }}</p>
                        <p class="mt-1 text-sm text-white/60">
                            Posti disponibili:
                            {{ $event->capacity ? max($event->capacity - $registrationStats[\App\Enums\EventAttendanceStatus::Attending->value], 0) : 'Illimitati' }}
                        </p>
                    </div>
                </div>

                {{-- Partecipazione --}}
                <div class="km-portal-panel p-6">
                    <div class="flex flex-col gap-4 border-b border-white/10 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Risposte</p>
                            <h2 class="mt-2 font-serif text-3xl font-semibold text-white">Partecipazione</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full px-4 py-2 text-sm font-medium" style="background:rgba(56,189,248,0.18);color:#7dd3fc;border:1px solid rgba(56,189,248,0.25);">Mi interessa: {{ $registrationStats[\App\Enums\EventAttendanceStatus::Interested->value] }}</span>
                            <span class="rounded-full px-4 py-2 text-sm font-medium" style="background:rgba(52,211,153,0.18);color:#6ee7b7;border:1px solid rgba(52,211,153,0.25);">Parteciperò: {{ $registrationStats[\App\Enums\EventAttendanceStatus::Attending->value] }}</span>
                            <span class="rounded-full px-4 py-2 text-sm font-medium" style="background:rgba(255,255,255,0.10);color:rgba(255,255,255,0.55);border:1px solid rgba(255,255,255,0.15);">Non mi interessa: {{ $registrationStats[\App\Enums\EventAttendanceStatus::NotInterested->value] }}</span>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto rounded-[1.75rem] border border-white/10">
                        <table class="min-w-[42rem] divide-y divide-white/10 text-sm sm:min-w-full">
                            <thead class="bg-white/[.045] text-left uppercase tracking-[0.18em] text-white/60">
                                <tr>
                                    <th class="px-5 py-4 font-medium">Utente</th>
                                    <th class="px-5 py-4 font-medium">Azienda</th>
                                    <th class="px-5 py-4 font-medium">Risposta</th>
                                    <th class="px-5 py-4 font-medium">Aggiornato</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white/10">
                                @forelse ($event->registrations as $registration)
                                @php $statusEnum = \App\Enums\EventAttendanceStatus::tryFrom($registration->status); @endphp
                                <tr>
                                    <td class="px-5 py-4 font-medium text-white">{{ $registration->user?->name ?? 'Utente eliminato' }}</td>
                                    <td class="px-5 py-4 text-white/75">{{ $registration->user?->memberProfile?->company_name ?? 'Non indicata' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold"
                                              style="{{ $statusEnum?->badgeStyle() ?? 'background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.60);border:1px solid rgba(255,255,255,0.15);' }}">
                                            {{ $statusEnum?->label() ?? $registration->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-white/60">{{ $registration->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="px-5 py-6 text-center text-white/60">Nessuna risposta registrata.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- ════════════════════════════════════════
                 COLONNA DESTRA: azioni + gestione
            ════════════════════════════════════════ --}}
            <aside class="space-y-6">

                {{-- Flash --}}
                @foreach (['event-full' => ['amber','Posti esauriti per questo evento.'], 'event-response-updated' => ['emerald','La tua risposta è stata aggiornata.'], 'event-unregistered' => ['stone','Partecipazione annullata.'], 'event-cancelled' => ['red','Evento annullato.']] as $key => [$color, $msg])
                @if (session('status') === $key)
                <div class="rounded-[1.75rem] border border-{{ $color }}-200 bg-{{ $color }}-50 px-5 py-4 text-sm text-{{ $color }}-800">{{ $msg }}</div>
                @endif
                @endforeach

                {{-- La tua risposta --}}
                <div class="km-portal-panel p-6">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/60">La tua risposta</p>
                    @php $currentStatus = $currentRegistration?->status; @endphp
                    <div class="mt-4 grid gap-2">
                        @foreach ([\App\Enums\EventAttendanceStatus::Interested, \App\Enums\EventAttendanceStatus::NotInterested, \App\Enums\EventAttendanceStatus::Attending] as $status)
                        @php
                            $isActive = $currentStatus === $status->value
                                || ($currentStatus === 'registered' && $status->value === 'attending');
                        @endphp
                        <form method="POST" action="{{ route('events.register', $event) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button type="submit"
                                class="w-full rounded-full border px-4 py-3 text-sm font-semibold transition"
                                style="{{ $isActive
                                    ? 'background:#059669;border-color:#059669;color:#ffffff;'
                                    : 'background:rgba(255,255,255,0.10);border-color:rgba(255,255,255,0.20);color:#f1f5f9;' }}">
                                {{ $status->label() }}
                            </button>
                        </form>
                        @endforeach
                    </div>

                    @if ($currentRegistration)
                    <form method="POST" action="{{ route('events.unregister', $event) }}" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full rounded-full border px-4 py-3 text-sm font-semibold transition"
                            style="background:transparent;border-color:rgba(255,255,255,0.15);color:rgba(255,255,255,0.45);"
                            onmouseover="this.style.borderColor='rgba(248,113,113,0.5)';this.style.color='#fca5a5';"
                            onmouseout="this.style.borderColor='rgba(255,255,255,0.15)';this.style.color='rgba(255,255,255,0.45)';">
                            Rimuovi partecipazione
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Gestione evento (solo manager) --}}
                @if ($canManageEvent)
                <div class="km-portal-panel p-6">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/60">Gestione</p>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ url('/admin/events/' . $event->id . '/edit') }}"
                           class="block w-full rounded-full border border-white/10 bg-white/10 px-4 py-3 text-center text-sm font-semibold text-white/80 transition hover:bg-white/15">
                            Modifica evento
                        </a>

                        @if ($event->status !== 'cancelled')
                        <form method="POST" action="{{ route('events.cancel', $event) }}"
                              onsubmit="return confirm('Sei sicuro di voler annullare questo evento?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full rounded-full border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 transition hover:bg-red-500/20">
                                Annulla evento
                            </button>
                        </form>
                        @else
                        <p class="rounded-full border border-red-400/30 bg-red-500/10 px-4 py-3 text-center text-sm font-semibold text-red-300">
                            Evento annullato
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Inviti (solo manager) --}}
                @if ($allUsers->isNotEmpty())
                <div class="km-portal-panel p-6" x-data="{
                    open: false,
                    userSearch: '',
                    selectedUserIds: [],
                    get filteredUsers() {
                        const q = this.userSearch.toLowerCase();
                        return {{ Js::from($allUsers) }}.filter(u =>
                            !q || u.label.toLowerCase().includes(q)
                        );
                    },
                    toggle(id) {
                        const idx = this.selectedUserIds.indexOf(id);
                        if (idx === -1) this.selectedUserIds.push(id);
                        else this.selectedUserIds.splice(idx, 1);
                    }
                }">
                    <div class="flex items-center justify-between">
                        <p class="text-xs uppercase tracking-[0.24em] text-white/60">Inviti</p>
                        <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-white/60">{{ $invitationCount }} inviati</span>
                    </div>

                    <button @click="open = !open" class="mt-4 w-full rounded-full border border-white/10 bg-white/10 px-4 py-3 text-sm font-semibold text-white/80 transition hover:bg-white/15">
                        <span x-text="open ? 'Chiudi' : 'Invita utenti'"></span>
                    </button>

                    <div x-show="open" x-transition class="mt-4 space-y-3">
                        <input type="text" x-model.debounce.200ms="userSearch"
                               placeholder="Cerca utente..."
                               class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-1 focus:ring-emerald-500">

                        <div class="max-h-48 space-y-1 overflow-y-auto">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-white/5">
                                    <input type="checkbox" :value="user.id"
                                           @change="toggle(user.id)"
                                           :checked="selectedUserIds.includes(user.id)"
                                           class="rounded border-white/20 bg-white/10 text-emerald-500">
                                    <span class="text-sm text-white/80" x-text="user.label"></span>
                                </label>
                            </template>
                        </div>

                        <form method="POST" action="{{ route('events.invite', $event) }}">
                            @csrf
                            <template x-for="id in selectedUserIds" :key="id">
                                <input type="hidden" name="user_ids[]" :value="id">
                            </template>
                            <button type="submit"
                                    :disabled="selectedUserIds.length === 0"
                                    class="w-full rounded-full bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:opacity-40">
                                Invia inviti (<span x-text="selectedUserIds.length"></span>)
                            </button>
                        </form>
                    </div>
                </div>
                @endif
                @endif

            </aside>
        </div>
    </div>
</x-app-layout>
