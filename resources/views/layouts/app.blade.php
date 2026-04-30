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
        @stack('styles')
    </head>
    <body class="font-sans antialiased text-stone-900">
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

                <style>
                    .km-toast-stack {
                        position: fixed;
                        top: 1rem;
                        right: 1rem;
                        z-index: 9999;
                        display: flex;
                        flex-direction: column;
                        gap: .65rem;
                        max-width: min(22rem, calc(100vw - 2rem));
                        pointer-events: none;
                    }
                    .km-toast {
                        pointer-events: auto;
                        display: flex;
                        align-items: flex-start;
                        gap: .65rem;
                        padding: .85rem 1rem;
                        border-radius: 14px;
                        box-shadow: 0 16px 38px rgba(15, 23, 42, .18), 0 2px 6px rgba(15, 23, 42, .08);
                        border: 1px solid;
                        font-size: .9rem;
                        line-height: 1.4;
                        backdrop-filter: blur(10px);
                    }
                    .km-toast-icon {
                        flex-shrink: 0;
                        width: 1.5rem;
                        height: 1.5rem;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 999px;
                        font-weight: 700;
                        font-size: .85rem;
                    }
                    .km-toast-body { flex: 1; min-width: 0; margin: 0; word-wrap: break-word; }
                    .km-toast-close {
                        flex-shrink: 0;
                        background: transparent;
                        border: 0;
                        font-size: 1.25rem;
                        line-height: 1;
                        cursor: pointer;
                        opacity: .6;
                        padding: 0 .15rem;
                    }
                    .km-toast-close:hover { opacity: 1; }
                    .km-toast-success { background: #ecfdf5; border-color: #6ee7b7; color: #065f46; }
                    .km-toast-success .km-toast-icon { background: #10b981; color: white; }
                    .km-toast-error { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
                    .km-toast-error .km-toast-icon { background: #ef4444; color: white; }
                    .km-toast-warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
                    .km-toast-warning .km-toast-icon { background: #f59e0b; color: white; }
                </style>
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
