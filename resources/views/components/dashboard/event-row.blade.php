@props([
    'event',
])

@php
    $start = $event->starts_at;
    $day   = $start?->translatedFormat('d');
    $month = $start ? \Illuminate\Support\Str::upper($start->translatedFormat('M')) : null;
    $time  = $start?->format('H:i');
    $isToday = $start && $start->isToday();
    $isTomorrow = $start && $start->isTomorrow();
@endphp

<a href="{{ route('events.show', $event) }}"
   class="flex items-center gap-3 rounded-xl border border-white/[.08] bg-white/[.04] p-3 transition hover:-translate-y-[1px] hover:border-[rgba(139,197,63,.30)]">
    @if ($start)
        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg border text-center"
             style="border-color: {{ $isToday ? 'rgba(139,197,63,.45)' : 'rgba(255,255,255,.10)' }}; background: {{ $isToday ? 'rgba(139,197,63,.10)' : 'rgba(255,255,255,.03)' }};">
            <span class="text-[9px] font-bold uppercase tracking-wider"
                  style="color: {{ $isToday ? 'var(--km-green-2)' : 'rgba(255,255,255,.55)' }};">{{ $month }}</span>
            <span class="text-base font-black leading-none text-white">{{ $day }}</span>
        </div>
    @endif

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-bold text-white">{{ $event->title }}</p>
        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-[11px] text-white/55">
            @if ($time)
                <span class="tabular-nums text-white/65">
                    @if($isToday) Oggi · @elseif($isTomorrow) Domani · @endif
                    {{ $time }}
                </span>
            @endif
            @if (! empty($event->location))
                <span class="truncate">· {{ $event->location }}</span>
            @endif
        </div>
    </div>

    <svg class="h-4 w-4 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 6l6 6-6 6"/>
    </svg>
</a>
