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

        {{-- Design system Kommunity (file STATICO modificabile via cPanel File Manager).
             Cache-busting cross-environment: in locale Laragon il file vive in public/css/,
             su cPanel produzione vive in ../public_html/css/ (web root del dominio). --}}
        @php
            $kmCssCandidates = [
                public_path('css/kommunity.css'),                  // locale (Laragon serve public/ come web root)
                base_path('../public_html/css/kommunity.css'),     // produzione cPanel (web root = public_html/)
            ];
            $kmCssVer = '1';
            foreach ($kmCssCandidates as $kmCssPath) {
                if (file_exists($kmCssPath)) { $kmCssVer = filemtime($kmCssPath); break; }
            }
        @endphp
        <link rel="stylesheet" href="{{ asset('css/kommunity.css') }}?v={{ $kmCssVer }}">

        @stack('styles')
        {{-- Nasconde gli elementi [x-cloak] PRIMA che Alpine.js inizializzi.
             Deve stare nell'<head>, non nel body, altrimenti Alpine rimuove
             x-cloak prima che la regola CSS venga letta → flash fullscreen. --}}
        <style>[x-cloak]{display:none!important}</style>
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
