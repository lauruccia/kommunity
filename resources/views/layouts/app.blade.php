<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kommunity') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased text-stone-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            {{-- Flash messages globali --}}
            @if(session('warning'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="relative z-40 bg-amber-50 border-b border-amber-300">
                    <div class="km-shell flex items-start gap-3 py-3 px-4">
                        <span class="mt-0.5 text-lg leading-none">⚠️</span>
                        <p class="flex-1 text-sm font-medium text-amber-800">{{ session('warning') }}</p>
                        <button @click="show = false" class="ml-2 shrink-0 text-amber-500 hover:text-amber-700 text-lg leading-none">&times;</button>
                    </div>
                </div>
            @endif
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="relative z-40 bg-emerald-50 border-b border-emerald-300">
                    <div class="km-shell flex items-start gap-3 py-3 px-4">
                        <span class="mt-0.5 text-lg leading-none">✅</span>
                        <p class="flex-1 text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                        <button @click="show = false" class="ml-2 shrink-0 text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="relative z-40 bg-red-50 border-b border-red-300">
                    <div class="km-shell flex items-start gap-3 py-3 px-4">
                        <span class="mt-0.5 text-lg leading-none">❌</span>
                        <p class="flex-1 text-sm font-medium text-red-800">{{ session('error') }}</p>
                        <button @click="show = false" class="ml-2 shrink-0 text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
                    </div>
                </div>
            @endif
            @if(session('status') === 'profile-updated')
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="relative z-40 bg-emerald-50 border-b border-emerald-300">
                    <div class="km-shell flex items-start gap-3 py-3 px-4">
                        <span class="mt-0.5 text-lg leading-none">✅</span>
                        <p class="flex-1 text-sm font-medium text-emerald-800">Profilo aggiornato con successo!</p>
                        <button @click="show = false" class="ml-2 shrink-0 text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                    </div>
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
    </body>
</html>
