<x-app-layout>
    <?php
        $weekdayLabels = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
        $miniWeekdayLabels = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];
    ?>

    <div
        class="pb-10 pt-6"
        x-data="{
            open: false,
            event: null,
            events: {{ \Illuminate\Support\Js::from($quickEvents) }},
            showEvent(id) {
                this.event = this.events[id] ?? null;
                this.open = !! this.event;
            },
            closeEvent() {
                this.open = false;
                this.event = null;
            }
        }"
    >
        <div class="km-shell">
            <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_18px_50px_rgba(60,79,94,0.10)]">
                <div class="border-b border-slate-200 px-5 py-4">
                    <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="km-brand-mark km-brand-mark-sm">
                                <x-application-logo />
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-[0.24em] text-stone-400">Calendario professionale</div>
                                <h1 class="text-2xl font-semibold text-stone-950">Eventi community</h1>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('events.index', ['view' => $viewMode, 'month' => now()->format('Y-m'), 'day' => now()->format('Y-m-d')]) }}" class="km-button-secondary px-4 py-2">Oggi</a>
                            <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->copy()->subMonth()->format('Y-m'), 'day' => $selectedDay->copy()->subMonthNoOverflow()->format('Y-m-d')]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                            <div class="min-w-[220px] text-center">
                                <div class="text-xs uppercase tracking-[0.18em] text-stone-400">
                                    {{ $viewMode === 'month' ? 'Vista mese' : ($viewMode === 'week' ? 'Vista settimana' : 'Vista giorno') }}
                                </div>
                                <div class="text-2xl font-semibold text-stone-950">
                                    @if ($viewMode === 'month')
                                        {{ $monthDate->translatedFormat('F Y') }}
                                    @elseif ($viewMode === 'week')
                                        {{ $weekStart->translatedFormat('d M') }} - {{ $weekEnd->translatedFormat('d M Y') }}
                                    @else
                                        {{ $selectedDay->translatedFormat('d F Y') }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->copy()->addMonth()->format('Y-m'), 'day' => $selectedDay->copy()->addMonthNoOverflow()->format('Y-m-d')]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            </a>

                            <div class="ml-0 flex overflow-hidden rounded-full border border-slate-300 bg-stone-50 2xl:ml-3">
                                @foreach (['month' => 'Mese', 'week' => 'Settimana', 'day' => 'Giorno'] as $mode => $label)
                                    <a
                                        href="{{ route('events.index', ['view' => $mode, 'month' => $monthDate->format('Y-m'), 'day' => $selectedDay->format('Y-m-d')]) }}"
                                        class="px-4 py-2 text-sm font-semibold {{ $viewMode === $mode ? 'bg-[color:var(--km-deep)] text-white' : 'text-slate-700' }}"
                                    >
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid xl:grid-cols-[300px_minmax(0,1fr)]">
                    <aside class="border-b border-slate-200 bg-stone-50 p-5 xl:border-b-0 xl:border-r">
                        <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold text-stone-900">{{ $monthDate->translatedFormat('F Y') }}</div>
                                <div class="text-xs text-stone-400">{{ $selectedDayEvents->count() }} eventi</div>
                            </div>

                            <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[10px] uppercase tracking-[0.18em] text-stone-400">
                                <?php foreach ($miniWeekdayLabels as $label): ?>
                                    <div class="py-1"><?= e($label) ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-2 space-y-1">
                                <?php foreach ($calendarWeeks as $week): ?>
                                    <div class="grid grid-cols-7 gap-1">
                                        <?php foreach ($week as $day): ?>
                                            <?php
                                                $miniSelected = $day['date']->isSameDay($selectedDay);
                                                $miniCurrentMonth = $day['date']->month === $monthDate->month;
                                            ?>
                                            <a
                                                href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                                                class="relative flex h-9 items-center justify-center rounded-lg text-sm <?= $miniSelected ? 'bg-[color:var(--km-accent)] font-semibold text-white' : ($miniCurrentMonth ? 'text-stone-800 hover:bg-stone-100' : 'text-stone-300 hover:bg-stone-50') ?>"
                                            >
                                                {{ $day['date']->format('j') }}
                                                <?php if ($day['events']->isNotEmpty() && ! $miniSelected): ?>
                                                    <span class="absolute bottom-1 h-1.5 w-1.5 rounded-full bg-[color:var(--km-accent)]"></span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs uppercase tracking-[0.18em] text-stone-400">Agenda selezionata</div>
                                    <div class="mt-1 text-xl font-semibold text-stone-950">{{ $selectedDay->translatedFormat('d F Y') }}</div>
                                </div>
                                <div class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">{{ $selectedDayEvents->count() }}</div>
                            </div>

                            <div class="mt-4 space-y-3">
                                <?php if ($selectedDayEvents->isEmpty()): ?>
                                    <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-sm text-stone-500">
                                        Nessun evento per il giorno selezionato.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($selectedDayEvents as $event): ?>
                                        <?php $userStatus = $eventStatuses[$event->id] ?? null; ?>
                                        <button
                                            type="button"
                                            @click="showEvent({{ $event->id }})"
                                            class="block w-full rounded-2xl border border-slate-200 bg-stone-50 p-4 text-left transition hover:border-slate-300 hover:bg-stone-100"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-xs uppercase tracking-[0.18em] text-stone-400">{{ $event->starts_at->format('H:i') }}</div>
                                                    <div class="mt-1 font-semibold text-stone-900">{{ $event->title }}</div>
                                                    <div class="mt-1 text-sm text-stone-500">{{ $event->location ?: 'Online' }}</div>
                                                </div>
                                                <?php if ($userStatus): ?>
                                                    <?php $statusEnum = \App\Enums\EventAttendanceStatus::tryFrom($userStatus); ?>
                                                    <?php if ($statusEnum): ?>
                                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusEnum->badgeClasses() }}">
                                                            {{ $statusEnum->label() }}
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </button>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($canManageEvents): ?>
                            <div class="mt-5 rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs uppercase tracking-[0.18em] text-stone-400">Gestione</div>
                                        <div class="mt-1 text-xl font-semibold text-stone-950">Nuovo evento</div>
                                    </div>
                                    <a href="/admin/events" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Backoffice</a>
                                </div>

                                <form method="POST" action="{{ route('events.store') }}" class="mt-4 space-y-3">
                                    @csrf
                                    <select name="chapter_id" class="km-input w-full" required>
                                        <option value="">Pianeta</option>
                                        @foreach ($managedChapters as $chapter)
                                            <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="title" class="km-input w-full" placeholder="Titolo evento" required>
                                    <select name="type" class="km-input w-full" required>
                                        @foreach ($eventTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="datetime-local" name="starts_at" class="km-input w-full" required>
                                    <input type="text" name="location" class="km-input w-full" placeholder="Luogo o online">
                                    <input type="number" name="capacity" min="1" class="km-input w-full" placeholder="Capienza">
                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm text-stone-700">
                                        <input type="checkbox" name="is_published" value="1" class="rounded border-stone-300 text-[color:var(--km-accent)] focus:ring-[rgba(85,121,79,0.18)]">
                                        Pubblica subito
                                    </label>
                                    <button type="submit" class="km-button-primary w-full">Crea evento</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </aside>

                    <section class="min-w-0 bg-white">
                        @if ($viewMode === 'month')
                            <div class="grid grid-cols-7 border-b border-slate-200 bg-stone-50 text-center text-[11px] uppercase tracking-[0.18em] text-stone-400">
                                <?php foreach ($weekdayLabels as $label): ?>
                                    <div class="border-r border-slate-200 px-3 py-3 last:border-r-0"><?= e($label) ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="divide-y divide-slate-200">
                                <?php foreach ($calendarWeeks as $week): ?>
                                    <div class="grid grid-cols-7">
                                        <?php foreach ($week as $day): ?>
                                            <?php
                                                $isCurrentMonth = $day['date']->month === $monthDate->month;
                                                $isSelected = $day['date']->isSameDay($selectedDay);
                                                $visibleEvents = $day['events']->take(4);
                                            ?>
                                            <div class="min-h-[150px] border-r border-slate-200 p-2 last:border-r-0 <?= $isSelected ? 'bg-[rgba(85,121,79,0.08)]' : 'bg-white' ?>">
                                                <div class="mb-2 flex items-center justify-between gap-2">
                                                    <a
                                                        href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                                                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-sm font-semibold <?= $isSelected ? 'bg-[color:var(--km-accent)] text-white' : ($isCurrentMonth ? 'text-stone-800' : 'text-stone-300') ?>"
                                                    >
                                                        {{ $day['date']->format('j') }}
                                                    </a>
                                                    <?php if ($day['events']->isNotEmpty()): ?>
                                                        <span class="text-[11px] text-stone-400">{{ $day['events']->count() }}</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="space-y-1.5">
                                                    <?php if ($visibleEvents->isEmpty()): ?>
                                                        <?php if ($isCurrentMonth): ?>
                                                            <div class="rounded-lg border border-dashed border-slate-200 px-2 py-5 text-center text-[11px] text-stone-300">Libero</div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php foreach ($visibleEvents as $event): ?>
                                                            <?php
                                                                $status = $eventStatuses[$event->id] ?? null;
                                                                $tone = match ($status) {
                                                                    'interested' => 'bg-sky-500/12 text-sky-800 border-sky-200',
                                                                    'attending', 'registered' => 'bg-[rgba(85,121,79,0.12)] text-[color:var(--km-accent-strong)] border-[rgba(85,121,79,0.22)]',
                                                                    'not_interested' => 'bg-stone-200 text-stone-700 border-stone-300',
                                                                    default => 'bg-[rgba(70,93,112,0.10)] text-[color:var(--km-deep-strong)] border-[rgba(70,93,112,0.18)]',
                                                                };
                                                            ?>
                                                            <button
                                                                type="button"
                                                                @click="showEvent({{ $event->id }})"
                                                                class="block w-full overflow-hidden rounded-lg border px-2 py-1.5 text-left text-[11px] leading-4 <?= $tone ?>"
                                                                title="{{ $event->title }}"
                                                            >
                                                                <div class="truncate font-semibold">{{ $event->starts_at->format('H:i') }} {{ $event->title }}</div>
                                                            </button>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <?php if ($day['events']->count() > 4): ?>
                                                        <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}" class="block rounded-lg bg-stone-100 px-2 py-1.5 text-[11px] font-semibold text-stone-600">
                                                            +{{ $day['events']->count() - 4 }} altri
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        @elseif ($viewMode === 'week')
                            <div class="grid grid-cols-7 border-b border-slate-200 bg-stone-50">
                                <?php foreach ($weekDays as $index => $day): ?>
    <?php $daySelected = $day['date']->isSameDay($selectedDay); ?>
    <a href="{{ route('events.index', ['view' => 'week', 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}" class="border-r border-slate-200 px-3 py-3 text-center last:border-r-0 {{ $daySelected ? 'bg-[rgba(85,121,79,0.08)]' : '' }}">
        <div class="text-[11px] uppercase tracking-[0.18em] text-stone-400">{{ $weekdayLabels[$index] }}</div>
        <div class="mt-1 text-lg font-semibold {{ $daySelected ? 'text-[color:var(--km-accent-strong)]' : 'text-stone-900' }}">{{ $day['date']->format('d') }}</div>
    </a>
<?php endforeach; ?>
                            </div>

                            <div class="grid grid-cols-7">
                                <?php foreach ($weekDays as $day): ?>
                                    <div class="min-h-[680px] border-r border-slate-200 p-3 last:border-r-0 {{ $day['date']->isSameDay($selectedDay) ? 'bg-[rgba(85,121,79,0.06)]' : 'bg-white' }}">
                                        <div class="space-y-2">
                                            <?php if ($day['events']->isEmpty()): ?>
                                                <div class="rounded-lg border border-dashed border-slate-200 px-3 py-5 text-center text-[11px] text-stone-300">Libero</div>
                                            <?php else: ?>
                                                <?php foreach ($day['events'] as $event): ?>
                                                    <?php
                                                        $status = $eventStatuses[$event->id] ?? null;
                                                        $tone = match ($status) {
                                                            'interested' => 'bg-sky-500/12 text-sky-800 border-sky-200',
                                                            'attending', 'registered' => 'bg-[rgba(85,121,79,0.12)] text-[color:var(--km-accent-strong)] border-[rgba(85,121,79,0.22)]',
                                                            'not_interested' => 'bg-stone-200 text-stone-700 border-stone-300',
                                                            default => 'bg-[rgba(70,93,112,0.10)] text-[color:var(--km-deep-strong)] border-[rgba(70,93,112,0.18)]',
                                                        };
                                                    ?>
                                                    <button
                                                        type="button"
                                                        @click="showEvent({{ $event->id }})"
                                                        class="block w-full rounded-xl border px-3 py-3 text-left <?= $tone ?>"
                                                    >
                                                        <div class="text-[11px] uppercase tracking-[0.18em]">{{ $event->starts_at->format('H:i') }}</div>
                                                        <div class="mt-1 text-sm font-semibold">{{ $event->title }}</div>
                                                        <div class="mt-1 text-xs opacity-75">{{ $event->location ?: 'Online' }}</div>
                                                    </button>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        @else
                            <div class="border-b border-slate-200 bg-stone-50 px-6 py-4">
                                <div class="text-xs uppercase tracking-[0.18em] text-stone-400">Vista giorno</div>
                                <div class="mt-1 text-2xl font-semibold text-stone-950">{{ $selectedDay->translatedFormat('l d F Y') }}</div>
                            </div>

                            <div class="divide-y divide-slate-200">
                                <?php foreach (range(7, 21) as $hour): ?>
                                    <?php
                                        $slotEvents = $selectedDayEvents->filter(fn ($event) => (int) $event->starts_at->format('G') === $hour);
                                    ?>
                                    <div class="grid grid-cols-[90px_minmax(0,1fr)]">
                                        <div class="border-r border-slate-200 px-4 py-4 text-sm text-stone-400">{{ sprintf('%02d:00', $hour) }}</div>
                                        <div class="min-h-[72px] p-3">
                                            <?php if ($slotEvents->isEmpty()): ?>
                                                <div class="h-full rounded-xl border border-dashed border-slate-200"></div>
                                            <?php else: ?>
                                                <div class="space-y-2">
                                                    <?php foreach ($slotEvents as $event): ?>
                                                        <?php
                                                            $status = $eventStatuses[$event->id] ?? null;
                                                            $tone = match ($status) {
                                                                'interested' => 'bg-sky-500/12 text-sky-800 border-sky-200',
                                                                'attending', 'registered' => 'bg-[rgba(85,121,79,0.12)] text-[color:var(--km-accent-strong)] border-[rgba(85,121,79,0.22)]',
                                                                'not_interested' => 'bg-stone-200 text-stone-700 border-stone-300',
                                                                default => 'bg-[rgba(70,93,112,0.10)] text-[color:var(--km-deep-strong)] border-[rgba(70,93,112,0.18)]',
                                                            };
                                                        ?>
                                                        <button
                                                            type="button"
                                                            @click="showEvent({{ $event->id }})"
                                                            class="block w-full rounded-xl border px-4 py-3 text-left <?= $tone ?>"
                                                        >
                                                            <div class="text-xs uppercase tracking-[0.18em]">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) - {{ $event->ends_at->format('H:i') }}@endif</div>
                                                            <div class="mt-1 font-semibold">{{ $event->title }}</div>
                                                            <div class="mt-1 text-sm opacity-80">{{ $event->location ?: 'Online' }}</div>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        </div>

        <div
            x-cloak
            x-show="open"
            x-transition.opacity
            @keydown.escape.window="closeEvent()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/30 p-4"
        >
            <div class="absolute inset-0" @click="closeEvent()"></div>

            <div
                x-show="open"
                x-transition
                class="relative z-10 w-full max-w-[520px] rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_30px_80px_rgba(15,23,42,0.18)]"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-400" x-text="event?.date_label"></p>
                        <h3 class="mt-2 text-2xl font-semibold text-stone-950" x-text="event?.title"></h3>
                        <p class="mt-2 text-sm text-stone-500"><span x-text="event?.time_label"></span> · <span x-text="event?.location"></span></p>
                        <p class="mt-1 text-sm text-stone-500" x-text="event?.chapter"></p>
                    </div>
                    <button type="button" @click="closeEvent()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="mt-4 text-sm leading-7 text-stone-600" x-text="event?.description"></p>

                <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach ([
                        \App\Enums\EventAttendanceStatus::Interested,
                        \App\Enums\EventAttendanceStatus::NotInterested,
                        \App\Enums\EventAttendanceStatus::Attending,
                    ] as $status)
                        <form method="POST" :action="event?.register_url">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button type="submit" class="w-full rounded-full border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-stone-50">
                                {{ $status->label() }}
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <form method="POST" :action="event?.unregister_url">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-full border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-stone-50">
                            Annulla risposta
                        </button>
                    </form>

                    <a :href="event?.detail_url" class="km-button-primary text-center">Apri dettaglio completo</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
