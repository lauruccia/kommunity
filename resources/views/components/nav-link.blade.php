@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center whitespace-nowrap border-b-2 border-[color:var(--km-accent)] px-1 pt-1 text-sm font-medium leading-5 text-[color:var(--km-deep-strong)] focus:outline-none focus:border-[color:var(--km-accent-strong)] transition duration-150 ease-in-out'
            : 'inline-flex items-center whitespace-nowrap border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-stone-500 hover:border-[rgba(70,93,112,0.24)] hover:text-[color:var(--km-deep-strong)] focus:outline-none focus:border-[rgba(70,93,112,0.24)] focus:text-[color:var(--km-deep-strong)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
