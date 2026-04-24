<x-app-layout>
    <x-slot name="header">
        <div class="km-panel overflow-hidden">
            <div class="bg-[linear-gradient(135deg,rgba(74,97,118,0.98),rgba(102,138,91,0.92))] px-6 py-7 text-white">
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

    <div class="pb-12">
        <div class="km-shell grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <section class="space-y-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Quando</p>
                        <p class="mt-2 text-sm text-stone-900">{{ $event->starts_at->format('d/m/Y H:i') }}</p>
                        @if ($event->ends_at)
                            <p class="mt-1 text-sm text-stone-500">Fine: {{ $event->ends_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Dove</p>
                        <p class="mt-2 text-sm text-stone-900">{{ $event->location ?: 'Online' }}</p>
                        @if ($event->meeting_url)
                            <a href="{{ $event->meeting_url }}" target="_blank" class="mt-2 inline-block text-sm text-emerald-700">Apri link meeting</a>
                        @endif
                    </div>
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Capienza</p>
                        <p class="mt-2 text-sm text-stone-900">{{ $event->capacity ?: 'Aperta' }}</p>
                        <p class="mt-1 text-sm text-stone-500">
                            Posti disponibili:
                            {{ $event->capacity ? max($event->capacity - $registrationStats[\App\Enums\EventAttendanceStatus::Attending->value], 0) : 'Illimitati' }}
                        </p>
                    </div>
                </div>

                <div class="km-panel p-6">
                    <div class="flex flex-col gap-4 border-b border-stone-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Risposte</p>
                            <h2 class="mt-2 font-serif text-3xl font-semibold text-stone-950">Partecipazione e interesse</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-sky-100 px-4 py-2 text-sm text-sky-700">Mi interessa: {{ $registrationStats[\App\Enums\EventAttendanceStatus::Interested->value] }}</span>
                            <span class="rounded-full bg-emerald-100 px-4 py-2 text-sm text-emerald-700">Parteciperò: {{ $registrationStats[\App\Enums\EventAttendanceStatus::Attending->value] }}</span>
                            <span class="rounded-full bg-stone-200 px-4 py-2 text-sm text-stone-600">Non mi interessa: {{ $registrationStats[\App\Enums\EventAttendanceStatus::NotInterested->value] }}</span>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto rounded-[1.75rem] border border-stone-200">
                        <table class="min-w-[42rem] divide-y divide-stone-200 text-sm sm:min-w-full">
                            <thead class="bg-stone-50 text-left uppercase tracking-[0.18em] text-stone-500">
                                <tr>
                                    <th class="px-5 py-4 font-medium">Membro</th>
                                    <th class="px-5 py-4 font-medium">Azienda</th>
                                    <th class="px-5 py-4 font-medium">Risposta</th>
                                    <th class="px-5 py-4 font-medium">Aggiornato</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @forelse ($event->registrations as $registration)
                                    @php
                                        $statusEnum = \App\Enums\EventAttendanceStatus::tryFrom($registration->status);
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-stone-900">{{ $registration->user->name }}</td>
                                        <td class="px-5 py-4 text-stone-600">{{ $registration->user->memberProfile?->company_name ?? 'Non indicata' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusEnum?->badgeClasses() ?? 'bg-stone-100 text-stone-700' }}">
                                                {{ $statusEnum?->label() ?? $registration->status }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-stone-500">{{ $registration->updated_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-6 text-center text-stone-500">Nessuna risposta registrata.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                @if (session('status') === 'event-full')
                    <div class="rounded-[1.75rem] border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                        Posti esauriti per questo evento.
                    </div>
                @endif

                @if (session('status') === 'event-response-updated')
                    <div class="rounded-[1.75rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                        La tua risposta all'evento è stata aggiornata.
                    </div>
                @endif

                @if (session('status') === 'event-unregistered')
                    <div class="rounded-[1.75rem] border border-stone-200 bg-stone-50 px-5 py-4 text-sm text-stone-700">
                        La tua partecipazione è stata annullata.
                    </div>
                @endif

                <div class="km-panel p-6">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">La tua risposta</p>
                    @php
                        $currentStatus = $currentRegistration?->status;
                    @endphp
                        <div class="mt-4 grid gap-2">
                            @foreach ([\App\Enums\EventAttendanceStatus::Interested, \App\Enums\EventAttendanceStatus::NotInterested, \App\Enums\EventAttendanceStatus::Attending] as $status)
                                <form method="POST" action="{{ route('events.register', $event) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $status->value }}">
                                <button type="submit" class="w-full rounded-full border px-4 py-3 text-sm font-semibold {{ $currentStatus === $status->value || ($currentStatus === 'registered' && $status->value === 'attending') ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-200 bg-white text-stone-700' }}">
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

                    <div class="mt-5 space-y-2 text-sm text-stone-600">
                        <div>Organizzatore: {{ $event->organizer?->name ?? 'Team Kommunity' }}</div>
                        <div>Pianeta: {{ $event->chapter?->name ?? 'Community' }}</div>
                    </div>
                </div>

                @if ($canManageEvent)
                    <div class="km-panel p-6">
                        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Gestione evento</p>
                        <div class="mt-4 space-y-3 text-sm text-stone-700">
                            <a href="/admin/events/{{ $event->id }}/edit" class="km-button-primary w-full text-center">Modifica evento</a>
                            <a href="/admin/events" class="km-button-secondary w-full text-center">Apri elenco eventi</a>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
