<x-app-layout>
@php
    $weekdayLabels     = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
    $miniWeekdayLabels = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];

    $calendarEventsList = collect($calendarWeeks ?? [])
        ->flatMap(fn ($week) => collect($week)->flatMap(fn ($day) => $day['events'] ?? collect()))
        ->filter()
        ->unique('id')
        ->sortBy('starts_at')
        ->values();

    $quickEventsSafe = $quickEvents ?? collect();
    $allUsersSafe = $allUsers ?? collect();
@endphp

<style>
    :root {
        --km-events-bg: #021018;
        --km-events-panel: rgba(4, 28, 40, .74);
        --km-events-panel-strong: rgba(5, 33, 47, .9);
        --km-events-line: rgba(185, 229, 215, .14);
        --km-events-line-soft: rgba(185, 229, 215, .09);
        --km-events-green: #8bc53f;
        --km-events-green-2: #5f9f37;
        --km-events-cyan: #49d1c4;
        --km-events-text: #f8fafc;
        --km-events-muted: rgba(226, 240, 243, .62);
    }

    .km-events-shell {
        min-height: calc(100vh - 5rem);
        background:
            radial-gradient(circle at 32% 28%, rgba(73, 209, 196, .16), transparent 32%),
            radial-gradient(circle at 82% 8%, rgba(139, 197, 63, .14), transparent 28%),
            linear-gradient(135deg, #03111a 0%, #032733 48%, #020c13 100%);
        color: var(--km-events-text);
    }

    .km-events-topbar,
    .km-events-card {
        border: 1px solid rgba(123, 180, 168, .18);
        background:
            radial-gradient(circle at 55% 18%, rgba(73, 209, 196, .08), transparent 38%),
            rgba(2, 27, 38, .74);
        box-shadow: 0 20px 70px rgba(0, 0, 0, .16);
        backdrop-filter: blur(16px);
    }

    .km-events-icon-btn {
        display: inline-flex;
        height: 44px;
        width: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid var(--km-events-line);
        background: rgba(255, 255, 255, .035);
        color: rgba(255, 255, 255, .78);
        transition: .16s ease;
    }

    .km-events-icon-btn:hover {
        border-color: rgba(139, 197, 63, .35);
        background: rgba(139, 197, 63, .13);
        color: #fff;
    }

    .km-events-primary {
        border-color: rgba(139, 197, 63, .35);
        background: linear-gradient(135deg, var(--km-events-green), var(--km-events-green-2));
        color: #06120a;
        font-weight: 800;
    }

    .km-events-tab {
        min-width: 92px;
        border-radius: 999px;
        padding: 12px 20px;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: rgba(255, 255, 255, .62);
        transition: .16s ease;
    }

    .km-events-tab:hover {
        color: #fff;
        background: rgba(255, 255, 255, .055);
    }

    .km-events-tab-active {
        background: rgba(139, 197, 63, .22);
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(139, 197, 63, .22);
    }

    .km-events-input {
        min-height: 54px;
        border-radius: 8px;
        border: 1px solid var(--km-events-line);
        background: rgba(2, 15, 24, .42);
        color: #fff;
    }

    .km-events-input::placeholder {
        color: rgba(255, 255, 255, .55);
    }

    .km-events-filter-dot {
        height: 10px;
        width: 10px;
        border-radius: 999px;
    }

    .km-events-filter-row {
        border: 1px solid rgba(130, 190, 177, .15);
        background: rgba(5, 41, 53, .52);
        color: rgba(226, 240, 243, .68);
    }

    .km-events-filter-row:hover {
        border-color: rgba(139, 197, 63, .22);
        background: rgba(12, 66, 58, .48);
        color: #fff;
    }

    .km-events-filter-row-active {
        border-color: rgba(139, 197, 63, .32);
        background: linear-gradient(90deg, rgba(139, 197, 63, .18), rgba(45, 120, 99, .12));
        color: #fff;
    }

    .km-events-count-pill {
        background: rgba(172, 217, 203, .13);
        color: rgba(241, 248, 250, .78);
    }

    .km-events-day {
        min-height: 128px;
        border-right: 1px solid var(--km-events-line-soft);
        border-bottom: 1px solid var(--km-events-line-soft);
        background: rgba(255, 255, 255, .012);
    }

    .km-events-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    @media (min-width: 1280px) {
        .km-events-layout {
            grid-template-columns: 280px minmax(680px, 1fr) 360px;
        }

        .km-events-day {
            min-height: 142px;
        }
    }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .km-events-layout {
            grid-template-columns: 250px minmax(560px, 1fr) 330px;
        }
    }

    @media (max-width: 1023px) {
        .km-events-shell {
            min-height: calc(100vh - 4rem);
        }

        .km-events-tab {
            min-width: auto;
            padding-inline: 14px;
        }
    }
</style>

<div
    class="km-events-shell flex flex-col"
    @keydown.escape.window="closeDetail(); createOpen = false;"
    @resize.window="isLargeScreen = window.innerWidth >= 1024; if (isLargeScreen) sidebarOpen = true;"
    x-init="$nextTick(() => { showDefaultEvent(); })"
    x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        isLargeScreen: window.innerWidth >= 1024,
        createOpen: false,

        events: {{ \Illuminate\Support\Js::from($quickEventsSafe) }},
        defaultEventId: {{ \Illuminate\Support\Js::from($defaultEventId ?? null) }},
        allUsers: {{ \Illuminate\Support\Js::from($allUsersSafe->values()) }},

        detailOpen: false,
        selectedEventId: null,

        get selectedEvent() {
            return this.selectedEventId !== null ? (this.events[this.selectedEventId] ?? null) : null;
        },

        activeFilter: 'all',
        searchQuery: '',

        isEventVisible(id) {
            const ev = this.events[id];
            if (!ev) return false;

            const title = String(ev.title || '').toLowerCase();
            const query = String(this.searchQuery || '').toLowerCase();

            if (query && !title.includes(query)) return false;

            switch (this.activeFilter) {
                case 'confirmed': return ev.user_status === 'attending' || ev.user_status === 'registered';
                case 'pending': return ev.is_invited || ev.user_status === 'interested';
                case 'past': return ev.is_past && !ev.is_cancelled;
                case 'cancelled': return ev.is_cancelled;
                case 'mine_org': return ev.is_mine;
                case 'mine_part': return (ev.user_status === 'attending' || ev.user_status === 'registered') && !ev.is_mine;
                default: return true;
            }
        },

        get filterCounts() {
            const evs = Object.values(this.events || {});
            return {
                all: evs.length,
                confirmed: evs.filter(e => e.user_status === 'attending' || e.user_status === 'registered').length,
                pending: evs.filter(e => e.is_invited || e.user_status === 'interested').length,
                past: evs.filter(e => e.is_past && !e.is_cancelled).length,
                cancelled: evs.filter(e => e.is_cancelled).length,
                mine_org: evs.filter(e => e.is_mine).length,
                mine_part: evs.filter(e => (e.user_status === 'attending' || e.user_status === 'registered') && !e.is_mine).length,
            };
        },

        showEvent(id) {
            const ev = this.events[id];
            if (!ev) return;
            this.selectedEventId = id;
            this.detailOpen = true;
        },

        showDefaultEvent() {
            if (this.defaultEventId !== null && this.events[this.defaultEventId]) {
                this.selectedEventId = this.defaultEventId;
                this.detailOpen = true;
                return;
            }

            const ids = Object.keys(this.events || {});
            if (ids.length) {
                this.selectedEventId = ids[0];
                this.detailOpen = true;
            }
        },

        closeDetail() {
            this.showDefaultEvent();
        },

        statusText(ev) {
            if (!ev) return '';
            if (ev.is_cancelled) return 'Annullato';
            if (ev.is_past) return 'Concluso';
            if (ev.is_invited) return 'Invitata';

            const s = ev.user_status;
            if (s === 'attending' || s === 'registered') return 'Parteciperò';
            if (s === 'interested') return 'Mi interessa';
            if (s === 'not_interested') return 'Non interessato';

            return 'In programma';
        },

        statusClass(ev) {
            if (!ev) return '';
            if (ev.is_cancelled) return 'bg-red-500/20 text-red-300 border-red-400/30';
            if (ev.is_past) return 'bg-white/[.06] text-white/45 border-white/10';
            if (ev.is_invited) return 'bg-amber-400/15 text-amber-200 border-amber-300/25';

            const s = ev.user_status;
            if (s === 'attending' || s === 'registered') return 'bg-emerald-500/20 text-emerald-200 border-emerald-400/25';
            if (s === 'interested') return 'bg-sky-500/20 text-sky-200 border-sky-400/25';

            return 'bg-[color:var(--km-events-green)]/15 text-[color:var(--km-events-green)] border-[color:var(--km-events-green)]/25';
        },

        inviteTarget: 'none',
        userSearch: '',
        selectedUserIds: [],

        get filteredUsers() {
            const users = this.allUsers || [];
            if (!this.userSearch) return users.slice(0, 30);

            const q = String(this.userSearch || '').toLowerCase();

            return users.filter(u => String(u.label || '').toLowerCase().includes(q)).slice(0, 30);
        },

        toggleUser(id) {
            const idx = this.selectedUserIds.indexOf(id);
            if (idx === -1) this.selectedUserIds.push(id);
            else this.selectedUserIds.splice(idx, 1);
        },

        initials(name) {
            return String(name || '?')
                .split(' ')
                .filter(Boolean)
                .map(w => w[0])
                .join('')
                .toUpperCase()
                .slice(0, 2) || '?';
        },
    }"
>

<div class="flex flex-none flex-wrap items-center gap-3 px-4 py-5 lg:flex-nowrap lg:px-8">
    <button
        @click="if (isLargeScreen) sidebarOpen = true; else sidebarOpen = !sidebarOpen"
        class="km-events-icon-btn flex-none"
        title="Mostra/nascondi pannello"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    @if ($canManageEvents)
        <button
            @click="createOpen = true"
            class="km-events-icon-btn km-events-primary flex-none"
            title="Nuovo evento"
        >
            <svg class="h-5 w-5 flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 5v14M5 12h14"/>
            </svg>
        </button>
    @endif

    <div class="flex items-center gap-3">
        <a href="{{ route('events.index', ['view' => $viewMode, 'month' => now()->format('Y-m'), 'day' => now()->format('Y-m-d')]) }}"
           class="hidden rounded-full border border-white/15 px-5 py-3 text-sm font-bold text-white/78 transition hover:bg-white/[.07] hover:text-white sm:block">
            Oggi
        </a>

        <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->copy()->subMonth()->format('Y-m'), 'day' => $selectedDay->copy()->subMonthNoOverflow()->format('Y-m-d')]) }}"
           class="km-events-icon-btn h-11 w-11">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
        </a>

        <div class="min-w-[140px] text-center sm:min-w-[190px]">
            <div class="text-xl font-bold text-white">
                @if ($viewMode === 'month')
                    {{ $monthDate->translatedFormat('F Y') }}
                @elseif ($viewMode === 'week')
                    {{ $weekStart->translatedFormat('d M') }} - {{ $weekEnd->translatedFormat('d M Y') }}
                @elseif ($viewMode === 'day')
                    {{ $selectedDay->translatedFormat('d F Y') }}
                @else
                    {{ $monthDate->translatedFormat('F Y') }}
                @endif
            </div>
        </div>

        <a href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->copy()->addMonth()->format('Y-m'), 'day' => $selectedDay->copy()->addMonthNoOverflow()->format('Y-m-d')]) }}"
           class="km-events-icon-btn h-11 w-11">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>
    </div>

    <div class="km-events-topbar flex overflow-hidden rounded-full p-1">
        @foreach (['month' => 'Mese', 'week' => 'Settimana', 'day' => 'Giorno', 'list' => 'Lista'] as $mode => $label)
            <a
                href="{{ route('events.index', ['view' => $mode, 'month' => $monthDate->format('Y-m'), 'day' => $selectedDay->format('Y-m-d')]) }}"
                class="km-events-tab {{ $viewMode === $mode ? 'km-events-tab-active' : '' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="relative ml-auto w-full sm:w-[260px] xl:w-[320px]">
        <svg class="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-white/56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
        </svg>
        <input
            type="text"
            x-model.debounce.200ms="searchQuery"
            placeholder="Cerca evento..."
            class="km-events-input w-full py-3 pl-5 pr-12 text-sm outline-none focus:border-[color:var(--km-events-green)]/50 focus:ring-0"
        >
    </div>

    <button
        @click="if (isLargeScreen) sidebarOpen = true; else sidebarOpen = !sidebarOpen"
        class="km-events-input flex h-[54px] items-center gap-3 px-5 text-sm font-bold text-white"
        title="Filtri"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M7 12h10M10 18h4"/>
        </svg>
        Filtri
    </button>
</div>

<div class="km-events-layout grid flex-1 gap-4 overflow-hidden px-4 pb-6 lg:px-8" style="min-height: calc(100vh - 10rem);">
    <aside
        x-show="sidebarOpen || isLargeScreen"
        x-transition:enter="transition-[width,opacity] duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-[width,opacity] duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="km-events-card w-full overflow-y-auto rounded-lg p-5"
    >
        <div class="mb-5">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-base font-bold text-white">{{ $monthDate->translatedFormat('F Y') }}</span>
                <span class="text-[11px] text-white/35" x-text="filterCounts.all + ' ev.'"></span>
            </div>

            <div class="mb-1.5 grid grid-cols-7 text-center text-[10px] uppercase tracking-[0.14em] text-white/40">
                @foreach ($miniWeekdayLabels as $ml)
                    <div>{{ $ml }}</div>
                @endforeach
            </div>

            @foreach ($calendarWeeks as $week)
                <div class="mb-0.5 grid grid-cols-7">
                    @foreach ($week as $day)
                        @php
                            $dayEvents = $day['events'] ?? collect();
                            $miniSel   = $day['date']->isSameDay($selectedDay);
                            $miniCurr  = $day['date']->month === $monthDate->month;
                            $miniToday = $day['date']->isToday();
                        @endphp

                        <a
                            href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                            class="relative flex h-8 items-center justify-center rounded-full text-sm font-semibold
                                {{ $miniSel
                                    ? 'bg-[color:var(--km-events-green)] text-white'
                                    : ($miniToday
                                        ? 'border border-[color:var(--km-events-green)]/60 text-white/90 hover:bg-white/[.07]'
                                        : ($miniCurr ? 'text-white/75 hover:bg-white/[.07]' : 'text-white/25')) }}"
                        >
                            {{ $day['date']->format('j') }}

                            @if ($dayEvents->isNotEmpty() && ! $miniSel)
                                <span class="absolute bottom-0.5 h-1 w-1 rounded-full bg-[color:var(--km-events-green)] opacity-70"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="mb-5">
            <p class="mb-3 text-[11px] uppercase tracking-[0.18em] text-white/40">Tipo evento</p>

            <div class="space-y-2">
                @foreach ([
                    'all'       => ['label' => 'Tutti gli eventi',  'count_key' => 'all',       'dot' => 'bg-white/35'],
                    'pending'   => ['label' => 'In attesa',         'count_key' => 'pending',   'dot' => 'bg-amber-400'],
                    'confirmed' => ['label' => 'Confermati',        'count_key' => 'confirmed', 'dot' => 'bg-[color:var(--km-events-green)]'],
                    'past'      => ['label' => 'Completati',        'count_key' => 'past',      'dot' => 'bg-sky-400'],
                    'cancelled' => ['label' => 'Cancellati',        'count_key' => 'cancelled', 'dot' => 'bg-red-400'],
                ] as $filterKey => $filterDef)
                    <button
                        @click="activeFilter = '{{ $filterKey }}'"
                        class="flex w-full items-center justify-between rounded-md px-3 py-2.5 text-left text-sm transition"
                        :class="activeFilter === '{{ $filterKey }}'
                            ? 'km-events-filter-row-active font-semibold'
                            : 'km-events-filter-row'"
                    >
                        <span class="flex items-center gap-3">
                            <span class="km-events-filter-dot {{ $filterDef['dot'] }}"></span>
                            {{ $filterDef['label'] }}
                        </span>

                        <span
                            class="km-events-count-pill ml-2 rounded-full px-2 py-0.5 text-xs font-bold"
                            x-text="filterCounts['{{ $filterDef['count_key'] }}']"
                        ></span>
                    </button>
                @endforeach
            </div>
        </div>

        <div>
            <p class="mb-3 text-[11px] uppercase tracking-[0.18em] text-white/40">I miei eventi</p>

            <div class="space-y-2">
                @foreach ([
                    'mine_org'  => ['label' => 'Organizzati',   'count_key' => 'mine_org'],
                    'mine_part' => ['label' => 'Partecipazioni', 'count_key' => 'mine_part'],
                ] as $filterKey => $filterDef)
                    <button
                        @click="activeFilter = '{{ $filterKey }}'"
                        class="flex w-full items-center justify-between rounded-md px-3 py-2.5 text-left text-sm transition"
                        :class="activeFilter === '{{ $filterKey }}'
                            ? 'km-events-filter-row-active font-semibold'
                            : 'km-events-filter-row'"
                    >
                        <span>{{ $filterDef['label'] }}</span>

                        <span
                            class="km-events-count-pill ml-2 rounded-full px-2 py-0.5 text-xs font-bold"
                            x-text="filterCounts['{{ $filterDef['count_key'] }}']"
                        ></span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-5 border-t border-white/[.07] pt-4">
            <p class="mb-2 text-[10px] uppercase tracking-[0.18em] text-white/35">{{ $selectedDay->translatedFormat('d F') }}</p>

            @forelse ($selectedDayEvents as $ev)
                @php
                    $st = $eventStatuses[$ev->id] ?? null;
                @endphp

                <button
                    @click="showEvent({{ $ev->id }})"
                    class="mb-2 block w-full rounded-xl border border-white/10 bg-white/[.04] px-3 py-2.5 text-left transition hover:border-white/15 hover:bg-white/[.07]"
                >
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 flex-none rounded-full {{ $st === 'attending' || $st === 'registered' ? 'bg-emerald-400' : ($st === 'interested' ? 'bg-sky-400' : 'bg-[color:var(--km-events-green)]/60') }}"></span>
                        <span class="text-[10px] uppercase tracking-[0.14em] text-white/45">
                            {{ $ev->starts_at?->format('H:i') ?? '--:--' }}
                        </span>
                    </div>

                    <div class="mt-1 truncate text-xs font-semibold text-white">
                        {{ $ev->title ?? 'Evento senza titolo' }}
                    </div>

                    <div class="mt-0.5 truncate text-[11px] text-white/50">
                        {{ $ev->location ?: 'Online' }}
                    </div>
                </button>
            @empty
                <div class="rounded-xl border border-dashed border-white/10 px-3 py-5 text-center text-[11px] text-white/35">
                    Nessun evento oggi
                </div>
            @endforelse
        </div>
    </aside>

    <main class="km-events-card min-w-0 overflow-auto rounded-lg">
        @if ($viewMode === 'month')
            <div class="sticky top-0 z-10 grid grid-cols-7 border-b border-white/10 bg-[rgba(4,28,40,0.96)]">
                @foreach ($weekdayLabels as $label)
                    <div class="border-r border-white/[.06] px-2 py-5 text-center text-[12px] uppercase tracking-[0.14em] text-white/48 last:border-r-0">
                        {{ mb_strtoupper(mb_substr($label, 0, 3)) }}
                    </div>
                @endforeach
            </div>

            @foreach ($calendarWeeks as $week)
                <div class="grid grid-cols-7">
                    @foreach ($week as $day)
                        @php
                            $dayEvents = $day['events'] ?? collect();
                            $isCurrentMonth = $day['date']->month === $monthDate->month;
                            $isSelected     = $day['date']->isSameDay($selectedDay);
                            $isToday        = $day['date']->isToday();
                        @endphp

                        <div class="km-events-day p-3 last:border-r-0 {{ $isSelected ? 'bg-[rgba(85,121,79,0.08)]' : ($isCurrentMonth ? 'bg-white/[.02]' : '') }}">
                            <div class="mb-1 flex items-center justify-between px-0.5">
                                <a
                                    href="{{ route('events.index', ['view' => $viewMode, 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full text-base font-semibold transition
                                        {{ $isToday ? 'bg-[color:var(--km-events-green)] text-white' : ($isSelected ? 'bg-white/15 text-white' : ($isCurrentMonth ? 'text-white/82 hover:bg-white/[.07]' : 'text-white/28')) }}"
                                >
                                    {{ $day['date']->format('j') }}
                                </a>

                                @if ($dayEvents->count() > 0)
                                    <span class="text-[10px] text-white/30">{{ $dayEvents->count() }}</span>
                                @endif
                            </div>

                            <div class="mt-3 space-y-2">
                                @foreach ($dayEvents->take(3) as $ev)
                                    @php
                                        $s = $eventStatuses[$ev->id] ?? null;
                                        $isCancelled = ($ev->status ?? null) === 'cancelled';
                                        $isPast = $ev->starts_at?->isPast() ?? false;

                                        $pill = $isCancelled
                                            ? 'bg-red-500/15 text-red-200 border-red-500/30'
                                            : ($isPast
                                                ? 'bg-white/[.06] text-white/55 border-white/[.10]'
                                                : match($s) {
                                                    'attending','registered' => 'bg-[color:var(--km-events-green)]/18 text-white border-[color:var(--km-events-green)]/75',
                                                    'interested'             => 'bg-amber-400/16 text-amber-100 border-amber-300/35',
                                                    'not_interested'         => 'bg-white/[.05] text-white/40 border-white/[.08]',
                                                    default                  => 'bg-[color:var(--km-events-green)]/12 text-white/86 border-[color:var(--km-events-green)]/45',
                                                });
                                    @endphp

                                    <button
                                        x-show="isEventVisible({{ $ev->id }})"
                                        @click="showEvent({{ $ev->id }})"
                                        class="block w-full overflow-hidden rounded-md border px-3 py-2 text-left text-xs leading-4 transition hover:translate-y-[-1px] hover:opacity-95 {{ $pill }}"
                                        title="{{ $ev->title ?? 'Evento senza titolo' }}"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="font-bold">{{ $ev->starts_at?->format('H:i') ?? '--:--' }}</div>
                                                <div class="line-clamp-2 font-semibold">{{ $ev->title ?? 'Evento senza titolo' }}</div>
                                            </div>

                                            @if (($ev->attending_count ?? 0) > 0)
                                                <span class="flex-none text-[11px] opacity-70">{{ $ev->attending_count }}</span>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach

                                @if ($dayEvents->count() > 3)
                                    <a href="{{ route('events.index', ['view' => 'day', 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                                       class="block rounded-md bg-white/[.065] px-1.5 py-1 text-[11px] font-semibold text-white/55 transition hover:text-white">
                                        +{{ $dayEvents->count() - 3 }} altri
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

        @elseif ($viewMode === 'week')
            <div class="sticky top-0 z-10 grid grid-cols-[56px_repeat(7,minmax(0,1fr))] border-b border-white/10 bg-[rgba(22,30,40,0.97)]">
                <div class="border-r border-white/[.06]"></div>

                @foreach ($weekDays as $idx => $day)
                    @php
                        $weekDayEvents = $day['events'] ?? collect();
                        $dSel = $day['date']->isSameDay($selectedDay);
                        $dToday = $day['date']->isToday();
                    @endphp

                    <a
                        href="{{ route('events.index', ['view' => 'week', 'month' => $monthDate->format('Y-m'), 'day' => $day['date']->format('Y-m-d')]) }}"
                        class="border-r border-white/[.06] px-2 py-3 text-center last:border-r-0 transition {{ $dSel ? 'bg-[rgba(85,121,79,0.08)]' : 'hover:bg-white/[.03]' }}"
                    >
                        <div class="text-[10px] uppercase tracking-[0.14em] text-white/40">
                            {{ $weekdayLabels[$idx] ?? '' }}
                        </div>

                        <div class="mx-auto mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full text-base font-semibold {{ $dToday ? 'bg-[color:var(--km-events-green)] text-white' : ($dSel ? 'bg-white/15 text-white' : 'text-white/75') }}">
                            {{ $day['date']->format('d') }}
                        </div>

                        @if ($weekDayEvents->count() > 0)
                            <div class="mt-0.5 text-[10px] text-white/35">{{ $weekDayEvents->count() }} ev.</div>
                        @endif
                    </a>
                @endforeach
            </div>

            @foreach (range(0, 23) as $hour)
                @php
                    $slotByDay = [];
                    $hasAnySlot = false;

                    foreach ($weekDays as $wIdx => $wDay) {
                        $slotByDay[$wIdx] = collect($wDay['events'] ?? [])
                            ->filter(fn ($e) => (int) optional($e->starts_at)->format('G') === $hour)
                            ->values();

                        if ($slotByDay[$wIdx]->isNotEmpty()) {
                            $hasAnySlot = true;
                        }
                    }

                    $showRow = ($hour >= 7 && $hour <= 22) || $hasAnySlot;
                @endphp

                @if ($showRow)
                    <div class="grid grid-cols-[56px_repeat(7,minmax(0,1fr))] border-b border-white/[.05] {{ $hour < 8 ? 'opacity-50' : '' }}">
                        <div class="border-r border-white/[.05] px-2 py-2 text-right text-[11px] text-white/30">{{ sprintf('%02d', $hour) }}</div>

                        @foreach ($weekDays as $wIdx => $wDay)
                            <div class="min-h-[52px] border-r border-white/[.05] p-1 last:border-r-0 {{ $wDay['date']->isSameDay($selectedDay) ? 'bg-[rgba(85,121,79,0.04)]' : '' }}">
                                @foreach ($slotByDay[$wIdx] as $ev)
                                    @php
                                        $s = $eventStatuses[$ev->id] ?? null;

                                        $pill = ($ev->status ?? null) === 'cancelled'
                                            ? 'bg-red-500/15 text-red-300 border-red-500/25'
                                            : (($ev->starts_at?->isPast() ?? false)
                                                ? 'bg-white/[.06] text-white/55 border-white/[.10]'
                                                : match($s) {
                                                    'attending','registered' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/20',
                                                    'interested'             => 'bg-sky-500/20 text-sky-200 border-sky-400/20',
                                                    default                  => 'bg-[color:var(--km-events-green)]/15 text-white/80 border-[color:var(--km-events-green)]/20',
                                                });
                                    @endphp

                                    <button
                                        x-show="isEventVisible({{ $ev->id }})"
                                        @click="showEvent({{ $ev->id }})"
                                        class="mb-1 block w-full rounded-lg border px-1.5 py-1 text-left text-[11px] transition hover:opacity-80 {{ $pill }}"
                                    >
                                        <div class="text-[10px] opacity-60">{{ $ev->starts_at?->format('H:i') ?? '--:--' }}</div>
                                        <div class="truncate font-semibold">{{ $ev->title ?? 'Evento senza titolo' }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach

        @elseif ($viewMode === 'day')
            <div class="sticky top-0 z-10 border-b border-white/10 bg-[rgba(22,30,40,0.97)] px-6 py-4">
                <div class="text-[11px] uppercase tracking-[0.2em] text-white/40">Vista giorno</div>
                <div class="mt-1 text-xl font-semibold text-white">{{ $selectedDay->translatedFormat('l d F Y') }}</div>
                <div class="mt-0.5 text-sm text-white/50">{{ $selectedDayEvents->count() }} eventi</div>
            </div>

            @foreach (range(0, 23) as $hour)
                @php
                    $slotEvs = $selectedDayEvents->filter(fn ($e) => (int) optional($e->starts_at)->format('G') === $hour);
                    $showRow = ($hour >= 7 && $hour <= 22) || $slotEvs->isNotEmpty();
                @endphp

                @if ($showRow)
                    <div class="grid grid-cols-[80px_minmax(0,1fr)] border-b border-white/[.05] {{ $hour < 8 ? 'opacity-50' : '' }}">
                        <div class="border-r border-white/[.05] px-4 py-4 text-sm text-white/35">{{ sprintf('%02d:00', $hour) }}</div>

                        <div class="min-h-[68px] p-2">
                            @if ($slotEvs->isEmpty())
                                <div class="h-full rounded-xl border border-dashed border-white/[.07]"></div>
                            @else
                                <div class="space-y-2">
                                    @foreach ($slotEvs as $ev)
                                        @php
                                            $s = $eventStatuses[$ev->id] ?? null;

                                            $pill = ($ev->status ?? null) === 'cancelled'
                                                ? 'bg-red-500/15 text-red-300 border-red-500/25'
                                                : (($ev->starts_at?->isPast() ?? false)
                                                    ? 'bg-white/[.06] text-white/55 border-white/[.10]'
                                                    : match($s) {
                                                        'attending','registered' => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/20',
                                                        'interested'             => 'bg-sky-500/20 text-sky-200 border-sky-400/20',
                                                        'not_interested'         => 'bg-white/[.05] text-white/50 border-white/[.08]',
                                                        default                  => 'bg-[color:var(--km-events-green)]/15 text-white/85 border-[color:var(--km-events-green)]/20',
                                                    });
                                        @endphp

                                        <button
                                            x-show="isEventVisible({{ $ev->id }})"
                                            @click="showEvent({{ $ev->id }})"
                                            class="block w-full rounded-xl border px-4 py-3 text-left transition hover:opacity-80 {{ $pill }}"
                                        >
                                            <div class="text-[11px] uppercase tracking-[0.14em] opacity-65">
                                                {{ $ev->starts_at?->format('H:i') ?? '--:--' }}
                                                @if ($ev->ends_at)
                                                    - {{ $ev->ends_at->format('H:i') }}
                                                @endif
                                            </div>

                                            <div class="mt-1 font-semibold">{{ $ev->title ?? 'Evento senza titolo' }}</div>
                                            <div class="mt-0.5 text-sm opacity-70">{{ $ev->location ?: 'Online' }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

        @else
            <div class="sticky top-0 z-10 border-b border-white/10 bg-[rgba(4,28,40,0.96)] px-6 py-4">
                <div class="text-[11px] uppercase tracking-[0.2em] text-white/40">Lista eventi</div>
                <div class="mt-1 text-xl font-semibold text-white">{{ $monthDate->translatedFormat('F Y') }}</div>
            </div>

            <div class="space-y-3 p-4">
                @forelse ($calendarEventsList as $ev)
                    @php
                        $s = $eventStatuses[$ev->id] ?? null;

                        $listPill = ($ev->status ?? null) === 'cancelled'
                            ? 'border-red-400/20 bg-red-500/10'
                            : ($s === 'attending' || $s === 'registered'
                                ? 'border-[color:var(--km-events-green)]/30 bg-[color:var(--km-events-green)]/12'
                                : 'border-white/10 bg-white/[.035]');
                    @endphp

                    <button
                        x-show="isEventVisible({{ $ev->id }})"
                        @click="showEvent({{ $ev->id }})"
                        class="flex w-full items-center gap-4 rounded-md border p-4 text-left transition hover:border-[color:var(--km-events-green)]/35 {{ $listPill }}"
                    >
                        <div class="flex h-14 w-14 flex-none flex-col items-center justify-center rounded-md bg-white/[.06]">
                            <span class="text-[10px] uppercase text-white/45">{{ $ev->starts_at?->translatedFormat('M') ?? '--' }}</span>
                            <span class="text-xl font-bold text-white">{{ $ev->starts_at?->format('d') ?? '--' }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-base font-bold text-white">{{ $ev->title ?? 'Evento senza titolo' }}</div>
                            <div class="mt-1 text-sm text-white/55">
                                {{ $ev->starts_at?->format('H:i') ?? '--:--' }}
                                @if ($ev->ends_at)
                                    - {{ $ev->ends_at->format('H:i') }}
                                @endif
                                &middot; {{ $ev->location ?: 'Online' }}
                            </div>
                        </div>

                        <span class="hidden rounded-full border border-white/10 px-3 py-1 text-xs font-bold text-white/55 sm:inline">
                            {{ $ev->attending_count ?? 0 }} partecipanti
                        </span>
                    </button>
                @empty
                    <div class="rounded-md border border-dashed border-white/10 p-10 text-center text-white/45">
                        Nessun evento nel periodo selezionato
                    </div>
                @endforelse
            </div>
        @endif
    </main>

    <aside
        class="km-events-card overflow-hidden rounded-lg"
        style="transition: width 0.2s ease-out;"
        :style="{ width: (detailOpen || isLargeScreen) ? '100%' : '0px' }"
    >
        <div class="flex h-full w-full flex-col overflow-y-auto">
            <div
                x-show="!detailOpen"
                class="flex flex-1 flex-col items-center justify-center gap-4 p-8 text-center"
            >
                <div class="flex h-16 w-16 items-center justify-center rounded-md border border-white/[.07] bg-[color:var(--km-events-green)]/15 text-[color:var(--km-events-green)]">
                    <svg class="h-7 w-7 text-white/25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-bold text-white/55">Seleziona un evento</p>
                    <p class="mt-1 text-xs text-white/35">Apri una scheda dal calendario per vedere dettagli e partecipanti</p>
                </div>
            </div>

            <div x-show="detailOpen" class="flex flex-col" style="min-height: 100%;">
                <div class="flex flex-none items-center justify-between border-b border-white/[.07] px-5 py-4 lg:hidden">
                    <div class="text-[10px] uppercase tracking-[0.2em] text-white/40">Dettaglio evento</div>

                    <button
                        @click="closeDetail()"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 text-white/55 transition hover:bg-white/[.07] hover:text-white"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="selectedEvent && selectedEvent.cover_image" class="h-44 w-full flex-none overflow-hidden">
                    <img :src="selectedEvent?.cover_image" :alt="selectedEvent?.title || 'Evento'" class="h-full w-full object-cover">
                </div>

                <div class="flex flex-1 flex-col gap-5 p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                            :class="statusClass(selectedEvent)"
                            x-text="statusText(selectedEvent)"
                        ></span>

                        <span
                            class="ml-auto inline-flex items-center rounded-full border border-white/10 bg-white/[.06] px-3 py-1.5 text-[11px] font-bold text-white/55"
                            x-text="selectedEvent?.type_label || 'Evento'"
                        ></span>

                        <span x-show="selectedEvent?.is_cancelled" class="inline-flex items-center gap-1 rounded-full border border-red-400/20 bg-red-500/10 px-2.5 py-1 text-[11px] font-semibold text-red-300">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                            Annullato
                        </span>
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-semibold text-white/58" x-text="(selectedEvent?.date_label || '') + ' - ' + (selectedEvent?.time_label || '')"></p>
                        <h2 class="text-2xl font-bold leading-tight text-white" x-text="selectedEvent?.title || 'Evento senza titolo'"></h2>
                        <p class="mt-2 text-sm text-white/58" x-text="selectedEvent?.location || 'Online'"></p>
                    </div>

                    <div class="flex items-start gap-3 rounded-2xl border border-white/[.07] bg-white/[.03] px-4 py-3">
                        <svg class="mt-0.5 h-4 w-4 flex-none text-[color:var(--km-events-green)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>

                        <div>
                            <div class="text-sm font-semibold text-white" x-text="selectedEvent?.date_label || 'Data non disponibile'"></div>
                            <div class="text-xs text-white/50" x-text="selectedEvent?.time_label || 'Orario non disponibile'"></div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-2xl border border-white/[.07] bg-white/[.03] px-4 py-3">
                        <svg class="mt-0.5 h-4 w-4 flex-none text-[color:var(--km-events-green)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 21c-4-4-7-7.5-7-10.5a7 7 0 0 1 14 0c0 3-3 6.5-7 10.5Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>

                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-white" x-text="selectedEvent?.location || 'Online'"></div>

                            <a
                                x-show="selectedEvent?.meeting_url"
                                :href="selectedEvent?.meeting_url"
                                target="_blank"
                                class="mt-0.5 inline-flex items-center gap-1 text-xs text-[color:var(--km-events-green)] hover:underline"
                            >
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
                                </svg>
                                Apri link meeting
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="inline-flex h-9 w-9 flex-none items-center justify-center rounded-full bg-[color:var(--km-events-green)]/25 text-xs font-bold text-[color:var(--km-events-green)]"
                            x-text="initials(selectedEvent?.organizer_name)"
                        ></div>

                        <div>
                            <div class="text-[11px] text-white/40">Organizzatore</div>
                            <div class="text-sm font-semibold text-white" x-text="selectedEvent?.organizer_name || 'Organizzatore non disponibile'"></div>
                        </div>
                    </div>

                    <p
                        x-show="selectedEvent?.description"
                        class="text-sm leading-6 text-white/60"
                        x-text="selectedEvent?.description"
                    ></p>

                    <div class="grid grid-cols-3 gap-2 rounded-2xl border border-white/[.07] bg-white/[.02] p-3">
                        <div class="text-center">
                            <div class="text-base font-bold text-emerald-300" x-text="selectedEvent?.attending_count ?? 0"></div>
                            <div class="text-[10px] text-white/40">Parteciperò</div>
                        </div>

                        <div class="text-center">
                            <div class="text-base font-bold text-sky-300" x-text="selectedEvent?.interested_count ?? 0"></div>
                            <div class="text-[10px] text-white/40">Interesse</div>
                        </div>

                        <div class="text-center">
                            <div class="text-base font-bold text-white/50" x-text="selectedEvent?.capacity ? (selectedEvent.capacity - (selectedEvent.attending_count ?? 0) + ' posti') : 'libero'"></div>
                            <div class="text-[10px] text-white/40">Disponibili</div>
                        </div>
                    </div>

                    <div x-show="selectedEvent?.attendees_preview && selectedEvent.attendees_preview.length > 0">
                        <p class="mb-2 text-[11px] text-white/40">Partecipanti</p>

                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="att in (selectedEvent?.attendees_preview ?? [])" :key="att.name || att.initials">
                                <div
                                    class="inline-flex h-8 w-8 cursor-default items-center justify-center rounded-full bg-[color:var(--km-events-green)]/20 text-[11px] font-bold text-[color:var(--km-events-green)] ring-1 ring-[color:var(--km-events-green)]/20"
                                    :title="att.name || 'Partecipante'"
                                    x-text="att.initials || '?'"
                                ></div>
                            </template>

                            <div
                                x-show="selectedEvent?.attending_count > 8"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/[.07] text-[10px] font-bold text-white/50"
                                x-text="'+' + ((selectedEvent?.attending_count ?? 0) - 8)"
                            ></div>
                        </div>
                    </div>

                    <div x-show="!selectedEvent?.is_past && !selectedEvent?.is_cancelled">
                        <p class="mb-2 text-[11px] text-white/40">La tua risposta</p>

                        <div class="space-y-2">
                            <form method="POST" :action="selectedEvent?.register_url || '#'">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Enums\EventAttendanceStatus::Attending->value }}">

                                <button
                                    type="submit"
                                    class="w-full rounded-xl border px-4 py-2.5 text-sm font-semibold transition"
                                    :class="(selectedEvent?.user_status === 'attending' || selectedEvent?.user_status === 'registered')
                                        ? 'border-emerald-400/40 bg-emerald-500/25 text-emerald-200'
                                        : 'border-white/10 bg-white/[.04] text-white/70 hover:border-emerald-400/30 hover:bg-emerald-500/15 hover:text-emerald-200'"
                                >
                                    Parteciperò
                                </button>
                            </form>

                            <div class="grid grid-cols-2 gap-2">
                                <form method="POST" :action="selectedEvent?.register_url || '#'">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ \App\Enums\EventAttendanceStatus::Interested->value }}">

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl border px-3 py-2.5 text-xs font-semibold transition"
                                        :class="selectedEvent?.user_status === 'interested'
                                            ? 'border-sky-400/40 bg-sky-500/20 text-sky-200'
                                            : 'border-white/10 bg-white/[.04] text-white/60 hover:border-sky-400/25 hover:bg-sky-500/10 hover:text-sky-200'"
                                    >
                                        Mi interessa
                                    </button>
                                </form>

                                <form method="POST" :action="selectedEvent?.register_url || '#'">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ \App\Enums\EventAttendanceStatus::NotInterested->value }}">

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl border px-3 py-2.5 text-xs font-semibold transition border-white/10 bg-white/[.04] text-white/60 hover:bg-white/[.07] hover:text-white"
                                    >
                                        Non mi interessa
                                    </button>
                                </form>
                            </div>

                            <form x-show="selectedEvent?.user_status" method="POST" :action="selectedEvent?.unregister_url || '#'">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="w-full rounded-xl border border-white/[.07] px-4 py-2 text-xs text-white/45 transition hover:bg-white/[.04] hover:text-white/65">
                                    Annulla risposta
                                </button>
                            </form>
                        </div>
                    </div>

                    <div x-show="selectedEvent?.can_manage" class="space-y-2">
                        <p class="text-[11px] text-white/40">Gestione evento</p>

                        <div class="grid grid-cols-2 gap-2">
                            <a
                                :href="selectedEvent?.detail_url || '#'"
                                class="flex items-center justify-center gap-1.5 rounded-xl border border-white/10 bg-white/[.05] px-3 py-2.5 text-xs font-semibold text-white/70 transition hover:bg-white/[.09] hover:text-white"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Modifica
                            </a>

                            <form
                                x-show="!selectedEvent?.is_cancelled"
                                method="POST"
                                :action="selectedEvent?.cancel_url || '#'"
                                onsubmit="return confirm('Annullare questo evento? I partecipanti verranno informati.')"
                            >
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-red-400/20 bg-red-500/10 px-3 py-2.5 text-xs font-semibold text-red-300 transition hover:bg-red-500/20">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 6 6 18M6 6l12 12"/>
                                    </svg>
                                    Annulla
                                </button>
                            </form>
                        </div>

                        <a
                            :href="selectedEvent?.detail_url || '#'"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-white/10 bg-white/[.04] px-4 py-2.5 text-xs font-semibold text-white/65 transition hover:bg-white/[.07]"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Gestisci inviti
                        </a>
                    </div>

                    <a
                        :href="selectedEvent?.detail_url || '#'"
                        class="km-button-primary mt-auto flex items-center justify-center gap-2 text-center text-sm"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        Apri pagina evento
                    </a>
                </div>
            </div>
        </div>
    </aside>
</div>

@if ($canManageEvents)
    <div
        x-cloak
        x-show="createOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-sm"
    >
        <div class="flex min-h-full items-start justify-center py-8">
            <div class="absolute inset-0" @click="createOpen = false"></div>

            <div
                x-show="createOpen"
                x-transition
                class="relative z-10 w-full max-w-xl rounded-[28px] border border-white/10 bg-[rgba(26,36,50,0.97)] shadow-[0_32px_80px_rgba(8,12,22,0.55)] backdrop-blur-xl"
            >
                <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
                    <div>
                        <div class="text-[10px] uppercase tracking-[0.22em] text-white/40">Calendario Kommunity</div>
                        <h2 class="mt-1 text-xl font-semibold text-white">Crea nuovo evento</h2>
                    </div>

                    <button @click="createOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 text-white/60 transition hover:bg-white/[.07]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="divide-y divide-white/[.07]">
                    @csrf

                    <div class="space-y-4 px-6 py-5">
                        <p class="text-[10px] uppercase tracking-[0.2em] text-white/40">Informazioni evento</p>

                        <select name="chapter_id" class="km-portal-input w-full" required>
                            <option value="">Seleziona pianeta *</option>

                            @foreach ($managedChapters as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter?->name ?? 'Pianeta eliminato' }}</option>
                            @endforeach
                        </select>

                        <input type="text" name="title" class="km-portal-input w-full" placeholder="Titolo evento *" required>

                        <select name="type" class="km-portal-input w-full" required>
                            <option value="">Tipo evento *</option>

                            @foreach ($eventTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-[11px] text-white/50">Inizio *</label>
                                <input type="datetime-local" name="starts_at" class="km-portal-input w-full" required>
                            </div>

                            <div>
                                <label class="mb-1 block text-[11px] text-white/50">Fine</label>
                                <input type="datetime-local" name="ends_at" class="km-portal-input w-full">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="location" class="km-portal-input w-full" placeholder="Luogo (vuoto = Online)">
                            <input type="url" name="meeting_url" class="km-portal-input w-full" placeholder="Link meeting">
                        </div>

                        <textarea name="description" rows="3" class="km-portal-input w-full resize-none" placeholder="Descrizione (opzionale)"></textarea>

                        <div class="grid grid-cols-2 gap-3">
                            <input type="number" name="capacity" min="1" class="km-portal-input w-full" placeholder="Capienza max">

                            <div>
                                <label class="mb-1 block text-[11px] text-white/50">Copertina</label>
                                <input type="file" name="cover_image" accept="image/*" class="km-portal-input w-full text-xs">
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-white/[.03] px-4 py-3 text-sm text-white/70 transition hover:border-white/15">
                            <input type="checkbox" name="is_published" value="1" class="rounded border-white/30 bg-white/10 text-[color:var(--km-events-green)] focus:ring-[color:var(--km-events-green)]/30">
                            Pubblica subito
                        </label>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <p class="text-[10px] uppercase tracking-[0.2em] text-white/40">Invita partecipanti</p>

                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([
                                'none'       => 'Nessuno',
                                'all'        => 'Tutti',
                                'chapter'    => 'Pianeta',
                                'profession' => 'Professione',
                                'category'   => 'Categoria',
                                'city'       => 'Città',
                                'region'     => 'Regione',
                                'users'      => 'Singoli',
                            ] as $val => $lbl)
                                <label
                                    class="flex cursor-pointer items-center justify-center rounded-xl border px-2 py-2.5 text-xs font-semibold transition"
                                    :class="inviteTarget === '{{ $val }}'
                                        ? 'border-[color:var(--km-events-green)] bg-[color:var(--km-events-green)]/15 text-white'
                                        : 'border-white/10 bg-white/[.03] text-white/55 hover:border-white/15 hover:text-white'"
                                >
                                    <input type="radio" name="invite_target" value="{{ $val }}" x-model="inviteTarget" class="sr-only">
                                    {{ $lbl }}
                                </label>
                            @endforeach
                        </div>

                        <div x-show="inviteTarget === 'chapter'" x-cloak>
                            <select name="invite_chapter_id" class="km-portal-input w-full">
                                <option value="">Scegli pianeta</option>

                                @foreach ($managedChapters as $ch)
                                    <option value="{{ $ch->id }}">{{ $ch?->name ?? 'Pianeta eliminato' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="inviteTarget === 'profession'" x-cloak>
                            <select name="invite_profession_id" class="km-portal-input w-full">
                                <option value="">Scegli professione</option>

                                @foreach ($professions as $p)
                                    <option value="{{ $p->id }}">{{ $p?->name ?? 'Professione eliminata' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="inviteTarget === 'category'" x-cloak>
                            <select name="invite_category_id" class="km-portal-input w-full">
                                <option value="">Scegli categoria</option>

                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat?->name ?? 'Categoria eliminata' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="inviteTarget === 'city'" x-cloak>
                            <select name="invite_city_id" class="km-portal-input w-full">
                                <option value="">Scegli città</option>

                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city?->name ?? 'Città eliminata' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="inviteTarget === 'region'" x-cloak>
                            <select name="invite_region_id" class="km-portal-input w-full">
                                <option value="">Scegli regione</option>

                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region?->name ?? 'Regione eliminata' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="inviteTarget === 'users'" x-cloak class="space-y-2">
                            <input type="text" x-model="userSearch" class="km-portal-input w-full" placeholder="Cerca per nome o azienda...">

                            <div class="max-h-48 overflow-y-auto rounded-2xl border border-white/10 bg-white/[.02]">
                                <template x-for="u in filteredUsers" :key="u.id">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 border-b border-white/[.06] px-3 py-2.5 transition last:border-b-0 hover:bg-white/[.05]"
                                        :class="selectedUserIds.includes(u.id) ? 'bg-[color:var(--km-events-green)]/10' : ''"
                                    >
                                        <input
                                            type="checkbox"
                                            name="invite_user_ids[]"
                                            :value="u.id"
                                            @change="toggleUser(u.id)"
                                            :checked="selectedUserIds.includes(u.id)"
                                            class="rounded border-white/30 bg-white/10 text-[color:var(--km-events-green)] focus:ring-[color:var(--km-events-green)]/30"
                                        >

                                        <span class="text-sm text-white/75" x-text="u.label || 'Utente senza nome'"></span>
                                    </label>
                                </template>

                                <template x-if="filteredUsers.length === 0">
                                    <div class="px-3 py-5 text-center text-sm text-white/40">Nessun risultato</div>
                                </template>
                            </div>

                            <p x-show="selectedUserIds.length > 0" class="text-xs text-white/45" x-text="selectedUserIds.length + ' utenti selezionati'"></p>
                        </div>

                        <div x-show="inviteTarget !== 'none'" x-cloak class="rounded-2xl border border-white/[.07] bg-white/[.015] px-4 py-3 text-xs text-white/45">
                            Gli utenti riceveranno un'email e una notifica interna con il link all'evento.
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4">
                        <button type="button" @click="createOpen = false" class="km-button-secondary px-5 py-2.5 text-sm">
                            Annulla
                        </button>

                        <button type="submit" class="km-button-primary px-5 py-2.5 text-sm">
                            <span x-text="inviteTarget !== 'none' ? 'Crea e invia inviti' : 'Crea evento'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@foreach ([
    'event-created'          => ['Evento creato con successo', 'emerald'],
    'event-cancelled'        => ['Evento annullato', 'red'],
    'event-response-updated' => ['Risposta registrata', 'emerald'],
    'event-unregistered'     => ['Risposta rimossa', 'white'],
    'invitations-sent'       => ['Inviti inviati', 'emerald'],
    'event-full'             => ['Evento al completo', 'amber'],
] as $flashKey => [$flashMsg, $flashColor])
    @if (session('status') === $flashKey)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition.opacity
            class="fixed bottom-6 right-6 z-[60] rounded-2xl border px-5 py-3 text-sm font-semibold shadow-lg backdrop-blur
                @if($flashColor === 'emerald') border-emerald-400/30 bg-emerald-500/20 text-emerald-200
                @elseif($flashColor === 'red') border-red-400/30 bg-red-500/20 text-red-200
                @elseif($flashColor === 'amber') border-amber-400/30 bg-amber-500/20 text-amber-200
                @else border-white/20 bg-white/10 text-white/80 @endif"
        >
            {{ $flashMsg }}
        </div>
    @endif
@endforeach

</div>
</x-app-layout>
