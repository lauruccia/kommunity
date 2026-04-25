<x-app-layout>
    @php
        $viewerIsOwner = auth()->check() && auth()->id() === $user->id;
        $whatsappUrl   = null;
        if ($profile->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number) {
            $whatsappUrl = 'https://wa.me/'.preg_replace('/\D+/', '', $profile->whatsapp_number)
                         .'?text='.urlencode('Ciao '.$user->name.', ti contatto dalla tua pagina su Kommunity.');
        }
    @endphp

    {{-- Alpine: gestisce il drawer mobile della sidebar --}}
    <div x-data="{ drawer: false }" class="pb-12 pt-6">

        {{-- ══════════════════════════════════════════
             MOBILE: barra superiore con trigger drawer
             Visibile solo < lg
        ══════════════════════════════════════════ --}}
        <div class="mb-4 flex items-center justify-between px-4 sm:px-6 lg:hidden">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-stone-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-stone-500">{{ $profile->company_name ?: 'Attività professionale' }}</p>
            </div>
            <button
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

        {{-- ══════════════════════════════════════════
             MOBILE DRAWER — overlay scuro
        ══════════════════════════════════════════ --}}
        <div
            x-show="drawer"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="drawer = false"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            style="backdrop-filter:blur(2px);"
        ></div>

        {{-- ══════════════════════════════════════════
             MOBILE DRAWER — pannello scorrevole da sinistra
        ══════════════════════════════════════════ --}}
        <div
            x-show="drawer"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[88vw] flex-col bg-stone-50 shadow-2xl lg:hidden"
            style="border-right:1px solid #e7e5e4;"
            @click.stop
        >
            {{-- Intestazione drawer --}}
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
                    @click="drawer = false"
                    class="ms-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-stone-500 transition hover:bg-stone-100 hover:text-stone-700"
                    aria-label="Chiudi"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </button>
            </div>

            {{-- Contenuto drawer (scrollabile) --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @include('members._sidebar')
            </div>

            {{-- CTA in fondo al drawer (solo per visitatori) --}}
            @unless($viewerIsOwner)
            <div class="shrink-0 border-t border-stone-200 bg-white p-4 space-y-2">
                <form method="POST" action="{{ route('conversations.start') }}">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                    <button type="submit" class="km-button-secondary w-full">Messaggio diretto</button>
                </form>
                <form method="POST" action="{{ route('one-to-ones.store') }}">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                    <input type="hidden" name="meeting_mode" value="online">
                    <input type="hidden" name="goal" value="Vorrei approfondire il tuo profilo e valutare una collaborazione.">
                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-full border border-transparent bg-[color:var(--km-accent)] px-5 py-3 text-sm font-semibold text-white shadow-[0_8px_24px_rgba(66,98,64,0.25)] transition hover:bg-[color:var(--km-accent-strong)]">
                        Prenota one-to-one
                    </button>
                </form>
            </div>
            @endunless
        </div>

        {{-- ══════════════════════════════════════════
             LAYOUT PRINCIPALE: sidebar + corpo
        ══════════════════════════════════════════ --}}
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">

                {{-- SIDEBAR — nascosta su mobile, visibile da lg --}}
                <aside class="hidden space-y-6 lg:block">
                    @include('members._sidebar')
                </aside>

                {{-- CORPO PAGINA --}}
                <section class="space-y-6">

                    {{-- Card principale: banner + avatar a cavallo + info membro --}}
                    <div class="km-panel overflow-hidden p-0">

                        {{-- Banner / Cover image --}}
                        <div
                            class="relative h-[200px] bg-[linear-gradient(135deg,#425767_0%,#d7e3d1_100%)] sm:h-[260px] lg:h-[320px]"
                            @if($onepage->coverImageUrl())
                                style="background-image:url('{{ $onepage->coverImageUrl() }}'); background-size:cover; background-position:center;"
                            @endif
                        >
                            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(22,31,41,0.04),rgba(22,31,41,0.28))]"></div>
                        </div>

                        {{-- ── Avatar a cavallo ──
                             Il div ha altezza zero; il cerchio h-24 (96px) è posizionato
                             -top-12 (-48px) → metà nel banner, metà nel corpo bianco.
                        --}}
                        <div class="relative">
                            <div class="absolute -top-12 left-5 z-10 sm:left-6">
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

                        {{-- Nome, ruolo, CTA
                             pt-16 (64px) = 48px avatar sporgente + 16px respiro
                        --}}
                        <div class="flex flex-col gap-4 px-5 pb-6 pt-16 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <h1 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                                    {{ $onepage->hero_title ?: $user->name }}
                                </h1>
                                <p class="mt-0.5 text-sm text-stone-500">
                                    {{ $onepage->hero_subtitle ?: ($profile->company_name ?: 'Attività professionale') }}
                                </p>
                                @php
                                    $profsLabel = $profile->professions->isNotEmpty()
                                        ? $profile->professions->pluck('name')->join(', ')
                                        : ($profile->profession?->name ?? null);
                                @endphp
                                @if ($profsLabel)
                                    <p class="mt-1 text-sm text-stone-600">{{ $profsLabel }}</p>
                                @endif
                            </div>

                            {{-- Pulsanti CTA — solo per visitatori, nascosti su mobile (ci sono nel drawer) --}}
                            @unless($viewerIsOwner)
                                <div class="hidden flex-wrap gap-3 sm:flex">
                                    <form method="POST" action="{{ route('conversations.start') }}">
                                        @csrf
                                        <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                                        <button type="submit" class="km-button-secondary">Messaggio diretto</button>
                                    </form>
                                    <form method="POST" action="{{ route('one-to-ones.store') }}">
                                        @csrf
                                        <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                                        <input type="hidden" name="meeting_mode" value="online">
                                        <input type="hidden" name="goal" value="Vorrei approfondire il tuo profilo e valutare una collaborazione.">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-full border border-transparent bg-[color:var(--km-accent)] px-5 py-3 text-sm font-semibold text-white shadow-[0_16px_36px_rgba(66,98,64,0.28)] transition hover:bg-[color:var(--km-accent-strong)]">
                                            Prenota one-to-one
                                        </button>
                                    </form>
                                </div>
                            @endunless
                        </div>
                    </div>

                    {{-- Contenuto: Chi sono, Servizi, Networking, Gallery --}}
                    <div class="km-panel p-5 sm:p-6">
                        <div class="grid gap-8">

                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">Chi sono</h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">
                                    {{ $onepage->about_text ?: ($profile->bio ?: 'Profilo professionale in fase di completamento.') }}
                                </p>
                            </div>

                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">Servizi e competenze</h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">
                                    {{ $onepage->services_text ?: ($profile->services ?: 'Questa sezione raccoglierà servizi e competenze professionali del membro.') }}
                                </p>
                                @if ($profile->skills)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach (collect(explode(',', $profile->skills))->map(fn ($item) => trim($item))->filter() as $skill)
                                            <span class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-700">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">Obiettivi di networking</h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">
                                    {{ $profile->networking_goals ?: 'Disponibile a creare sinergie, referral qualificati e nuove collaborazioni nella kommunity.' }}
                                </p>
                            </div>

                            <div>
                                <h2 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">Gallery</h2>
                                @if ($user->memberGalleryImages->isNotEmpty())
                                    @php $galleryUrls = $user->memberGalleryImages->map(fn($i) => $i->imageUrl())->values()->all(); @endphp
                                    <div
                                        x-data="{
                                            open: false,
                                            current: 0,
                                            images: @js($galleryUrls),
                                            prev() { this.current = (this.current - 1 + this.images.length) % this.images.length; },
                                            next() { this.current = (this.current + 1) % this.images.length; }
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

                                        {{-- Lightbox --}}
                                        <div
                                            x-show="open"
                                            x-cloak
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            @click="open = false"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85"
                                            style="padding: 3.5rem 1rem 1rem;"
                                        >
                                            <button @click.stop="open = false"
                                                class="absolute top-3 right-3 z-20 flex h-11 w-11 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 focus:outline-none focus:ring-2 focus:ring-white/50"
                                                aria-label="Chiudi">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                                            </button>

                                            <div class="flex max-h-full max-w-full items-center justify-center" @click.stop>
                                                <template x-for="(url, i) in images" :key="i">
                                                    <img
                                                        x-show="current === i"
                                                        :src="url"
                                                        x-transition:enter="transition ease-out duration-150"
                                                        x-transition:enter-start="opacity-0 scale-95"
                                                        x-transition:enter-end="opacity-100 scale-100"
                                                        class="rounded-[1.2rem] object-contain shadow-2xl"
                                                        style="max-height: calc(100vh - 5rem); max-width: min(90vw, 1200px);"
                                                        alt="Gallery"
                                                    >
                                                </template>
                                            </div>

                                            <button @click.stop="prev()" x-show="images.length > 1"
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 focus:outline-none">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 010 1.06L8.06 10l3.72 3.72a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                                            </button>

                                            <button @click.stop="next()" x-show="images.length > 1"
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/40 focus:outline-none">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                                            </button>

                                            <div x-show="images.length > 1"
                                                 class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 flex gap-1.5"
                                                 @click.stop>
                                                <template x-for="(url, i) in images" :key="i">
                                                    <button @click="current = i"
                                                            :class="current === i ? 'bg-white w-4' : 'bg-white/40 w-2'"
                                                            class="h-2 rounded-full transition-all duration-200">
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">
                                        La gallery verrà popolata dal membro con immagini dei propri progetti e attività.
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>

                </section>{{-- fine corpo --}}

            </div>
        </div>
    </div>{{-- fine x-data drawer --}}

    @push('modals')
    @endpush
</x-app-layout>
