<x-app-layout>
    <?php
        $weekdayLabels = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
        $miniWeekdayLabels = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];
    ?>

    <div
        class="km-portal-bg km-portal-page pb-10 pt-6"
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
            <div class="overflow-hidden rounded-[32px] border border-white/10 bg-white/10 shadow-[0_18px_50px_rgba(60,79,94,0.10)]">
                <div class="border-b border-white/10 px-5 py-4">
                    <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="km-brand-mark km-brand-mark-sm">
                                <x-application-logo />
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-[0.24em] text-white/45">Calendario professionale</div>
                                <h1 class="text-2xl font-semibold text-white">Eventi community</h1>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:flex sm:flex-wrap sm:items-center">
                            <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 sm:flex sm:items-center">
                            <a href="{{ route('events.index', ['view' => $viewMode, 'month' => now()->format('Y-m'), 'day' => now()->format('Y-m-d')]) }}" class="km-button-secondary px-4 py-2">Oggi</a>
                            <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->copy()->subMonth()->format('Y-m'), 'day' => $selectedDay->copy()->subMonthNoOverflow()->format('Y-m-d')]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white/80 hover:bg-white/15">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                            </a>
                            <div class="min-w-0 text-center sm:min-w-[220px]">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/45">
                                    {{ $viewMode === 'month' ? 'Vista mese' : ($viewMode === 'week' ? 'Vista settimana' : 'Vista giorno') }}
                                </div>
                                <div class="text-xl font-semibold text-white sm:text-2xl">
                                    @if ($viewMode === 'month')
                                        {{ $monthDate->translatedFormat('F Y') }}
                                    @elseif ($viewMode === 'week')
                                        {{ $weekStart->translatedFormat('d M') }} - {{ $weekEnd->translatedFormat('d M Y') }}
                                    @else
                                        {{ $selectedDay->translatedFormat('d F Y') }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->copy()->addMonth()->format('Y-m'), 'day' => $selectedDay->copy()->addMonthNoOverflow()->format('Y-m-d')]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/10 text-white/80 hover:bg-white/15">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                            </div>

                            <div class="ml-0 grid grid-cols-3 overflow-hidden rounded-full border border-white/15 bg-white/[.045] 2xl:ml-3">
                                @foreach (['month' => 'Mese', 'week' => 'Settimana', 'day' => 'Giorno'] as $mode => $label)
                                    <a
                                        href="{{ route('events.index', ['view' => $mode, 'month' => $monthDate->format('Y-m'), 'day' => $selectedDay->format('Y-m-d')]) }}"
                                        class="px-3 py-2 text-center text-sm font-semibold {{ $viewMode === $mode ? 'bg-[color:var(--km-deep)] text-white' : 'text-white/70 hover:bg-white/[.075] hover:text-white' }}"
                                    >
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <aside class="grid gap-5 border-b border-white/10 bg-white/[.045] p-5 xl:grid-cols-3">
                        <div class="rounded-[24px] border border-white/10 bg-white/10 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold text-white">{{ $monthDate->translatedFormat('F Y') }}</div>
                                <div class="text-xs text-white/45">{{ $selectedDayEvents->count() }} eventi</div>
                            </div>

                            <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[10px] uppercase tracking-[0.18em] text-white/45">
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
                                                class="relative flex h-9 items-center justify-center rounded-lg text-sm <?= $miniSelected ? 'bg-[color:var(--km-accent)] font-semibold text-white' : ($miniCurrentMonth ? 'text-white/90 hover:bg-white/[.075]' : 'text-stone-300 hover:bg-white/[.045]') ?>"
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

                        <div class="mt-5 rounded-[24px] border border-white/10 bg-white/10 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs uppercase tracking-[0.18em] text-white/45">Agenda selezionata</div>
                                    <div class="mt-1 text-xl font-semibold text-white">{{ $selectedDay->translatedFormat('d F Y') }}</div>
                                </div>
                                <div class="rounded-full bg-white/[.075] px-3 py-1 text-xs font-semibold text-white/75">{{ $selectedDayEvents->count() }}</div>
                            </div>

                            <div class="mt-4 space-y-3">
                                <?php if ($selectedDayEvents->isEmpty()): ?>
                                    <div class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-sm text-white/60">
                                        Nessun evento per il giorno selezionato.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($selectedDayEvents as $event): ?>
                                        <?php $userStatus = $eventStatuses[$event->id] ?? null; ?>
                                        <button
                                            type="button"
                                            @click="showEvent({{ $event->id }})"
                                            class="block w-full rounded-2xl border border-white/10 bg-white/[.045] p-4 text-left transition hover:border-white/15 hover:bg-white/[.075]"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-xs uppercase tracking-[0.18em] text-white/45">{{ $event->starts_at->format('H:i') }}</div>
                                                    <div class="mt-1 font-semibold text-white">{{ $event->title }}</div>
                                                    <div class="mt-1 text-sm text-white/60">{{ $event->location ?: 'Online' }}</div>
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
                            <div class="mt-5 rounded-[24px] border border-white/10 bg-white/10 p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs uppercase tracking-[0.18em] text-white/45">Gestione</div>
                                        <div class="mt-1 text-xl font-semibold text-white">Nuovo evento</div>
                                    </div>
                                    <a href="/admin/events" class="rounded-full border border-white/15 px-3 py-2 text-xs font-semibold text-white/80 hover:bg-white/[.075]">Backoffice</a>
                                </div>

                                <form method="POST" action="{{ route('events.store') }}" class="mt-4 space-y-3">
                                    @csrf
                                    <select name="chapter_id" class="km-portal-input w-full" required>
                                        <option value="">Pianeta</option>
                                        @foreach ($managedChapters as $chapter)
                                            <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="title" class="km-portal-input w-full" placeholder="Titolo evento" required>
                                    <select name="type" class="km-portal-input w-full" required>
                                        @foreach ($eventTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="datetime-local" name="starts_at" class="km-portal-input w-full" required>
                                    <input type="text" name="location" class="km-portal-input w-full" placeholder="Luogo o online">
                                    <input type="number" name="capacity" min="1" class="km-portal-input w-full" placeholder="Capienza">
                                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 px-4 py-3 text-sm text-white/80">
                                        <input type="checkbox" name="is_published" value="1" class="rounded border-stone-300 text-[color:var(--km-accent)] focus:ring-[rgba(85,121,79,0.18)]">
                                        Pubblica subito
                                    </label>
                                    <button type="submit" class="km-button-primary w-full">Crea evento</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </aside>

                    <section class="min-w-0 bg-white/10">
                        <div class="space-y-3 p-4 md:hidden">
                            <div class="rounded-[1.4rem] border border-white/10 bg-white/[.045] p-4">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/45">Agenda mobile</div>
                                <div class="mt-1 text-xl font-semibold text-white">{{ $selectedDay->translatedFormat('l d F Y') }}</div>
                                <div class="mt-1 text-sm text-white/60">{{ $selectedDayEvents->count() }} eventi nel giorno selezionato</div>
                            </div>

                            @forelse ($selectedDayEvents as $event)
                                @php
                                    $status = $eventStatuses[$event->id] ?? null;
                                    $tone = match ($status) {
                                        'interested' => 'bg-sky-400/15 text-sky-100 border-sky-300/25',
                                        'attending', 'registered' => 'bg-emerald-400/15 text-emerald-100 border-emerald-300/25',
                                        'not_interested' => 'bg-white/[.075] text-white/65 border-white/10',
                                        default => 'bg-white/[.06] text-white/80 border-white/10',
                                    };
                                @endphp
                                <button
                                    type="button"
                                    @click="showEvent({{ $event->id }})"
                                    class="block w-full rounded-[1.4rem] border px-4 py-4 text-left {{ $tone }}"
                                >
                                    <div class="text-xs uppercase tracking-[0.18em]">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) - {{ $event->ends_at->format('H:i') }}@endif</div>
                                    <div class="mt-1 text-base font-semibold">{{ $event->title }}</div>
                                    <div class="mt-1 text-sm opacity-80">{{ $event->location ?: 'Online' }}</div>
                                </button>
                            @empty
                                <div class="rounded-[1.4rem] border border-dashed border-white/10 px-4 py-8 text-center text-sm text-white/60">
                                    Nessun evento nel giorno selezionato.
                                </div>
                            @endforelse
                        </div>

                        <div class="hidden md:block">
                        @if ($viewMode === 'month')
                            <div class="grid grid-cols-7 border-b border-white/10 bg-white/[.045] text-center text-[11px] uppercase tracking-[0.18em] text-white/45">
                                <?php foreach ($weekdayLabels as $label): ?>
                                    <div class="border-r border-white/10 px-3 py-3 last:border-r-0"><?= e($label) ?></div>
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
                                            <div class="min-h-[190px] border-r border-white/10 p-2 last:border-r-0 <?= $isSelected ? 'bg-[rgba(85,121,79,0.08)]' : 'bg-white/10' ?>">
                                                <div class="mb-2 flex items-center justify-between gap-2">
                                                    <a
                                                        href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                                                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-sm font-semibold <?= $isSelected ? 'bg-[color:var(--km-accent)] text-white' : ($isCurrentMonth ? 'text-white/90' : 'text-stone-300') ?>"
                                                    >
                                                        {{ $day['date']->format('j') }}
                                                    </a>
                                                    <?php if ($day['events']->isNotEmpty()): ?>
                                                        <span class="text-[11px] text-white/45">{{ $day['events']->count() }}</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="space-y-1.5">
                                                    <?php if ($visibleEvents->isEmpty()): ?>
                                                        <?php if ($isCurrentMonth): ?>
                                                            <div class="rounded-lg border border-dashed border-white/10 px-2 py-5 text-center text-[11px] text-stone-300">Libero</div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php foreach ($visibleEvents as $event): ?>
                                                            <?php
                                                                $status = $eventStatuses[$event->id] ?? null;
                                                                $tone = match ($status) {
                                                                    'interested' => 'bg-sky-400/15 text-sky-100 border-sky-300/25',
                                                                    'attending', 'registered' => 'bg-emerald-400/15 text-emerald-100 border-emerald-300/25',
                                                                    'not_interested' => 'bg-white/[.075] text-white/65 border-white/10',
                                                                    default => 'bg-white/[.06] text-white/80 border-white/10',
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
                                                        <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}" class="block rounded-lg bg-white/[.075] px-2 py-1.5 text-[11px] font-semibold text-white/75">
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
                            <div class="grid grid-cols-7 border-b border-white/10 bg-white/[.045]">
                                <?php foreach ($weekDays as $index => $day): ?>
    <?php $daySelected = $day['date']->isSameDay($selectedDay); ?>
    <a href="{{ route('events.index', ['view' => 'week', 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}" class="border-r border-white/10 px-3 py-3 text-center last:border-r-0 {{ $daySelected ? 'bg-[rgba(85,121,79,0.08)]' : '' }}">
        <div class="text-[11px] uppercase tracking-[0.18em] text-white/45">{{ $weekdayLabels[$index] }}</div>
        <div class="mt-1 text-lg font-semibold {{ $daySelected ? 'text-emerald-200' : 'text-white' }}">{{ $day['date']->format('d') }}</div>
    </a>
<?php endforeach; ?>
                            </div>

                            <div class="grid grid-cols-7">
                                <?php foreach ($weekDays as $day): ?>
                                    <div class="min-h-[760px] border-r border-white/10 p-3 last:border-r-0 {{ $day['date']->isSameDay($selectedDay) ? 'bg-[rgba(85,121,79,0.06)]' : 'bg-white/10' }}">
                                        <div class="space-y-2">
                                            <?php if ($day['events']->isEmpty()): ?>
                                                <div class="rounded-lg border border-dashed border-white/10 px-3 py-5 text-center text-[11px] text-stone-300">Libero</div>
                                            <?php else: ?>
                                                <?php foreach ($day['events'] as $event): ?>
                                                    <?php
                                                        $status = $eventStatuses[$event->id] ?? null;
                                                        $tone = match ($status) {
                                                            'interested' => 'bg-sky-400/15 text-sky-100 border-sky-300/25',
                                                            'attending', 'registered' => 'bg-emerald-400/15 text-emerald-100 border-emerald-300/25',
                                                            'not_interested' => 'bg-white/[.075] text-white/65 border-white/10',
                                                            default => 'bg-white/[.06] text-white/80 border-white/10',
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
                            <div class="border-b border-white/10 bg-white/[.045] px-6 py-4">
                                <div class="text-xs uppercase tracking-[0.18em] text-white/45">Vista giorno</div>
                                <div class="mt-1 text-2xl font-semibold text-white">{{ $selectedDay->translatedFormat('l d F Y') }}</div>
                            </div>

                            <div class="divide-y divide-slate-200">
                                <?php foreach (range(7, 21) as $hour): ?>
                                    <?php
                                        $slotEvents = $selectedDayEvents->filter(fn ($event) => (int) $event->starts_at->format('G') === $hour);
                                    ?>
                                    <div class="grid grid-cols-[90px_minmax(0,1fr)]">
                                        <div class="border-r border-white/10 px-4 py-4 text-sm text-white/45">{{ sprintf('%02d:00', $hour) }}</div>
                                        <div class="min-h-[72px] p-3">
                                            <?php if ($slotEvents->isEmpty()): ?>
                                                <div class="h-full rounded-xl border border-dashed border-white/10"></div>
                                            <?php else: ?>
                                                <div class="space-y-2">
                                                    <?php foreach ($slotEvents as $event): ?>
                                                        <?php
                                                            $status = $eventStatuses[$event->id] ?? null;
                                                            $tone = match ($status) {
                                                                'interested' => 'bg-sky-400/15 text-sky-100 border-sky-300/25',
                                                                'attending', 'registered' => 'bg-emerald-400/15 text-emerald-100 border-emerald-300/25',
                                                                'not_interested' => 'bg-white/[.075] text-white/65 border-white/10',
                                                                default => 'bg-white/[.06] text-white/80 border-white/10',
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
                        </div>
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
                class="relative z-10 w-full max-w-[520px] rounded-[28px] border border-white/10 bg-white/10 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.18)]"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45" x-text="event?.date_label"></p>
                        <h3 class="mt-2 text-2xl font-semibold text-white" x-text="event?.title"></h3>
                        <p class="mt-2 text-sm text-white/60"><span x-text="event?.time_label"></span> · <span x-text="event?.location"></span></p>
                        <p class="mt-1 text-sm text-white/60" x-text="event?.chapter"></p>
                    </div>
                    <button type="button" @click="closeEvent()" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-white/70 hover:bg-white/[.075] hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="mt-4 text-sm leading-7 text-white/75" x-text="event?.description"></p>

                <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach ([
                        \App\Enums\EventAttendanceStatus::Interested,
                        \App\Enums\EventAttendanceStatus::NotInterested,
                        \App\Enums\EventAttendanceStatus::Attending,
                    ] as $status)
                        <form method="POST" :action="event?.register_url">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status->value }}">
                            <button type="submit" class="w-full rounded-full border border-white/10 px-4 py-3 text-sm font-semibold text-white/80 hover:bg-white/[.075]">
                                {{ $status->label() }}
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <form method="POST" :action="event?.unregister_url">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-full border border-white/10 px-4 py-3 text-sm font-semibold text-white/80 hover:bg-white/[.075]">
                            Annulla risposta
                        </button>
                    </form>

                    <a :href="event?.detail_url" class="km-button-primary text-center">Apri dettaglio completo</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
