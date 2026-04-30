<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kommunity') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-white antialiased km-portal-bg">
        <div class="relative flex min-h-screen w-full flex-col justify-center overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
            <div class="pointer-events-none absolute right-[10%] top-[-9rem] text-[28rem] font-black leading-none text-white/[0.035]">K</div>
            <div class="w-full">
                <a href="/">
                    <div class="km-brand-lockup">
                        <div class="km-brand-mark border-white/15 bg-white/10">
                            <x-application-logo />
                        </div>
                        <div>
                            <div class="text-3xl font-black tracking-[-0.055em] text-white">Kommunity</div>
                            <div class="km-portal-eyebrow text-[10px]">Community professionale</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="km-portal-panel mt-8 w-full overflow-hidden px-6 py-6 sm:px-8">
                {{ $slot }}
            </div>
        </div>

        {{-- Cookie banner GDPR (sempre presente, anche su login/register) --}}
        @include('partials.cookie-banner')
    </body>
</html>
