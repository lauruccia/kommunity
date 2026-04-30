@props([
    'href',
    'title',
    'desc' => null,
    'tone' => 'green',
])

@php
    $toneMap = [
        'green' => ['#8BC53F', '#5f9d42'],
        'teal'  => ['#2DD4BF', '#0D9488'],
        'amber' => ['#F59E0B', '#B45309'],
    ];
    [$c1, $c2] = $toneMap[$tone] ?? $toneMap['green'];
@endphp

<a href="{{ $href }}"
   class="group flex items-center gap-3 rounded-2xl border border-white/[.08] bg-white/[.03] p-4 transition hover:-translate-y-[2px] hover:border-[rgba(139,197,63,.35)]"
   style="backdrop-filter: blur(12px);">
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white"
          style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }}); box-shadow: 0 8px 22px rgba(139,197,63,.18);">
        {{ $icon ?? '' }}
        @if (! isset($icon))
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        @endif
    </span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-bold text-white">{{ $title }}</p>
        @if ($desc)
            <p class="mt-0.5 truncate text-xs text-white/55">{{ $desc }}</p>
        @endif
    </div>
    <svg class="h-4 w-4 shrink-0 text-white/30 transition group-hover:translate-x-0.5 group-hover:text-[color:var(--km-green-2)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 6l6 6-6 6"/>
    </svg>
</a>
