<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kommunity') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />

        {{-- PWA: manifest + icone (Feature #6) --}}
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#0b0d12">
        <link rel="apple-touch-icon" href="/images/icon-192.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Design system Kommunity (file STATICO in public/css/, modificabile via cPanel)
             Cache-busting via filemtime: ogni volta che modifichi il file, cambia il param ?v= --}}
        @php
            $kmCssPath = public_path('css/kommunity.css');
            $kmCssVer = file_exists($kmCssPath) ? filemtime($kmCssPath) : '1';
        @endphp
        <link rel="stylesheet" href="{{ asset('css/kommunity.css') }}?v={{ $kmCssVer }}">

        @stack('styles')
    </head>
    <body class="font-sans antialiased text-stone-900 @stack('body-class')">
        <div class="min-h-screen">
            @include('layouts.navigation')

            {{-- Flash toast: top-right, auto-dismiss, contenuti nel viewport --}}
            @php
                $toastSuccess = session('success') ?: (session('status') === 'profile-updated' ? 'Profilo aggiornato con successo!' : null);
                $toastError   = session('error');
                $toastWarning = session('warning');
            @endphp
            @if($toastSuccess || $toastError || $toastWarning)
                <div class="km-toast-stack" aria-live="polite">
                    @if($toastSuccess)
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                             class="km-toast km-toast-success">
                            <span class="km-toast-icon">&#10003;</span>
                            <p class="km-toast-body">{{ $toastSuccess }}</p>
                            <button type="button" @click="show = false" class="km-toast-close" aria-label="Chiudi">&times;</button>
                        </div>
                    @endif
                    @if($toastError)
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 7000)" x-show="show" x-transition
                             class="km-toast km-toast-error">
                            <span class="km-toast-icon">!</span>
                            <p class="km-toast-body">{{ $toastError }}</p>
                            <button type="button" @click="show = false" class="km-toast-close" aria-label="Chiudi">&times;</button>
                        </div>
                    @endif
                    @if($toastWarning)
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
                             class="km-toast km-toast-warning">
                            <span class="km-toast-icon">!</span>
                            <p class="km-toast-body">{{ $toastWarning }}</p>
                            <button type="button" @click="show = false" class="km-toast-close" aria-label="Chiudi">&times;</button>
                        </div>
                    @endif
                </div>

            @endif

            @isset($header)
                <header class="pt-8">
                    <div class="km-shell">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            <main>
                {{ $slot }}
            </main>
        </div>

        {{-- Modali globali (password, elimina account, ecc.) --}}
        @stack('modals')

        {{-- Cookie banner GDPR (sempre presente) --}}
        @include('partials.cookie-banner')

        {{-- Push notification consent banner (Feature #6, gated) --}}
        @auth
            @include('partials.push-consent-banner')
        @endauth
    </body>
</html>
