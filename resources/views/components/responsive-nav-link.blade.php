@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-[color:var(--km-accent)] bg-[rgba(85,121,79,0.10)] ps-3 pe-4 py-2 text-start text-base font-medium text-[color:var(--km-accent-strong)] focus:outline-none focus:bg-[rgba(85,121,79,0.14)] focus:text-[color:var(--km-accent-strong)] transition duration-150 ease-in-out'
            : 'block w-full border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-base font-medium text-stone-600 hover:border-[rgba(70,93,112,0.20)] hover:bg-white/60 hover:text-[color:var(--km-deep-strong)] focus:outline-none focus:border-[rgba(70,93,112,0.20)] focus:bg-white/60 focus:text-[color:var(--km-deep-strong)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
