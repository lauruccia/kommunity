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
                        <span class="rounded-full bg-white/15 px-4 py-2 text-sm">{{ $event->chapter?->name ?? 'Community' }}</span>
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
                            <span class="rounded-full bg-sky-100 px-4 py-2 text-sm text-sky-700">Mi interessa: {{ $registrationStats[\App\Enums\EventAttendanceStatus::Interested->value] }}</span>
                            <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm text-emerald-700">Parteciperò: {{ $registrationStats[\App\Enums\EventAttendanceStatus::Attending->value] }}</span>
                            <span class="rounded-full bg-stone-200 px-4 py-2 text-sm text-stone-700">Non mi interessa: {{ $registrationStats[\App\Enums\EventAttendanceStatus::NotInterested->value] }}</span>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto rounded-[1.75rem] border border-white/10">
                        <table class="min-w-[42rem] divide-y divide-stone-200 text-sm sm:min-w-full">
                            <thead class="bg-white/[.045] text-left uppercase tracking-[0.18em] text-white/60">
                                <tr>
                                    <th class="px-5 py-4 font-medium">Membro</th>
                                    <th class="px-5 py-4 font-medium">Azienda</th>
                                    <th class="px-5 py-4 font-medium">Risposta</th>
                                    <th class="px-5 py-4 font-medium">Aggiornato</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white/10">
                                @forelse ($event->registrations as $registration)
                                @php $statusEnum = \App\Enums\EventAttendanceStatus::tryFrom($registration->status); @endphp
                                <tr>
                                    <td class="px-5 py-4 font-medium text-white">{{ $registration->user->name }}</td>
                                    <td class="px-5 py-4 text-white/75">{{ $registration->user->memberProfile?->company_name ?? 'Non indicata' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusEnum?->badgeClasses() ?? 'bg-white/[.075] text-white/80' }}">
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
                @foreach (['event-full' => ['amber','Posti esauriti per questo evento.'], 'event-response-updated' => ['emerald','La tua risposta è stata aggiornata.'], 'event-unregistered' => ['stone','Partecipazione annullata.']] as $key => [$color, $msg])
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
                        <form method="POST" action="{{ route('events.register', $event) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button type="submit" class="w-full rounded-full border px-4 py-3 text-sm font-semibold transition
                                {{ $currentStatus === $status->value || ($currentStatus === 'registered' && $status->value === 'attending')
                                    ? 'border-emerald-600 bg-emerald-600 text-white'
                                    : 'border-white/10 bg-white/10 text-white/80 hover:bg-white/15' }}">
                                {{ $status->label() }}
                            </button>
                        </form>
                        @endforeach
                    </div>

                    @if ($currentRegistration)
                    <form method="POST" action="{{ route('events.unregister', $event) }}" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="km-button-secondary w-full">Annulla partecipazione</button>
                    </form>
                    @endif

                    <div class="mt-5 space-y-1 text-sm text-white/65">
                        <div>Organizzatore: <span class="text-white/80">{{ $event->organizer?->name ?? 'Team Kommunity' }}</span></div>
                        <div>Pianeta: <span class="text-white/80">{{ $event->chapter?->name ?? 'Community' }}</span></div>
                    </div>
                </div>

                {{-- Gestione evento (solo manager) --}}
                @if ($canManageEvent)
                <div class="km-portal-panel p-6">
                    <div class="flex items-center justify-between">
                        <p class="text-xs uppercase tracking-[0.24em] text-white/60">Gestione evento</p>
                        <a href="/admin/events/{{ $event->id }}/edit" class="rounded-full border border-white/15 px-3 py-1.5 text-xs text-white/70 transition hover:bg-white/[.07]">Backoffice</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        <a href="/admin/events/{{ $event->id }}/edit" class="km-button-primary block w-full text-center text-sm">Modifica evento</a>
                    </div>
                </div>

                {{-- Pannello inviti --}}
                <div
                    class="km-portal-panel p-6"
                    x-data="{
                        open: false,
                        target: 'all',
                        userSearch: '',
                        selectedIds: [],
                        allUsers: {{ \Illuminate\Support\Js::from($allUsers->values()) }},
                        get filtered() {
                            if (!this.userSearch) return this.allUsers.slice(0, 30);
                            const q = this.userSearch.toLowerCase();
                            return this.allUsers.filter(u => u.label.toLowerCase().includes(q)).slice(0, 30);
                        },
                        toggle(id) {
                            const i = this.selectedIds.indexOf(id);
                            if (i === -1) this.selectedIds.push(id); else this.selectedIds.splice(i, 1);
                        }
                    }"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Invita partecipanti</p>
                            <p class="mt-1 text-sm text-white/50">{{ $invitationCount }} inviti già inviati</p>
                        </div>
                        <button @click="open = !open" class="rounded-full border border-white/15 px-3 py-1.5 text-xs font-semibold text-white/70 transition hover:bg-white/[.07]">
                            <span x-text="open ? 'Nascondi' : 'Invia inviti'"></span>
                        </button>
                    </div>

                    <div x-show="open" x-transition class="mt-5 space-y-4">
                        <form method="POST" action="{{ route('events.invite', $event) }}" class="space-y-4">
                            @csrf

                            <div class="text-[10px] uppercase tracking-[0.18em] text-white/40">Destinatari</div>
                            <div class="grid grid-cols-4 gap-1.5">
                                @foreach ([
                                    'all'        => 'Tutti',
                                    'chapter'    => 'Pianeta',
                                    'profession' => 'Professione',
                                    'category'   => 'Categoria',
                                    'city'       => 'Città',
                                    'region'     => 'Regione',
                                    'users'      => 'Singoli',
                                ] as $val => $lbl)
                                <label
                                    class="flex cursor-pointer items-center justify-center rounded-xl border px-2 py-2 text-xs font-semibold transition"
                                    :class="target === '{{ $val }}'
                                        ? 'border-[color:var(--km-accent)] bg-[color:var(--km-accent)]/15 text-white'
                                        : 'border-white/10 bg-white/[.03] text-white/55 hover:text-white'"
                                >
                                    <input type="radio" name="invite_target" value="{{ $val }}" x-model="target" class="sr-only">
                                    {{ $lbl }}
                                </label>
                                @endforeach
                            </div>

                            {{-- Pianeta --}}
                            <div x-show="target === 'chapter'" x-cloak>
                                <select name="invite_chapter_id" class="km-portal-input w-full">
                                    <option value="">Scegli pianeta</option>
                                    @foreach ($managedChapters as $ch)
                                    <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Professione --}}
                            <div x-show="target === 'profession'" x-cloak>
                                <select name="invite_profession_id" class="km-portal-input w-full">
                                    <option value="">Scegli professione</option>
                                    @foreach ($professions as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Categoria --}}
                            <div x-show="target === 'category'" x-cloak>
                                <select name="invite_category_id" class="km-portal-input w-full">
                                    <option value="">Scegli categoria</option>
                                    @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Città --}}
                            <div x-show="target === 'city'" x-cloak>
                                <select name="invite_city_id" class="km-portal-input w-full">
                                    <option value="">Scegli città</option>
                                    @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Regione --}}
                            <div x-show="target === 'region'" x-cloak>
                                <select name="invite_region_id" class="km-portal-input w-full">
                                    <option value="">Scegli regione</option>
                                    @foreach ($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Singoli utenti --}}
                            <div x-show="target === 'users'" x-cloak class="space-y-2">
                                <input type="text" x-model="userSearch" class="km-portal-input w-full" placeholder="Cerca per nome o azienda…">
                                <div class="max-h-44 overflow-y-auto rounded-2xl border border-white/10 bg-white/[.02]">
                                    <template x-for="u in filtered" :key="u.id">
                                        <label
                                            class="flex cursor-pointer items-center gap-3 border-b border-white/[.06] px-3 py-2.5 transition last:border-b-0 hover:bg-white/[.05]"
                                            :class="selectedIds.includes(u.id) ? 'bg-[color:var(--km-accent)]/10' : ''"
                                        >
                                            <input
                                                type="checkbox"
                                                name="invite_user_ids[]"
                                                :value="u.id"
                                                @change="toggle(u.id)"
                                                :checked="selectedIds.includes(u.id)"
                                                class="rounded border-white/30 bg-white/10 text-[color:var(--km-accent)]"
                                            >
                                            <span class="text-sm text-white/75" x-text="u.label"></span>
                                        </label>
                                    </template>
                                </div>
                                <p x-show="selectedIds.length > 0" class="text-xs text-white/45" x-text="selectedIds.length + ' utenti selezionati'"></p>
                            </div>

                            <div class="rounded-2xl border border-white/[.07] bg-white/[.015] px-4 py-3 text-xs text-white/45">
                                📧 Gli utenti non ancora invitati riceveranno email + notifica interna.
                            </div>

                            <button type="submit" class="km-button-primary w-full py-3 text-sm">Invia inviti</button>
                        </form>
                    </div>
                </div>
                @endif

            </aside>
        </div>
    </div>
</x-app-layout>
