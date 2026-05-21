<x-app-layout>
    <x-slot name="header">
        @if(! optional(auth()->user()->memberProfile)->onboarding_completed)
            <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 text-xl leading-none">🔒</span>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-amber-800">Completa il profilo per sbloccare la piattaforma</p>
                        <p class="mt-1 text-sm text-amber-700">
                            Finché non salvi questa pagina con i campi essenziali compilati, non potrai accedere alle altre sezioni (directory, eventi, forum, messaggi).
                            Compila i campi qui sotto e clicca <strong>Salva</strong>.
                        </p>
                        @if(count($profileCompletion['missing']) > 0)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($profileCompletion['missing'] as $item)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800 border border-amber-200">
                                        {{ $item['icon'] }} {{ $item['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="km-panel p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Onboarding membro</p>
                    <h1 class="mt-3 font-serif text-2xl font-semibold text-stone-950 sm:text-3xl lg:text-4xl">Completa il profilo business e le preferenze di networking</h1>
                    <p class="mt-3 text-sm leading-7 text-stone-600">
                        Questa area alimenta directory interna, pagina personale, contatti rapidi, richieste one-to-one e visibilità nella kommunity.
                    </p>
                </div>

                {{-- Widget completamento profilo --}}
                <div class="shrink-0 rounded-2xl border border-stone-200 bg-stone-50 px-5 py-4 sm:min-w-[200px]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-stone-400">Completamento</p>
                            <p class="mt-0.5 text-2xl font-black text-stone-900">{{ $profileCompletion['percentage'] }}%</p>
                            <p class="text-xs text-stone-400">{{ $profileCompletion['done'] }}/{{ $profileCompletion['total'] }} campi</p>
                        </div>
                        <div class="relative flex h-14 w-14 shrink-0 items-center justify-center">
                            <svg class="h-14 w-14 -rotate-90" viewBox="0 0 48 48">
                                <circle cx="24" cy="24" r="20" fill="none" stroke="#e7e5e4" stroke-width="4"/>
                                <circle cx="24" cy="24" r="20" fill="none"
                                        stroke="{{ $profileCompletion['percentage'] === 100 ? '#10b981' : '#537d4d' }}"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ round(2 * M_PI * 20, 2) }}"
                                        stroke-dashoffset="{{ round(2 * M_PI * 20 * (1 - $profileCompletion['percentage'] / 100), 2) }}"/>
                            </svg>
                            <span class="absolute text-[0.6rem] font-black text-stone-700">{{ $profileCompletion['percentage'] }}%</span>
                        </div>
                    </div>

                    @if(count($profileCompletion['missing']) > 0)
                        <div class="mt-3 border-t border-stone-200 pt-3 space-y-1">
                            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.15em] text-stone-400">Da completare</p>
                            @foreach(array_slice($profileCompletion['missing'], 0, 3) as $item)
                                <div class="flex items-center gap-1.5 text-[0.74rem] text-stone-500">
                                    <span class="text-sm leading-none">{{ $item['icon'] }}</span>
                                    <span>{{ $item['label'] }}</span>
                                </div>
                            @endforeach
                            @if(count($profileCompletion['missing']) > 3)
                                <p class="text-[0.65rem] text-stone-400">+ altri {{ count($profileCompletion['missing']) - 3 }}</p>
                            @endif
                        </div>
                    @else
                        <p class="mt-3 border-t border-stone-200 pt-3 text-xs font-semibold text-emerald-600">
                            ✓ Profilo completo!
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell space-y-6">
            <div class="km-panel p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- ── Biglietto da visita digitale ────────────────────────────── --}}
            @php
                $cardOnepage   = auth()->user()->memberOnepage;
                $cardPublicUrl = $cardOnepage ? route('card.show', $cardOnepage->slug) : null;
                $cardQrPreview = $cardPublicUrl
                    ? 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($cardPublicUrl) . '&size=120x120&margin=4&color=263d2a&bgcolor=f6faf8'
                    : null;
            @endphp
            @if($cardPublicUrl)
            <div class="km-panel p-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-400">Biglietto da visita digitale</p>
                        <h2 class="mt-1.5 font-serif text-lg font-semibold text-stone-900">La tua card pubblica</h2>
                        <p class="mt-1 text-sm leading-relaxed text-stone-500">
                            Condividi questo link — o fai scansionare il QR — per condividere i tuoi contatti senza nessuna app.
                        </p>

                        {{-- URL copiabile --}}
                        <div class="mt-4 flex items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 px-3 py-2">
                            <span class="min-w-0 flex-1 truncate font-mono text-xs text-stone-600" id="card-url-text">{{ $cardPublicUrl }}</span>
                            <button
                                type="button"
                                onclick="
                                    navigator.clipboard.writeText('{{ $cardPublicUrl }}').then(function(){
                                        var el = document.getElementById('card-copy-label');
                                        el.textContent = 'Copiato!';
                                        setTimeout(function(){ el.textContent = 'Copia'; }, 2000);
                                    });
                                "
                                class="shrink-0 rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
                            >
                                <span id="card-copy-label">Copia</span>
                            </button>
                        </div>

                        {{-- Bottoni azione --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ $cardPublicUrl }}"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-[#55794f] px-4 py-2 text-xs font-semibold text-white hover:bg-[#426240] transition">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Apri la card
                            </a>
                            <a href="https://api.qrserver.com/v1/create-qr-code/?data={{ urlencode($cardPublicUrl) }}&size=600x600&margin=16&color=263d2a&bgcolor=ffffff"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-stone-300 bg-white px-4 py-2 text-xs font-semibold text-stone-700 hover:bg-stone-50 transition">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Scarica QR
                            </a>
                        </div>
                    </div>

                    {{-- Anteprima QR --}}
                    <div class="shrink-0 self-center sm:self-auto">
                        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white p-2 shadow-sm">
                            <img src="{{ $cardQrPreview }}"
                                 alt="QR code della tua card"
                                 width="80" height="80"
                                 class="block h-20 w-20 rounded-lg">
                        </div>
                        <p class="mt-1.5 text-center text-[0.65rem] text-stone-400">Scansiona per aprire</p>
                    </div>

                </div>
            </div>
            @endif

            {{-- ── Pianeti di cui faccio parte ─────────────────────────────── --}}
            @if($userPlanets->isNotEmpty())
            <div class="km-panel p-6">
                <h2 class="font-serif text-lg font-semibold text-stone-900">I miei Pianeti</h2>
                <p class="mt-1 text-sm text-stone-500">Comunità a cui appartieni all'interno di Kommunity.</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($userPlanets as $planet)
                    <div class="relative flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">

                        {{-- Copertina --}}
                        @if($planet->cover_image)
                            <div class="h-28 w-full overflow-hidden bg-stone-100">
                                <img src="{{ Storage::url($planet->cover_image) }}"
                                     alt="{{ $planet->name }}"
                                     class="h-full w-full object-cover">
                            </div>
                        @else
                            <div class="flex h-28 w-full items-center justify-center bg-gradient-to-br from-[#537d4d]/20 to-[#537d4d]/5">
                                <span class="text-4xl">🪐</span>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col gap-2 p-4">
                            {{-- Nome + stato attivo --}}
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-stone-900 leading-snug">{{ $planet->name }}</h3>
                                @if(optional(auth()->user()->memberProfile)->active_chapter_id === $planet->id)
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-emerald-700">Attivo</span>
                                @endif
                            </div>

                            {{-- Descrizione --}}
                            @if($planet->description)
                                <p class="text-xs leading-relaxed text-stone-500 line-clamp-2">{{ $planet->description }}</p>
                            @endif

                            {{-- Leader --}}
                            @if($planet->leaders->isNotEmpty())
                                <div class="mt-auto pt-2 border-t border-stone-100">
                                    <p class="text-[0.7rem] font-semibold uppercase tracking-wide text-stone-400">
                                        {{ $planet->leaders->count() === 1 ? 'Leader' : 'Leader' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-stone-600">
                                        {{ $planet->leaders->pluck('name')->join(', ') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
