@props([
    'label',
    'value',
    'sub' => null,
    'tone' => 'green', // green | teal | amber | rose | white
    'href' => null,
    'badge' => null,
])

@php
    $toneMap = [
        'green' => ['bg' => 'rgba(139,197,63,.10)', 'text' => 'var(--km-green-2)', 'ring' => 'rgba(139,197,63,.22)'],
        'teal'  => ['bg' => 'rgba(45,212,191,.10)', 'text' => '#5EEAD4', 'ring' => 'rgba(45,212,191,.22)'],
        'amber' => ['bg' => 'rgba(245,158,11,.12)', 'text' => '#FCD34D', 'ring' => 'rgba(245,158,11,.22)'],
        'rose'  => ['bg' => 'rgba(244,63,94,.12)',  'text' => '#FDA4AF', 'ring' => 'rgba(244,63,94,.22)'],
        'white' => ['bg' => 'rgba(255,255,255,.06)','text' => '#FFFFFF', 'ring' => 'rgba(255,255,255,.18)'],
    ];
    $t = $toneMap[$tone] ?? $toneMap['green'];
@endphp

@if ($href)
    <a href="{{ $href }}"
       class="group relative flex h-full items-center gap-3 rounded-2xl border border-white/[.08] bg-white/[.03] p-4 transition hover:-translate-y-[2px] hover:border-[rgba(139,197,63,.30)]"
       style="backdrop-filter: blur(12px);">
@else
    <div class="relative flex h-full items-center gap-3 rounded-2xl border border-white/[.08] bg-white/[.03] p-4"
         style="backdrop-filter: blur(12px);">
@endif

    {{-- Icona a sinistra --}}
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
          style="background: {{ $t['bg'] }}; color: {{ $t['text'] }}; box-shadow: inset 0 0 0 1px {{ $t['ring'] }};">
        {{ $icon ?? '' }}
        @if (! isset($icon))
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 7v6c0 5 3.5 8.5 8 9 4.5-.5 8-4 8-9V7l-8-5z"/></svg>
        @endif
    </span>

    {{-- Testo a destra --}}
    <div class="min-w-0 flex-1">
        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/55">{{ $label }}</p>
        <p class="text-xl font-black leading-tight tracking-tight text-white sm:text-2xl">{{ $value }}</p>
        @if ($sub)
            <p class="mt-0.5 text-xs leading-4 text-white/50">{{ $sub }}</p>
        @endif
    </div>

    {{-- Badge in alto a destra --}}
    @if ($badge)
        <span class="absolute right-3 top-3 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
              style="background: {{ $t['bg'] }}; color: {{ $t['text'] }};">{{ $badge }}</span>
    @endif

@if ($href)
    </a>
@else
    </div>
@endif
