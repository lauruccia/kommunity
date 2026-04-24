<x-app-layout>
    <x-slot name="header">
        <div class="w-full rounded-[2rem] bg-[linear-gradient(135deg,#425767_0%,#4d6474_52%,#5b7d4b_100%)] p-6 text-white shadow-[0_22px_60px_rgba(66,87,103,0.22)]">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/70">Directory membri</p>
                    <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl">Kommunity business interna</h1>
                    <p class="mt-3 max-w-4xl text-sm leading-7 text-white/80">
                        Esplora professionisti, networker e imprenditori con una struttura piu' vicina a un business hub: filtri laterali, ricerca veloce e card ricche di contatti.
                    </p>
                </div>
                <div class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur">
                    {{ $members->total() }} membri attivi
                </div>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
             class="mx-2 mb-4 mt-2 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-800 shadow-sm sm:mx-3 lg:mx-4">
            <svg class="h-5 w-5 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
            <button @click="show = false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
            </button>
        </div>
    @endif

    <div class="pb-12">
        <div class="w-full px-2 sm:px-3 lg:px-4" x-data="{ filtersOpen: window.innerWidth >= 1024 }">

            {{-- Pulsante Filtra visibile solo su mobile --}}
            <div class="mb-4 lg:hidden">
                <button @click="filtersOpen = !filtersOpen"
                        class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-5 py-2.5 text-sm font-medium text-stone-700 shadow-sm transition hover:bg-stone-50">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="filtersOpen ? 'Nascondi filtri' : 'Filtra membri'">Filtra membri</span>
                </button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start"
                       x-show="filtersOpen"
                       x-cloak
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0 -translate-y-2"
                       x-transition:enter-end="opacity-100 translate-y-0"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100 translate-y-0"
                       x-transition:leave-end="opacity-0 -translate-y-2">
                    {{-- CATEGORIE AD ALBERO --}}
                    <div class="km-panel overflow-hidden p-0 shadow-[0_14px_34px_rgba(66,87,103,0.08)]">
                        <div class="border-b border-stone-200 bg-[color:var(--km-soft)] px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-stone-950">Categorie</h2>
                                @if($filters['category'] ?? null)
                                    <a href="{{ route('directory.index', array_filter(array_merge($filters, ['category' => null]))) }}" class="text-xs text-stone-400 hover:text-stone-700">Azzera</a>
                                @endif
                            </div>
                        </div>
                        <div class="divide-y divide-stone-100">
                            @foreach ($rootCategories as $root)
                                <div x-data="{ open: {{ $root->activeChildren->isNotEmpty() && (($filters['category'] ?? null) == $root->id || $root->activeChildren->contains('id', (int)($filters['category'] ?? 0))) ? 'true' : 'false' }} }">
                                    <div class="flex items-center">
                                        <a href="{{ route('directory.index', array_filter(array_merge($filters, ['category' => $root->id]))) }}"
                                           class="flex-1 px-6 py-3 text-[14px] font-medium transition hover:bg-stone-50 {{ ($filters['category'] ?? null) == $root->id ? 'text-emerald-700' : 'text-stone-700' }}">
                                            {{ $root->name }}
                                        </a>
                                        @if($root->activeChildren->isNotEmpty())
                                            <button @click="open = !open" class="px-4 py-3 text-stone-400 hover:text-stone-700">
                                                <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                    @if($root->activeChildren->isNotEmpty())
                                        <div x-show="open" x-cloak class="border-t border-stone-100 bg-stone-50/60">
                                            @foreach($root->activeChildren as $child)
                                                <a href="{{ route('directory.index', array_filter(array_merge($filters, ['category' => $child->id]))) }}"
                                                   class="flex items-center gap-2 py-2 pl-10 pr-6 text-[13px] transition hover:bg-stone-100 {{ ($filters['category'] ?? null) == $child->id ? 'font-medium text-emerald-700' : 'text-stone-600' }}">
                                                    <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                                                    {{ $child->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- FILTRI: Località + Pianeta --}}
                    @php
                        $provinceOptions = $provinces
                            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'region_id' => $p->region_id])
                            ->values();
                        $cityOptions = $regions
                            ->flatMap(fn ($r) => $r->cities)
                            ->map(fn ($c) => [
                                'id' => $c->id,
                                'name' => $c->name,
                                'province_id' => $c->province_id ?? null,
                                'region_id' => $c->region_id,
                            ])
                            ->values();
                    @endphp
                    <div class="km-panel p-6 shadow-[0_14px_34px_rgba(66,87,103,0.08)]"
                         x-data="{
                            regionId: '{{ $filters['region'] ?? '' }}',
                            provinceId: '{{ $filters['province'] ?? '' }}',
                            provinces: @js($provinceOptions),
                            cities: @js($cityOptions),
                            get filteredProvinces() { return this.regionId ? this.provinces.filter(p => p.region_id == this.regionId) : this.provinces; },
                            get filteredCities() {
                                if (this.provinceId) return this.cities.filter(c => c.province_id == this.provinceId);
                                if (this.regionId) return this.cities.filter(c => c.region_id == this.regionId);
                                return this.cities;
                            }
                         }">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Filtri rapidi</h3>

                        <form method="GET" class="mt-4 space-y-3">
                            {{-- Mantieni categoria e ricerca corrente --}}
                            @if($filters['category'] ?? null)
                                <input type="hidden" name="category" value="{{ $filters['category'] }}">
                            @endif
                            @if($filters['search'] ?? null)
                                <input type="hidden" name="search" value="{{ $filters['search'] }}">
                            @endif

                            {{-- Regione --}}
                            <div>
                                <label class="mb-1 block text-xs font-medium text-stone-500">Regione</label>
                                <select name="region" class="km-input"
                                        x-model="regionId" @change="provinceId=''">
                                    <option value="">Tutte le regioni</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" @selected(($filters['region'] ?? null) == $region->id)>
                                            {{ $region->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Provincia (filtrata per regione) --}}
                            <div x-show="filteredProvinces.length > 0">
                                <label class="mb-1 block text-xs font-medium text-stone-500">Provincia</label>
                                <select name="province" class="km-input" x-model="provinceId">
                                    <option value="">Tutte le province</option>
                                    <template x-for="p in filteredProvinces" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="provinceId == p.id"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Città (filtrata per provincia o regione) --}}
                            <div x-show="filteredCities.length > 0">
                                <label class="mb-1 block text-xs font-medium text-stone-500">Città</label>
                                <select name="city" class="km-input">
                                    <option value="">Tutte le città</option>
                                    <template x-for="c in filteredCities" :key="c.id">
                                        <option :value="c.id" x-text="c.name" :selected="c.id == {{ $filters['city'] ?? 0 }}"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Pianeta --}}
                            <div>
                                <label class="mb-1 block text-xs font-medium text-stone-500">Pianeta</label>
                                <select name="chapter" class="km-input">
                                    <option value="">Tutti i Pianeti</option>
                                    @foreach ($chapters as $chapter)
                                        <option value="{{ $chapter->id }}" @selected(($filters['chapter'] ?? null) == $chapter->id)>
                                            {{ $chapter->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="km-button-primary w-full">Applica filtri</button>
                            <a href="{{ route('directory.index') }}" class="block text-center text-xs text-stone-400 hover:text-stone-700">Azzera tutti i filtri</a>
                        </form>
                    </div>
                </aside>

                <section class="min-w-0 space-y-4">
                    <form method="GET" class="flex gap-3">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            class="km-input flex-1 bg-white shadow-sm"
                            placeholder="Nome, professione, categoria…"
                        >
                        <button type="submit" class="km-button-primary px-6">Cerca</button>
                        @if(array_filter($filters))
                            <a href="{{ route('directory.index') }}" class="km-button-secondary px-5">×</a>
                        @endif

                        {{-- Mantieni filtri sidebar nella ricerca testuale --}}
                        @foreach(['category','region','province','city','chapter'] as $fk)
                            @if($filters[$fk] ?? null)
                                <input type="hidden" name="{{ $fk }}" value="{{ $filters[$fk] }}">
                            @endif
                        @endforeach
                    </form>

                    {{-- ===== POPUP VIDEO (globale, Alpine condiviso via $dispatch) ===== --}}
                    <div
                        x-data="{ open: false, embedUrl: '', localUrl: '' }"
                        @open-video.window="open = true; embedUrl = $event.detail.embed; localUrl = $event.detail.local"
                        @keydown.escape.window="open = false"
                    >
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                            @click.self="open = false"
                        >
                            <div class="relative w-full max-w-3xl rounded-[1.6rem] bg-black shadow-2xl overflow-hidden" style="aspect-ratio:16/9;">
                                <template x-if="embedUrl">
                                    <iframe :src="embedUrl" class="h-full w-full" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                                </template>
                                <template x-if="!embedUrl && localUrl">
                                    <video :src="localUrl" controls autoplay class="h-full w-full"></video>
                                </template>
                                <button @click="open = false" class="absolute top-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 items-stretch">
                        @forelse ($members as $member)
                            @php
                                $onepage    = $member->user->memberOnepage ?? null;
                                $photoUrl   = $member->avatarUrl() ?: $member->logoUrl();
                                $mediaFit   = $member->avatarUrl() ? 'cover' : 'contain';
                                $profession = $member->professions->first()?->name ?? $member->profession?->name ?? $member->profession_other ?? null;

                                $whatsappUrl = ($member->show_whatsapp && $member->allow_whatsapp_contact && $member->whatsapp_number)
                                    ? 'https://wa.me/'.preg_replace('/\D+/', '', $member->whatsapp_number).'?text='.urlencode('Ciao '.$member->user->name.', ti contatto dalla directory di Kommunity.')
                                    : null;

                                $profileUrl  = $onepage?->slug ? route('members.show', $onepage->slug) : '#';
                                $embedUrl    = $member->videoEmbedUrl() ?? '';
                                $localUrl    = (!$embedUrl && $member->introVideoUrl()) ? $member->introVideoUrl() : '';
                                $hasVideo    = $member->hasVideo();
                                $sectorLabel = $member->categories->first()?->name ?? $member->category?->name ?? null;
                                $sectorInit  = $sectorLabel ? strtoupper(mb_substr($sectorLabel, 0, 1)) : null;

                                // Estrae thumbnail YouTube dall'embed URL
                                $ytId = null;
                                if ($embedUrl && preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $embedUrl, $ytMatch)) {
                                    $ytId = $ytMatch[1];
                                }
                                // Tipo di contenuto del cerchio: 'yt' | 'local' | 'vimeo' | 'photo'
                                $circleType = 'photo';
                                if ($hasVideo) {
                                    if ($ytId)         $circleType = 'yt';
                                    elseif ($localUrl) $circleType = 'local';
                                    else               $circleType = 'vimeo';
                                }

                                // ── SVG icon per categoria (Heroicons 24px fill) ──────────
                                $catLower = strtolower($sectorLabel ?? '');
                                $sectorIconPaths = match(true) {
                                    // Marketing, comunicazione, PR, media, brand
                                    (bool)preg_match('/market|comuni(?!t)|pubblici|brand|media|pr\b|adverti|comunica/', $catLower) =>
                                        '<path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 001.5 12c0 .898.121 1.768.35 2.595.341 1.241 1.519 1.905 2.66 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06zM18.584 5.106a.75.75 0 011.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 11-1.06-1.06 8.25 8.25 0 000-11.668.75.75 0 010-1.06z"/><path d="M15.932 7.757a.75.75 0 011.061 0 6 6 0 010 8.486.75.75 0 01-1.06-1.061 4.5 4.5 0 000-6.364.75.75 0 010-1.06z"/>',

                                    // Tecnologia, IT, software, web, digitale, informatica
                                    (bool)preg_match('/tecn|informat|software|web\b|digit|app\b|svilup|program|cloud|cyber/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M2.25 5.25a3 3 0 013-3h13.5a3 3 0 013 3V15a3 3 0 01-3 3h-3v.257c0 .597.237 1.17.659 1.591l.621.622a.75.75 0 01-.53 1.28h-9a.75.75 0 01-.53-1.28l.621-.622a2.25 2.25 0 00.659-1.591V18h-3a3 3 0 01-3-3V5.25zm1.5 0v7.5c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75v-7.5a.75.75 0 00-.75-.75H4.5a.75.75 0 00-.75.75z" clip-rule="evenodd"/>',

                                    // Legale, diritto, avvocato, notaio
                                    (bool)preg_match('/legal|legge|diritto|avvocat|notaio|giuridic|studio legale/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M12 1.5a.75.75 0 01.75.75V4.5a.75.75 0 01-1.5 0V2.25A.75.75 0 0112 1.5zM5.636 4.136a.75.75 0 011.06 0l1.592 1.591a.75.75 0 01-1.061 1.06l-1.591-1.59a.75.75 0 010-1.061zm12.728 0a.75.75 0 010 1.06l-1.591 1.592a.75.75 0 01-1.06-1.061l1.59-1.591a.75.75 0 011.061 0zm-6.816 4.496a.75.75 0 01.82.311l5.228 7.917a.75.75 0 01-.777 1.148l-2.097-.43 1.045 3.9a.75.75 0 01-1.45.388l-1.044-3.899-1.601 1.42a.75.75 0 01-1.247-.606l.569-9.47a.75.75 0 01.554-.68zM3 10.5a.75.75 0 01.75-.75H6a.75.75 0 010 1.5H3.75A.75.75 0 013 10.5zm14.25 0a.75.75 0 01.75-.75h2.25a.75.75 0 010 1.5H18a.75.75 0 01-.75-.75zm-8.962 3.712a.75.75 0 010 1.061l-1.591 1.591a.75.75 0 11-1.061-1.06l1.591-1.592a.75.75 0 011.061 0z" clip-rule="evenodd"/>',

                                    // Finanza, assicurazioni, banca, credito, investimenti, contabilità
                                    (bool)preg_match('/finanz|assicur|banca|bank|invest|credito|contabil|fiscal|tributar/', $catLower) =>
                                        '<path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd"/><path d="M2.25 18a.75.75 0 000 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 00-.75-.75H2.25z"/>',

                                    // Immobiliare, costruzioni, edilizia, architettura
                                    (bool)preg_match('/immobil|costruz|ediliz|architet|ingegner|residenz|cantiere/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M4.5 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5h-.75V3.75a.75.75 0 000-1.5h-15zm4.875 3.375a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 3.375a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 3.375a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75-6.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 3.375a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 3.375a.375.375 0 11-.75 0 .375.375 0 01.75 0zM9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21H9z" clip-rule="evenodd"/>',

                                    // Salute, medicina, benessere, farmaceutica, psicologia
                                    (bool)preg_match('/salut|medic|benessere|farmac|psicolog|terapia|sanitari|clinica/', $catLower) =>
                                        '<path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>',

                                    // Formazione, coaching, training, education
                                    (bool)preg_match('/formazion|coach|training|educaz|insegn|corsi|scuol|accadem/', $catLower) =>
                                        '<path d="M11.7 2.805a.75.75 0 01.6 0A60.65 60.65 0 0122.83 8.72a.75.75 0 01-.231 1.337 49.949 49.949 0 00-9.902 3.912l-.003.002-.34.18a.75.75 0 01-.707 0A50.009 50.009 0 007.5 12.174v-.224c0-.131.067-.248.172-.311a54.614 54.614 0 014.653-2.52.75.75 0 00-.65-1.352 56.129 56.129 0 00-4.78 2.589 1.858 1.858 0 00-.859 1.228 49.803 49.803 0 00-4.634-1.527.75.75 0 01-.231-1.337A60.653 60.653 0 0111.7 2.805z"/><path d="M13.06 15.473a48.45 48.45 0 017.666-3.282c.134 1.414.22 2.843.255 4.285a.75.75 0 01-.46.71 47.878 47.878 0 00-8.105 4.342.75.75 0 01-.832 0 47.877 47.877 0 00-8.104-4.342.75.75 0 01-.461-.71c.035-1.442.121-2.87.255-4.286A48.4 48.4 0 016 13.18v1.27a1.5 1.5 0 00-.14 2.508c-.09.38-.222.753-.397 1.11.452.213.901.434 1.346.661a6.729 6.729 0 00.551-1.608 1.5 1.5 0 00.14-2.67v-.645a48.549 48.549 0 013.44 1.668 2.25 2.25 0 002.12 0z"/>',

                                    // Ristorazione, food, cucina, bar, gastronomia
                                    (bool)preg_match('/ristoran|food|cucina|chef|bar\b|alimentar|gastrono|beverage/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M15.75 2.25a.75.75 0 01.75.75c0 8.284-6.716 15-15 15a.75.75 0 010-1.5A13.5 13.5 0 0015 3a.75.75 0 01.75-.75zm.75 12a.75.75 0 01.75-.75c1.243 0 2.438-.175 3.567-.501a.75.75 0 01.433 1.437A15.04 15.04 0 0117.25 15a.75.75 0 01-.75-.75zm-5.25-3.375a.75.75 0 011.5 0V15a.75.75 0 01-1.5 0v-4.125zM3.75 12a.75.75 0 01.75-.75H8.25a.75.75 0 010 1.5H4.5a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>',

                                    // Moda, abbigliamento, fashion, retail, lusso, beauty
                                    (bool)preg_match('/moda|fashion|abbiglia|retail|lusso|beauty|estetica|tessile/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z" clip-rule="evenodd"/>',

                                    // Arte, design, fotografia, grafica, creatività
                                    (bool)preg_match('/arte|design|fotograf|grafica|creativi|studio|agenz(?!ia immo)/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M20.599 1.5c-.376 0-.743.111-1.055.32l-5.08 3.385a18.747 18.747 0 00-3.471 2.987 10.04 10.04 0 014.815 4.815 18.748 18.748 0 002.987-3.472l3.386-5.079A1.902 1.902 0 0020.599 1.5zm-8.3 14.025a18.76 18.76 0 001.896-1.207 8.026 8.026 0 00-4.513-4.513A18.75 18.75 0 008.475 11.7l-.278.5a5.26 5.26 0 013.601 3.602l.502-.278zM6.75 13.5A3.75 3.75 0 003 17.25a1.5 1.5 0 01-1.601 1.497.75.75 0 00-.7 1.123 5.25 5.25 0 009.8-2.62 3.75 3.75 0 00-3.75-3.75z" clip-rule="evenodd"/>',

                                    // Trasporti, logistica, spedizioni, distribuzione
                                    (bool)preg_match('/trasport|logistic|spedizion|distribuz|corriere|autotraspor/', $catLower) =>
                                        '<path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25zM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h.375a3 3 0 116 0h3a3 3 0 116 0h.375c1.035 0 1.875-.84 1.875-1.875v-1.5c0-1.036-.84-1.875-1.875-1.875H15a1.5 1.5 0 01-1.5-1.5V15z"/><path d="M8.25 19.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0zM15.75 6.75a.75.75 0 00-.75.75v11.25c0 .087.015.17.042.248a3 3 0 015.958.464c.853-.175 1.522-.935 1.464-1.883a18.845 18.845 0 00-3.732-10.104 1.837 1.837 0 00-1.47-.725H15.75z"/><path d="M19.5 19.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z"/>',

                                    // Energia, ambiente, sostenibilità, green
                                    (bool)preg_match('/energia|ambient|sostenib|green|rinnovab|ecolog|solare/', $catLower) =>
                                        '<path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM6.262 6.072a8.25 8.25 0 1010.562-.766 4.5 4.5 0 01-1.318 1.357L14.25 7.5l.165.33a.809.809 0 01-1.086 1.085l-.604-.302a1.125 1.125 0 00-1.298.21l-.132.131c-.439.44-.439 1.152 0 1.591l.296.296c.256.257.622.374.98.314l1.17-.195c.323-.054.654.036.905.245l1.33 1.108c.32.267.46.694.358 1.1a8.7 8.7 0 01-2.288 4.04l-.723.724a1.125 1.125 0 01-1.298.21l-.153-.076a1.125 1.125 0 01-.622-1.006v-1.089c0-.298-.119-.585-.33-.796l-1.347-1.347a1.125 1.125 0 01-.21-1.298L9.75 12l-1.64-1.228a1.125 1.125 0 01-.483-.906v-.581c0-.432.218-.835.582-1.076l.089-.05a1.125 1.125 0 000-1.461l-1.036-1.626z" clip-rule="evenodd"/>',

                                    // HR, risorse umane, selezione, recruitment
                                    (bool)preg_match('/risorse.umane|hr\b|personale|recruitment|selezione|head.?hunt/', $catLower) =>
                                        '<path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/>',

                                    // Default: valigetta consulenza/business
                                    default =>
                                        '<path fill-rule="evenodd" d="M7.5 5.25a3 3 0 013-3h1a3 3 0 013 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0112 15.75c-2.73 0-5.357-.442-7.814-1.259C2.984 14.093 2.25 12.952 2.25 11.741v-3.033c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 017.5 5.455V5.25zm7.5 0v.09a49.488 49.488 0 00-6 0v-.09a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5zm-3 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/><path d="M3 14.25a.75.75 0 01.75-.75h16.5a.75.75 0 010 1.5H3.75A.75.75 0 013 14.25z"/>',
                                };
                            @endphp

                            <article class="km-directory-card group transition duration-300 hover:-translate-y-1">
                                <div class="flex flex-1 flex-col px-4 pb-3 pt-5">
                                    <div class="flex flex-col items-center text-center">

                                        {{-- Avatar / cerchio video --}}
                                        <div class="relative">
                                            @if ($hasVideo)
                                                <button
                                                    type="button"
                                                    @click="window.dispatchEvent(new CustomEvent('open-video', { detail: { embed: @js($embedUrl), local: @js($localUrl) } }))"
                                                    class="relative inline-flex cursor-pointer focus:outline-none group/play"
                                                    title="Guarda la videopresentazione"
                                                >
                                            @endif

                                            <div class="km-directory-avatar">
                                                @if ($circleType === 'yt')
                                                    <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg"
                                                         alt="Videopresentazione {{ $member->user->name }}"
                                                         class="h-full w-full object-cover">
                                                @elseif ($circleType === 'local')
                                                    <video src="{{ $localUrl }}"
                                                           muted loop playsinline autoplay
                                                           class="h-full w-full object-cover"
                                                           style="pointer-events:none;">
                                                    </video>
                                                @elseif ($photoUrl)
                                                    <img src="{{ $photoUrl }}"
                                                         alt="{{ $member->user->name }}"
                                                         class="h-full w-full bg-white p-2"
                                                         style="object-fit:{{ $mediaFit }};">
                                                @else
                                                    <span style="font-size:2.6rem; font-weight:700; color:#57534e;">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                                                @endif
                                            </div>

                                            @if ($hasVideo)
                                                {{-- Overlay sempre visibile: velo scuro + play bianco --}}
                                                <div class="absolute inset-0 flex items-center justify-center rounded-full bg-black/25 transition duration-200 group-hover/play:bg-black/45">
                                                    <svg class="h-11 w-11 text-white drop-shadow-lg transition duration-200 group-hover/play:scale-110" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/>
                                                    </svg>
                                                </div>
                                                </button>
                                            @endif
                                        </div>

                                        {{-- Nome + professione --}}
                                        <div class="mt-3">
                                            <h2 class="text-[1.02rem] font-semibold leading-tight text-stone-950">{{ $member->user->name }}</h2>
                                            @if ($profession)
                                                <p class="mt-0.5 text-[0.85rem] leading-5 text-stone-500">{{ $profession }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Città --}}
                                    <div class="mt-2.5 flex items-center justify-center">
                                        @if ($member->city)
                                            <span class="km-directory-chip km-directory-chip-city gap-2 py-1.5">
                                                <svg class="h-3.5 w-3.5 shrink-0 text-stone-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 3.39-3.34 6.63-4.39 7.56a.85.85 0 01-1.12 0c-1.05-.93-4.39-4.17-4.39-7.56zM10 10.5A2.45 2.45 0 1010 5.6a2.45 2.45 0 000 4.9z" clip-rule="evenodd"/></svg>
                                                {{ $member->city->name }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Social icons --}}
                                    @if($member->show_email || $whatsappUrl || ($member->show_phone && $member->phone) || $member->linkedin_url || $member->facebook_url || $member->instagram_url || $member->website)
                                    <div class="mt-2 flex items-center justify-center border-t border-stone-100 pt-2">
                                        <div class="flex flex-wrap items-center justify-center gap-1.5">
                                            @if ($member->show_email)
                                                <a href="mailto:{{ $member->user->email }}" title="Email" class="flex h-7 w-7 items-center justify-center rounded-full bg-sky-50 text-sky-500 transition hover:bg-sky-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M2.94 6.34A2 2 0 014.6 5.5h10.8a2 2 0 011.66.84L10 10.94 2.94 6.34z"/><path d="M2 7.56V13.5a2 2 0 002 2h12a2 2 0 002-2V7.56l-7.45 4.85a1 1 0 01-1.1 0L2 7.56z"/></svg>
                                                </a>
                                            @endif
                                            @if ($whatsappUrl)
                                                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" title="WhatsApp" class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.05 4.91A9.82 9.82 0 0012.03 2C6.56 2 2.12 6.43 2.12 11.9c0 1.75.46 3.46 1.33 4.96L2 22l5.29-1.39a9.9 9.9 0 004.74 1.2h.01c5.47 0 9.9-4.44 9.9-9.91a9.83 9.83 0 00-2.89-6.99zm-7.02 15.22h-.01a8.23 8.23 0 01-4.19-1.14l-.3-.18-3.14.82.84-3.06-.2-.31a8.2 8.2 0 01-1.26-4.36c0-4.53 3.69-8.22 8.24-8.22a8.16 8.16 0 015.82 2.41 8.16 8.16 0 012.4 5.82c0 4.54-3.69 8.22-8.2 8.22zm4.5-6.16c-.25-.12-1.47-.72-1.7-.8-.23-.09-.39-.12-.56.12-.16.25-.64.8-.78.96-.14.17-.28.19-.53.07-.25-.12-1.03-.38-1.96-1.22-.73-.64-1.22-1.43-1.36-1.67-.14-.24-.01-.37.11-.49.11-.11.25-.28.37-.42.12-.14.16-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.86-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.65.31-.22.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.17 1.73 2.64 4.19 3.7.58.25 1.03.39 1.38.5.58.18 1.11.16 1.53.1.47-.07 1.47-.6 1.68-1.19.21-.59.21-1.09.14-1.19-.06-.1-.22-.16-.47-.28z"/></svg>
                                                </a>
                                            @endif
                                            @if ($member->show_phone && $member->phone)
                                                <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}" title="Telefono" class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-50 text-rose-500 transition hover:bg-rose-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3.5A1.5 1.5 0 013.5 2h2.17a1.5 1.5 0 011.45 1.12l.65 2.6a1.5 1.5 0 01-.4 1.43l-1.2 1.2a11.04 11.04 0 005.31 5.31l1.2-1.2a1.5 1.5 0 011.43-.4l2.6.65A1.5 1.5 0 0118 14.33v2.17A1.5 1.5 0 0116.5 18h-1C8.596 18 2 11.404 2 3.5z"/></svg>
                                                </a>
                                            @endif
                                            @if ($member->linkedin_url)
                                                <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener" title="LinkedIn" class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-blue-600 transition hover:bg-blue-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452H17.21v-5.569c0-1.327-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.988V9h3.12v1.561h.046c.435-.824 1.497-1.693 3.082-1.693 3.296 0 3.905 2.17 3.905 4.993v6.591zM5.337 7.433a1.812 1.812 0 11-.001-3.624 1.812 1.812 0 010 3.624zM6.763 20.452H3.91V9h2.853v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                                </a>
                                            @endif
                                            @if ($member->facebook_url)
                                                <a href="{{ $member->facebook_url }}" target="_blank" rel="noopener" title="Facebook" class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.095 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.884v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.095 24 18.1 24 12.073z"/></svg>
                                                </a>
                                            @endif
                                            @if ($member->instagram_url)
                                                <a href="{{ $member->instagram_url }}" target="_blank" rel="noopener" title="Instagram" class="flex h-7 w-7 items-center justify-center rounded-full bg-pink-50 text-pink-500 transition hover:bg-pink-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                                </a>
                                            @endif
                                            @if ($member->website)
                                                <a href="{{ Str::startsWith($member->website, ['http://', 'https://']) ? $member->website : 'https://'.$member->website }}" target="_blank" rel="noopener" title="Sito web" class="flex h-7 w-7 items-center justify-center rounded-full bg-lime-50 text-lime-600 transition hover:bg-lime-100">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm5.25-8a6.98 6.98 0 00-.9-3.42h-2.08c.24 1.03.38 2.2.4 3.42h2.58zm-4.58 0a16.2 16.2 0 00-.45-3.42H9.78c-.23 1.02-.39 2.19-.45 3.42h1.34zm-1.34 2c.06 1.23.22 2.4.45 3.42h.44c.23-1.02.39-2.19.45-3.42H9.33zm-2 0H4.75a6.98 6.98 0 00.9 3.42h2.08a17.37 17.37 0 01-.4-3.42zm0-2c.02-1.22.16-2.39.4-3.42H5.65A6.98 6.98 0 004.75 10h2.58zm4.92 5.1a5 5 0 001.72-1.68h-1.36c-.11.61-.23 1.18-.36 1.68zm1.72-8.52a5 5 0 00-1.72-1.68c.13.5.25 1.07.36 1.68h1.36zM8.09 4.9A5 5 0 006.37 6.58h1.36c.11-.61.23-1.18.36-1.68zm-1.72 8.52a5 5 0 001.72 1.68c-.13-.5-.25-1.07-.36-1.68H6.37z" clip-rule="evenodd"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- ── Barra azioni ─────────────────────────────────────── --}}
                                <div class="km-directory-actions">

                                    {{-- Azioni: Profilo + Messaggio + One-to-one (sinistra) --}}
                                    <div class="flex shrink-0 items-center gap-1.5">
                                        <a href="{{ $profileUrl }}"
                                           class="km-button-primary flex items-center gap-1 px-3.5 py-1.5 text-[0.82rem]">
                                            Profilo
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                                        </a>

                                        @if(auth()->check() && auth()->id() !== $member->user_id)
                                            {{-- Messaggio --}}
                                            <form method="POST" action="{{ route('conversations.start') }}">
                                                @csrf
                                                <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                                <input type="hidden" name="message" value="Ciao {{ $member->user->name }}, ti contatto dalla directory membri di Kommunity.">
                                                <button type="submit" title="Invia messaggio"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-stone-200 bg-stone-50 text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M3.105 2.288a.75.75 0 00-.826.95l1.414 4.926A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.897 28.897 0 0015.293-7.155.75.75 0 000-1.115A28.897 28.897 0 003.105 2.288z"/>
                                                    </svg>
                                                </button>
                                            </form>

                                            {{-- One-to-one --}}
                                            <form method="POST" action="{{ route('one-to-ones.store') }}">
                                                @csrf
                                                <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                                <input type="hidden" name="meeting_mode" value="online">
                                                <input type="hidden" name="goal" value="Conosciamoci e valutiamo possibili sinergie professionali.">
                                                <input type="hidden" name="redirect_to" value="directory">
                                                <button type="submit" title="Proponi One-to-one"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full border border-stone-200 bg-stone-50 text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3zm13.5-9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-11 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    {{-- Icona categoria (destra) — solo icona, tooltip col nome --}}
                                    @if ($sectorLabel)
                                        <span
                                            title="{{ $sectorLabel }}"
                                            class="ml-auto flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-white"
                                            style="background:linear-gradient(135deg,#465d70 0%,#35495a 100%);box-shadow:0 3px 10px rgba(70,93,112,0.40);"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">{!! $sectorIconPaths !!}</svg>
                                        </span>
                                    @endif
                                </div>

                            </article>
                        @empty
                            <div class="km-panel p-8 md:col-span-2 xl:col-span-4">
                                <h2 class="text-2xl font-semibold text-stone-950">Nessun profilo trovato</h2>
                                <p class="mt-3 text-sm leading-7 text-stone-600">
                                    Allarga i filtri o riparti dalla directory completa.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <div class="km-panel p-4">
                        {{ $members->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
