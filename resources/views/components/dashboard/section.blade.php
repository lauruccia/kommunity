@props([
    'eyebrow' => null,
    'title',
    'href' => null,
    'cta' => 'Vedi tutto',
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-white/[.08] bg-white/[.03] ' . ($compact ? 'p-4' : 'p-5 sm:p-6')]) }}
     style="backdrop-filter: blur(12px);">
    <div class="mb-4 flex items-end justify-between gap-3">
        <div class="min-w-0">
            @if($eyebrow)
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-[color:var(--km-green-2)]">{{ $eyebrow }}</p>
            @endif
            <h2 class="mt-1 truncate text-lg font-black tracking-tight text-white sm:text-xl">{{ $title }}</h2>
        </div>
        @if($href)
            <a href="{{ $href }}"
               class="shrink-0 rounded-full border border-white/10 bg-white/[.04] px-3 py-1.5 text-xs font-semibold text-white/80 transition hover:border-[rgba(139,197,63,.4)] hover:text-[color:var(--km-green-2)]">
                {{ $cta }} &rarr;
            </a>
        @endif
    </div>

    {{ $slot }}
</div>
