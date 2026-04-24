@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-[rgba(85,121,79,0.18)] bg-[rgba(219,231,216,0.55)] px-4 py-3 text-sm font-medium text-[color:var(--km-accent-strong)]']) }}>
        {{ $status }}
    </div>
@endif
