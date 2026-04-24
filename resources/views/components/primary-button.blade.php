<button {{ $attributes->merge(['type' => 'submit', 'class' => 'km-button-primary']) }}>
    {{ $slot }}
</button>
