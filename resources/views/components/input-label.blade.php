@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-[color:var(--km-deep-strong)]']) }}>
    {{ $value ?? $slot }}
</label>
