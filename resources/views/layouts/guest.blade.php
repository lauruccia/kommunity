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
    <body class="font-sans text-stone-900 antialiased">
        <div class="relative flex min-h-screen w-full flex-col justify-center overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
            <div class="absolute inset-x-0 top-0 -z-10 h-[26rem] bg-[radial-gradient(circle_at_top_left,_rgba(70,93,112,0.20),_transparent_38%)]"></div>
            <div class="absolute inset-x-0 bottom-0 -z-10 h-[24rem] bg-[radial-gradient(circle_at_bottom_right,_rgba(85,121,79,0.16),_transparent_34%)]"></div>
            <div class="w-full">
                <a href="/">
                    <div class="km-brand-lockup">
                        <div class="km-brand-mark">
                            <x-application-logo />
                        </div>
                        <div>
                            <div class="font-serif text-3xl font-semibold text-stone-950">Kommunity</div>
                            <div class="km-brand-kicker">Community professionale</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="km-panel mt-8 w-full overflow-hidden border-white/60 bg-[color:var(--km-surface-strong)]/[0.86] px-6 py-6 backdrop-blur sm:px-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
