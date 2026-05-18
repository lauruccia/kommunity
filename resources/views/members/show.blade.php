{{-- KM-MEMBER-VIEW-v2 --}}
<x-app-layout>
    @php
        $viewerIsOwner = auth()->check() && auth()->id() === $user->id;

        $whatsappUrl = null;

        if ($profile->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number) {
            $whatsappUrl = 'https://wa.me/' . preg_replace('/\D+/', '', $profile->whatsapp_number)
                . '?text=' . urlencode('Ciao ' . $user->name . ', ti contatto dalla tua pagina su Kommunity.');
        }
    @endphp

    <style>
        .member-banner {
            height: 180px;
        }

        @media (min-width: 640px) {
            .member-banner {
                height: 280px;
            }
        }

        @media (min-width: 1024px) {
            .member-banner {
                height: 420px;
            }

            .member-grid {
                display: grid !important;
                gap: 1.5rem;
                grid-template-columns: 320px minmax(0, 1fr) !important;
                align-items: start;
            }
        }

        .member-avatar-wrap {
            position: relative;
        }

        .member-avatar-pin {
            position: absolute;
            top: -3rem;
            left: 1.25rem;
            z-index: 10;
        }

        @media (min-width: 640px) {
            .member-avatar-pin {
                left: 1.5rem;
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <div x-data="{ drawer: false }" class="pb-12 pt-6">

        @if (session('status'))
            @php
                $videoStatusMessages = [
                    'video-access-requested' => 'Richiesta video inviata.',
                    'video-access-accepted' => 'Scambio video accettato.',
                    'video-access-declined' => 'Richiesta video rifiutata.',
                    'video-access-revoked' => 'Accesso video revocato.',
                    'video-access-own-video-required' => 'Carica prima la tua videopresentazione per richiedere uno scambio.',
                    'video-access-unavailable' => 'La funzione video privato sara disponibile dopo l aggiornamento database.',
                ];
            @endphp

            @if (isset($videoStatusMessages[session('status')]))
                <div class="mx-4 mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-800 sm:mx-6 lg:mx-8">
                    {{ $videoStatusMessages[session('status')] }}
                </div>
            @endif
        @endif

        {{-- MOBILE TOP BAR --}}
        <div class="mb-4 flex items-center justify-between px-4 sm:px-6 lg:hidden">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-stone-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-stone-500">
                    {{ $profile->company_name ?: 'Attività professionale' }}
                </p>
            </div>

            <button
                type="button"
                @click="drawer = true"
                class="ms-3 flex shrink-0 items-center gap-2 rounded-2xl border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-700 shadow-sm transition hover:bg-stone-50 active:scale-95"
                aria-label="Apri info membro"
            >
                <svg class="h-4 w-4 text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span>Info</span>
            </button>
        </div>

        {{-- MOBILE OVERLAY --}}
        <div
            x-show="drawer"
            x-cloak
            x-transition.opacity
            @click="drawer = false"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            style="backdrop-filter: blur(2px);"
        ></div>

        {{-- MOBILE DRAWER --}}
        <div
            x-show="drawer"
            x-cloak
            x-transition
            class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[88vw] flex-col bg-stone-50 shadow-2xl lg:hidden"
            style="border-right: 1px solid #e7e5e4;"
            @click.stop
        >
            <div class="flex shrink-0 items-center justify-between border-b border-stone-200 bg-white px-5 py-4">
                <div class="min-w-0">
                    <p class="truncate font-semibold text-stone-900">{{ $user->name }}</p>
                    <p class="truncate text-xs text-stone-500">
                        @if ($profile->professions->isNotEmpty())
                            {{ $profile->professions->pluck('name')->first() }}
                        @else
                            {{ $profile->profession?->name ?? 'Membro Kommunity' }}
                        @endif
                    </p>
                </div>

                <button
                    type="button"
                    @click="drawer = false"
                    class="ms-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-stone-500 transition hover:bg-stone-100 hover:text-stone-700"
                    aria-label="Chiudi"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto p-4">
                @include('members._sidebar')
            </div>

            @unless ($viewerIsOwner)
                <div class="shrink-0 space-y-2 border-t border-stone-200 bg-white p-4">
                    {{-- Referenze ricevute sopra il pulsante --}}
                    @if (!empty($receivedReferralsCount) && $receivedReferralsCount > 0)
                        <a href="{{ route('members.referrals', $onepage->slug) }}"
                           class="flex items-center justify-center gap-2 rounded-2xl bg-amber-50 px-4 py-2.5 transition hover:bg-amber-100">
                            @if (!empty($receivedReferralsAvgPriority))
                                <span class="flex gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= floor($receivedReferralsAvgPriority) ? 'text-yellow-400' : 'text-stone-300' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </span>
                            @endif
                            <span class="text-sm font-semibold text-stone-700">{{ $receivedReferralsCount }} {{ $receivedReferralsCount === 1 ? 'referenza ricevuta' : 'referenze ricevute' }}</span>
                            <svg class="h-3.5 w-3.5 text-stone-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                        </a>
                    @endif
                    {{-- Apre la pagina messaggi con il destinatario precompilato. NESSUN invio automatico. --}}
                    <a href="{{ route('conversations.index', ['to' => $user->id]) }}"
                       class="km-button-secondary inline-flex w-full items-center justify-center">
                        Messaggio diretto
                    </a>

                    {{-- Apre il form one-to-one con il destinatario precompilato. NESSUN invio automatico. --}}
                    <a href="{{ route('one-to-ones.index', ['member' => $user->id, 'compose' => 1]) }}"
                       class="inline-flex w-full items-center justify-center rounded-full border border-transparent bg-[color:var(--km-accent)] px-5 py-3 text-sm font-semibold text-white shadow-[0_8px_24px_rgba(66,98,64,0.25)] transition hover:bg-[color:var(--km-accent-strong)]">
                        Prenota one-to-one
                    </a>
                </div>
            @endunless
        </div>

        {{-- MAIN LAYOUT --}}
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div id="km-member-grid" class="member-grid grid grid-cols-1 gap-6">

                {{-- SIDEBAR DESKTOP --}}
                <aside class="hidden space-y-6 lg:block">
                    @include('members._sidebar')
                </aside>

                {{-- CONTENT --}}
                <section class="min-w-0 space-y-6">

                    {{-- HERO CARD --}}
                    <div class="km-panel overflow-hidden p-0">
                        <div
                            class="member-banner relative bg-[linear-gradient(135deg,#425767_0%,#d7e3d1_100%)]"
                            @if ($onepage->coverImageUrl())
                                style="background-image: url('{{ $onepage->coverImageUrl() }}'); background-size: cover; background-position: left top;"
                            @endif
                        >
                            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(22,31,41,0.04),rgba(22,31,41,0.28))]"></div>
                        </div>

                        <div class="member-avatar-wrap">
                            <div class="member-avatar-pin">
                                @if ($profile->avatarUrl() || $profile->logoUrl())
                                    <img
                                        src="{{ $profile->avatarUrl() ?: $profile->logoUrl() }}"
                                        alt="{{ $user->name }}"
                                        class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-[0_12px_28px_rgba(38,52,63,0.18)]"
                                    >
                                @else
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-stone-900 text-4xl font-semibold text-white shadow-[0_12px_28px_rgba(38,52,63,0.18)]">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 px-5 pb-6 pt-16 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <h1 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    {{ $onepage->hero_title ?: $user->name }}
                                </h1>

                                @php $heroSub = $onepage->hero_subtitle ?: $profile->company_name; @endphp
                                @if ($heroSub)
                                    <p class="mt-0.5 text-sm text-stone-500">{{ $heroSub }}</p>
                                @endif

                                @php
                                    $profsLabel = $profile->professions->isNotEmpty()
                                        ? $profile->professions->pluck('name')->join(', ')
                                        : ($profile->profession?->name ?? null);
                                @endphp

                                @if ($profsLabel)
                                    <p class="mt-1 text-sm text-stone-600">{{ $profsLabel }}</p>
                                @endif

                                @php $shortBio = $onepage->intro_text ?: $profile->short_bio; @endphp
                                @if ($shortBio)
                                    <p class="mt-2 text-sm italic text-stone-500">{{ $shortBio }}</p>
                                @endif

                            </div>

                            @unless ($viewerIsOwner)
                                <div class="hidden flex-col items-end gap-3 sm:flex">
                                    {{-- Referenze ricevute sopra il pulsante --}}
                                    @if (!empty($receivedReferralsCount) && $receivedReferralsCount > 0)
                                        <a href="{{ route('members.referrals', $onepage->slug) }}"
                                           class="flex items-center gap-2 rounded-2xl bg-amber-50 px-4 py-2 transition hover:bg-amber-100">
                                            @if (!empty($receivedReferralsAvgPriority))
                                                <span class="flex gap-0.5">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="h-4 w-4 {{ $i <= floor($receivedReferralsAvgPriority) ? 'text-yellow-400' : 'text-stone-300' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                    @endfor
                                                </span>
                                            @endif
                                            <span class="text-sm font-semibold text-stone-700">{{ $receivedReferralsCount }} {{ $receivedReferralsCount === 1 ? 'referenza' : 'referenze' }}</span>
                                            <svg class="h-3.5 w-3.5 text-stone-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                                        </a>
                                    @endif
                                    <div class="flex flex-wrap gap-3">
                                        {{-- Apre la pagina messaggi con il destinatario precompilato. NESSUN invio automatico. --}}
                                        <a href="{{ route('conversations.index', ['to' => $user->id]) }}"
                                           class="km-button-secondary inline-flex items-center justify-center">
                                            Messaggio diretto
                                        </a>

                                        {{-- Apre il form one-to-one con il destinatario precompilato. NESSUN invio automatico. --}}
                                        <a href="{{ route('one-to-ones.index', ['member' => $user->id, 'compose' => 1]) }}"
                                           class="inline-flex items-center justify-center rounded-full border border-transparent bg-[color:var(--km-accent)] px-5 py-3 text-sm font-semibold text-white shadow-[0_16px_36px_rgba(66,98,64,0.28)] transition hover:bg-[color:var(--km-accent-strong)]">
                                            Prenota one-to-one
                                        </a>
                                    </div>
                                </div>
                            @endunless
                        </div>
                    </div>

                    {{-- DETAILS --}}
                    <div class="km-panel p-5 sm:p-6">
                        <div class="grid gap-8">

                            @php $aboutText = $onepage->about_text ?: $profile->bio; @endphp
                            @if ($aboutText)
                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    Chi sono
                                </h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">{{ $aboutText }}</p>
                            </div>
                            @endif

                            @php
                                $servicesText = $onepage->services_text ?: $profile->services;
                                $skillsList   = $profile->skills
                                    ? collect(explode(',', $profile->skills))->map(fn ($s) => trim($s))->filter()
                                    : collect();
                            @endphp
                            @if ($servicesText)
                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    Servizi
                                </h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">{{ $servicesText }}</p>
                            </div>
                            @endif

                            @if ($skillsList->isNotEmpty())
                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    Competenze
                                </h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">{{ $skillsList->implode(', ') }}</p>
                            </div>
                            @endif

                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    Obiettivi di networking
                                </h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">
                                    {{ $profile->networking_goals ?: 'Disponibile a creare sinergie, referral qualificati e nuove collaborazioni nella kommunity.' }}
                                </p>
                            </div>

                            @if ($user->memberGalleryImages->isNotEmpty())
                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    Gallery
                                </h2>

                                @if ($user->memberGalleryImages->isNotEmpty())
                                    @php
                                        $galleryUrls = $user->memberGalleryImages
                                            ->map(fn ($image) => $image->imageUrl())
                                            ->values()
                                            ->all();
                                    @endphp

                                    <div
                                        x-data="{
                                            open: false,
                                            current: 0,
                                            images: @js($galleryUrls),
                                            prev() {
                                                this.current = (this.current - 1 + this.images.length) % this.images.length;
                                            },
                                            next() {
                                                this.current = (this.current + 1) % this.images.length;
                                            }
                                        }"
                                        @keydown.escape.window="open && (open = false)"
                                        @keydown.arrow-left.window="open && prev()"
                                        @keydown.arrow-right.window="open && next()"
                                    >
                                        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                                            @foreach ($user->memberGalleryImages as $idx => $galleryImage)
                                                <button
                                                    type="button"
                                                    @click="current = {{ $idx }}; open = true"
                                                    class="group overflow-hidden rounded-[1.2rem] border border-stone-200 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-[color:var(--km-accent)]"
                                                >
                                                    <img
                                                        src="{{ $galleryImage->imageUrl() }}"
                                                        alt="{{ $user->name }}"
                                                        class="h-28 w-full object-cover transition duration-300 group-hover:scale-105 sm:h-36"
                                                        loading="lazy"
                                                    >
                                                </button>
                                            @endforeach
                                        </div>

                                        {{-- LIGHTBOX --}}
                                        <div
                                            x-show="open"
                                            x-cloak
                                            x-transition.opacity
                                            @click="open = false"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85"
                                            style="padding: 3.5rem 1rem 1rem;"
                                        >
                                            <button
                                                type="button"
                                                @click.stop="open = false"
                                                class="absolute right-3 top-3 z-20 flex h-11 w-11 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 focus:outline-none focus:ring-2 focus:ring-white/50"
                                                aria-label="Chiudi"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                                </svg>
                                            </button>

                                            <div class="flex max-h-full max-w-full items-center justify-center" @click.stop>
                                                <template x-for="(url, i) in images" :key="i">
                                                    <img
                                                        x-show="current === i"
                                                        :src="url"
                                                        class="rounded-[1.2rem] object-contain shadow-2xl"
                                                        style="max-height: calc(100vh - 5rem); max-width: min(90vw, 1200px);"
                                                        alt="Gallery"
                                                    >
                                                </template>
                                            </div>

                                            <button
                                                type="button"
                                                @click.stop="prev()"
                                                x-show="images.length > 1"
                                                class="absolute left-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 focus:outline-none"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 010 1.06L8.06 10l3.72 3.72a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                @click.stop="next()"
                                                x-show="images.length > 1"
                                                class="absolute right-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 focus:outline-none"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 010-1.06z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>

                                            <div
                                                x-show="images.length > 1"
                                                class="absolute bottom-3 left-1/2 z-10 flex -translate-x-1/2 gap-1.5"
                                                @click.stop
                                            >
                                                <template x-for="(url, i) in images" :key="i">
                                                    <button
                                                        type="button"
                                                        @click="current = i"
                                                        :class="current === i ? 'bg-white w-4' : 'bg-white/40 w-2'"
                                                        class="h-2 rounded-full transition-all duration-200"
                                                    ></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endif

                        </div>
                    </div>

                    {{-- RECENSIONI DALLA COMMUNITY --}}
                    @if ($reviews->isNotEmpty())
                    @php
                        $reviewsWithRating = $reviews->filter(fn ($r) => $r->rating > 0);
                        $avgRating = $reviewsWithRating->isNotEmpty()
                            ? round($reviewsWithRating->avg('rating'), 1)
                            : null;
                    @endphp
                    <div class="km-panel p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                Recensioni dalla community
                            </h2>
                            @if ($avgRating)
                                <div class="flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5">
                                    {{-- stelle medie --}}
                                    <span class="flex gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= floor($avgRating))
                                                <svg class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @else
                                                <svg class="h-4 w-4 text-stone-200" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endif
                                        @endfor
                                    </span>
                                    <span class="text-sm font-semibold text-amber-700">{{ number_format($avgRating, 1) }}</span>
                                    <span class="text-xs text-stone-400">({{ $reviews->count() }})</span>
                                </div>
                            @else
                                <span class="text-sm text-stone-400">{{ $reviews->count() }} {{ $reviews->count() === 1 ? 'recensione' : 'recensioni' }}</span>
                            @endif
                        </div>

                        <div class="mt-5 space-y-5">
                            @foreach ($reviews as $review)
                            <div class="rounded-2xl border border-stone-100 bg-stone-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        {{-- Avatar iniziali autore --}}
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-stone-200 text-sm font-semibold text-stone-600">
                                            {{ strtoupper(substr($review->author?->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-stone-800">{{ $review->author?->name ?? 'Membro Kommunity' }}</p>
                                            <p class="text-xs text-stone-400">{{ $review->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        @if ($review->is_recommended)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                ✓ Raccomanda
                                            </span>
                                        @endif
                                        @if ($review->rating)
                                            <span class="flex gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $review->rating)
                                                        <svg class="h-3.5 w-3.5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                    @else
                                                        <svg class="h-3.5 w-3.5 text-stone-200" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                    @endif
                                                @endfor
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($review->content)
                                    <p class="mt-3 text-sm leading-relaxed text-stone-700">{{ $review->content }}</p>
                                @endif

                                @if (!empty($review->tags))
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        @foreach ($review->tags as $tag)
                                            <span class="rounded-full bg-white px-2.5 py-0.5 text-xs font-medium text-stone-600 ring-1 ring-stone-200">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ENDORSEMENT PUBBLICI --}}
                    @if (!empty($publicEndorsements) && $publicEndorsements->isNotEmpty())
                    <div class="km-panel p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                Referenze dalla community
                            </h2>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                <svg class="h-3.5 w-3.5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $publicEndorsements->count() }} {{ $publicEndorsements->count() === 1 ? 'referenza' : 'referenze' }} pubbliche
                            </span>
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($publicEndorsements as $endorsement)
                            @php
                                $eStars = match(true) {
                                    in_array($endorsement->priority, ['1','2','3','4','5'], true) => (int) $endorsement->priority,
                                    $endorsement->priority === 'high' => 5,
                                    $endorsement->priority === 'low'  => 1,
                                    default => 3,
                                };
                            @endphp
                            <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-700">
                                        {{ strtoupper(substr($endorsement->sender?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-semibold text-stone-800">{{ $endorsement->sender?->name ?? 'Membro Kommunity' }}</p>
                                            <span class="flex gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <svg class="h-3.5 w-3.5 {{ $i <= $eStars ? 'text-yellow-400' : 'text-stone-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                @endfor
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-stone-700">{{ $endorsement->title }}</p>
                                        @if ($endorsement->description)
                                            <p class="mt-1.5 text-sm leading-relaxed text-stone-600">{{ Str::limit($endorsement->description, 200) }}</p>
                                        @endif
                                        @if ($endorsement->company_name)
                                            <p class="mt-1.5 text-xs text-stone-400">{{ $endorsement->company_name }}</p>
                                        @endif
                                        <p class="mt-1 text-xs text-stone-400">{{ $endorsement->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </section>
            </div>
        </div>
    </div>
</x-app-layout>
