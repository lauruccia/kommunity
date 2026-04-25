<x-app-layout>

{{-- ════════════════════════════════════════════════════════════════════════
     CSS CRITICO — iniettato direttamente per non dipendere da npm/vite.
     Definisce il layout delle card (header-zone, banner, avatar a cavallo)
     e isola il z-index della card per evitare overflow nella sidebar.
════════════════════════════════════════════════════════════════════════ --}}
<style>
/*
 * OVERRIDE DEFINITIVO: il CSS compilato da Vite ha position:absolute su .km-directory-banner
 * e .km-directory-avatar-wrap. Senza un override esplicito di "position", quegli elementi
 * restano fuori dal flusso e il testo viene coperto.
 * Qui sovrascriviamo TUTTE le proprietà di posizionamento con !important.
 */

/* Card: flex column */
.km-directory-card {
    display: flex !important;
    flex-direction: column !important;
    position: relative !important;
    overflow: hidden !important;
    isolation: isolate !important;
}

/*
 * Banner: RIMOSSO position:absolute dal Vite CSS.
 * Ora è un blocco nel flusso, alto 100px.
 */
.km-directory-banner {
    position: relative !important;   /* override Vite: position:absolute */
    inset: auto !important;          /* override Vite: inset:0 */
    top: auto !important;
    left: auto !important;
    right: auto !important;
    bottom: auto !important;
    width: 100% !important;
    height: 100px !important;
    flex-shrink: 0 !important;
    background: linear-gradient(135deg, #dbe7f3 0%, #c7d7ea 45%, #aac2dd 100%);
    background-size: cover !important;
    background-position: center !important;
}

/*
 * Avatar wrapper: RIMOSSO position:absolute dal Vite CSS.
 * margin-top negativo = metà avatar → straddle 50% nel flusso normale.
 * Il contenuto dopo parte dove finisce il cerchio: testo MAI coperto.
 */
.km-directory-avatar-wrap {
    position: relative !important;      /* override Vite: position:absolute */
    left: auto !important;              /* override Vite: left:50% */
    top: auto !important;               /* override Vite: top:100px */
    transform: none !important;         /* override Vite: translate(-50%,-50%) */
    margin-top: -3.5rem !important;     /* sale di 3.5rem nel banner → straddle */
    padding-left: 1.25rem !important;
    z-index: 2;
    line-height: 0;
    flex-shrink: 0 !important;
}

.km-directory-avatar-button {
    border: 0;
    background: transparent;
    padding: 0;
    cursor: pointer;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Avatar cerchio */
.km-directory-avatar {
    position: relative !important;
    display: inline-flex !important;
    width: 7rem !important;
    height: 7rem !important;
    border-radius: 9999px !important;
    border: 4px solid #ffffff !important;
    box-shadow: 0 14px 34px rgba(0,0,0,0.14);
    overflow: hidden !important;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg, #ffffff 0%, #f5f5f4 100%);
    flex-shrink: 0;
    transition: box-shadow 0.22s ease;
}
.km-directory-avatar-button:hover .km-directory-avatar {
    box-shadow: 0 18px 40px rgba(15,23,33,0.28);
}

/* Forza img dentro cerchio a restare nel cerchio */
.km-directory-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    max-width: none !important;
}

/* Video overlay hover dentro cerchio */
.km-directory-avatar video.km-video-preview {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.km-directory-avatar video.km-video-preview.is-playing { opacity: 1; }

/* Badge play */
.km-directory-avatar-play-badge {
    position: absolute;
    right: 0.15rem;
    bottom: 0.15rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.85rem !important;
    height: 1.85rem !important;
    border-radius: 9999px;
    border: 2.5px solid rgba(255,255,255,0.95);
    background: linear-gradient(135deg, #55794f 0%, #426240 100%);
    box-shadow: 0 10px 20px rgba(14,20,27,0.22);
}

/* Corpo card: piccolo respiro dopo l'avatar */
.km-directory-body {
    padding: 0.5rem 1rem 0.75rem 1.25rem !important;
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
}

/* Mobile: banner 80px, avatar 6rem → margin-top: -3rem */
@media (max-width: 639px) {
    .km-directory-banner      { height: 80px !important; }
    .km-directory-avatar-wrap { margin-top: -3rem !important; padding-left: 1rem !important; }
    .km-directory-avatar      { width: 6rem !important; height: 6rem !important; }
    .km-directory-avatar-play-badge { width: 1.6rem !important; height: 1.6rem !important; }
    .km-directory-body        { padding: 0.5rem 0.75rem 0.75rem 1rem !important; }
}
</style>

    <x-slot name="header">
        <div class="w-full rounded-[2rem] bg-[linear-gradient(135deg,#425767_0%,#4d6474_52%,#5b7d4b_100%)] p-6 text-white shadow-[0_22px_60px_rgba(66,87,103,0.22)]">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/70">Directory membri</p>
                    <h1 class="mt-2 font-serif text-2xl font-semibold sm:text-3xl">Kommunity business interna</h1>
                </div>
                <div class="shrink-0 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium backdrop-blur">
                    {{ $members->total() }} membri attivi
                </div>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
             class="mx-2 mb-4 mt-2 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm text-emerald-800 shadow-sm">
            <svg class="h-5 w-5 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
            <button @click="show = false" class="ml-auto text-emerald-500 hover:text-emerald-700">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
            </button>
        </div>
    @endif

    <div class="pb-12">
        <div class="w-full px-2 sm:px-3 lg:px-4"
             x-data="{ filtersOpen: false }">

            {{-- Toggle filtri: solo su mobile (< md) --}}
            <div class="mb-4 md:hidden">
                <button @click="filtersOpen = !filtersOpen"
                        class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-5 py-2.5 text-sm font-medium text-stone-700 shadow-sm transition hover:bg-stone-50">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="filtersOpen ? 'Chiudi filtri' : 'Filtri'">Filtri</span>
                </button>
            </div>

            {{-- Layout principale: sidebar + cards --}}
            <div class="flex gap-5">

                {{-- ═══════════════════════════════════════════
                     SIDEBAR — visibile sempre da md in su,
                     collassabile su mobile
                ════════════════════════════════════════════ --}}
                <aside
                    class="md:sticky md:top-6 md:self-start md:block md:w-[260px] lg:w-[280px] shrink-0 space-y-4"
                    :class="filtersOpen ? 'block' : 'hidden md:block'"
                    style="min-width:0;"
                >
                    {{-- Categorie --}}
                    <div class="km-panel overflow-hidden p-0 shadow-[0_14px_34px_rgba(66,87,103,0.08)]">
                        <div class="border-b border-stone-200 bg-[color:var(--km-soft)] px-5 py-3.5">
                            <div class="flex items-center justify-between">
                                <h2 class="text-sm font-semibold text-stone-950">Categorie</h2>
                                @if($filters['category'] ?? null)
                                    <a href="{{ route('directory.index', array_filter(array_merge($filters, ['category' => null]))) }}"
                                       class="text-xs text-stone-400 hover:text-stone-700">Azzera</a>
                                @endif
                            </div>
                        </div>
                        <div class="divide-y divide-stone-100 text-sm">
                            @foreach ($rootCategories as $root)
                                <div x-data="{ open: {{ $root->activeChildren->isNotEmpty() && (($filters['category'] ?? null) == $root->id || $root->activeChildren->contains('id', (int)($filters['category'] ?? 0))) ? 'true' : 'false' }} }">
                                    <div class="flex items-center">
                                        <a href="{{ route('directory.index', array_filter(array_merge($filters, ['category' => $root->id]))) }}"
                                           class="flex-1 px-5 py-2.5 font-medium transition hover:bg-stone-50
                                                  {{ ($filters['category'] ?? null) == $root->id ? 'text-emerald-700' : 'text-stone-700' }}">
                                            {{ $root->name }}
                                        </a>
                                        @if($root->activeChildren->isNotEmpty())
                                            <button @click="open = !open" class="px-3 py-2.5 text-stone-400 hover:text-stone-700">
                                                <svg class="h-3.5 w-3.5 transition-transform duration-150" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    @if($root->activeChildren->isNotEmpty())
                                        <div x-show="open" x-cloak class="border-t border-stone-100 bg-stone-50/70">
                                            @foreach($root->activeChildren as $child)
                                                <a href="{{ route('directory.index', array_filter(array_merge($filters, ['category' => $child->id]))) }}"
                                                   class="flex items-center gap-2 py-2 pl-9 pr-5 text-xs transition hover:bg-stone-100
                                                          {{ ($filters['category'] ?? null) == $child->id ? 'font-semibold text-emerald-700' : 'text-stone-600' }}">
                                                    <span class="h-1 w-1 rounded-full bg-stone-300 shrink-0"></span>
                                                    {{ $child->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filtri rapidi --}}
                    @php
                        $provinceOptions = $provinces->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'region_id' => $p->region_id])->values();
                        $cityOptions     = $regions->flatMap(fn ($r) => $r->cities)
                            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'province_id' => $c->province_id ?? null, 'region_id' => $c->region_id])
                            ->values();
                    @endphp

                    <div class="km-panel p-5 shadow-[0_14px_34px_rgba(66,87,103,0.08)]"
                         x-data="{
                             regionId:   '{{ $filters['region']   ?? '' }}',
                             provinceId: '{{ $filters['province'] ?? '' }}',
                             provinces:  @js($provinceOptions),
                             cities:     @js($cityOptions),
                             get filteredProvinces() {
                                 return this.regionId
                                     ? this.provinces.filter(p => p.region_id == this.regionId)
                                     : this.provinces;
                             },
                             get filteredCities() {
                                 if (this.provinceId) return this.cities.filter(c => c.province_id == this.provinceId);
                                 if (this.regionId)   return this.cities.filter(c => c.region_id   == this.regionId);
                                 return this.cities;
                             }
                         }">
                        <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Filtri rapidi</h3>
                        <form method="GET" class="mt-3 space-y-3">
                            @if($filters['category'] ?? null)
                                <input type="hidden" name="category" value="{{ $filters['category'] }}">
                            @endif
                            @if($filters['search'] ?? null)
                                <input type="hidden" name="search" value="{{ $filters['search'] }}">
                            @endif

                            <div>
                                <label class="mb-1 block text-xs font-medium text-stone-500">Regione</label>
                                <select name="region" class="km-input text-sm" x-model="regionId" @change="provinceId=''">
                                    <option value="">Tutte le regioni</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" @selected(($filters['region'] ?? null) == $region->id)>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="filteredProvinces.length > 0" x-cloak>
                                <label class="mb-1 block text-xs font-medium text-stone-500">Provincia</label>
                                <select name="province" class="km-input text-sm" x-model="provinceId">
                                    <option value="">Tutte le province</option>
                                    <template x-for="p in filteredProvinces" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="provinceId == p.id"></option>
                                    </template>
                                </select>
                            </div>

                            <div x-show="filteredCities.length > 0" x-cloak>
                                <label class="mb-1 block text-xs font-medium text-stone-500">Città</label>
                                <select name="city" class="km-input text-sm">
                                    <option value="">Tutte le città</option>
                                    <template x-for="c in filteredCities" :key="c.id">
                                        <option :value="c.id" x-text="c.name" :selected="c.id == {{ $filters['city'] ?? 0 }}"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-stone-500">Pianeta</label>
                                <select name="chapter" class="km-input text-sm">
                                    <option value="">Tutti i Pianeti</option>
                                    @foreach ($chapters as $chapter)
                                        <option value="{{ $chapter->id }}" @selected(($filters['chapter'] ?? null) == $chapter->id)>{{ $chapter->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="km-button-primary w-full py-2.5 text-sm">Applica filtri</button>
                            <a href="{{ route('directory.index') }}" class="block text-center text-xs text-stone-400 hover:text-stone-700">
                                Azzera tutti i filtri
                            </a>
                        </form>
                    </div>
                </aside>

                {{-- ═══════════════════════════════════════════
                     AREA PRINCIPALE: ricerca + card
                ════════════════════════════════════════════ --}}
                <section class="min-w-0 flex-1 space-y-4">

                    {{-- Barra di ricerca --}}
                    <form method="GET" class="flex gap-2">
                        <input type="text" name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               class="km-input flex-1 bg-white shadow-sm"
                               placeholder="Nome, professione, categoria…">
                        <button type="submit" class="km-button-primary px-5 shrink-0">Cerca</button>
                        @if(array_filter($filters))
                            <a href="{{ route('directory.index') }}" class="km-button-secondary px-4 shrink-0">×</a>
                        @endif
                        @foreach(['category','region','province','city','chapter'] as $fk)
                            @if($filters[$fk] ?? null)
                                <input type="hidden" name="{{ $fk }}" value="{{ $filters[$fk] }}">
                            @endif
                        @endforeach
                    </form>

                    {{-- Modal video globale --}}
                    <div x-data="{
                             open: false, embedUrl: '', localUrl: '',
                             openVideo(d) { this.embedUrl = d.embed||''; this.localUrl = d.local||''; this.open = true; },
                             closeVideo() {
                                 const v = this.$refs.mv;
                                 if (v) { v.pause(); v.removeAttribute('src'); v.load(); }
                                 this.open = false; this.embedUrl = ''; this.localUrl = '';
                             }
                         }"
                         @open-video.window="openVideo($event.detail)"
                         @keydown.escape.window="if(open) closeVideo()">
                        <template x-if="open">
                            <div x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4"
                                 @click.self="closeVideo()">
                                <div class="relative w-full max-w-3xl overflow-hidden rounded-[1.6rem] bg-black shadow-2xl" style="aspect-ratio:16/9;">
                                    <template x-if="embedUrl">
                                        <iframe :src="embedUrl" class="h-full w-full" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                                    </template>
                                    <template x-if="!embedUrl && localUrl">
                                        <video x-ref="mv" :src="localUrl" controls autoplay class="h-full w-full"></video>
                                    </template>
                                    <button @click="closeVideo()"
                                            class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- ═══════ GRIGLIA CARD ═══════ --}}
                    {{-- 1 col < sm | 2 col sm | 3 col lg | 4 col xl --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                        @forelse ($members as $member)
                            @php
                                $onepage      = $member->user->memberOnepage ?? null;
                                // Nel cerchio della directory: solo foto personale.
                                // Il logo aziendale NON viene usato come avatar
                                // (evita che il logo KOSMOS appaia al posto della foto).
                                // Se non c'è foto → viene mostrata l'iniziale.
                                $photoUrl     = $member->avatarUrl() ?: null;
                                $mediaFit     = 'cover'; // le foto personali sono sempre cover
                                $profession   = $member->professions->first()?->name
                                             ?? $member->profession?->name
                                             ?? $member->profession_other
                                             ?? null;
                                $profileUrl   = $onepage?->slug ? route('members.show', $onepage->slug) : '#';
                                $embedUrl     = $member->videoEmbedUrl() ?? '';
                                $localUrl     = (!$embedUrl && $member->introVideoUrl()) ? $member->introVideoUrl() : '';
                                $hasVideo     = $member->hasVideo();

                                $ytId = null;
                                if ($embedUrl && preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $embedUrl, $m)) {
                                    $ytId = $m[1];
                                }
                                $circleType = 'photo';
                                if ($hasVideo) {
                                    $circleType = $ytId ? 'yt' : ($localUrl ? 'local' : 'vimeo');
                                }

                                // Il banner usa SOLO la cover image dedicata.
                                // La foto profilo / thumbnail video restano nel cerchio avatar,
                                // non vengono mai promosse a sfondo banner.
                                $coverImage   = $onepage?->coverImageUrl() ?: null;
                                $displayName  = $member->user->name;
                                $rawWebsite   = $member->website ?? null;
                                $websiteUrl   = $rawWebsite
                                    ? (\Illuminate\Support\Str::startsWith($rawWebsite, ['http://', 'https://'])
                                        ? $rawWebsite : 'https://'.$rawWebsite)
                                    : null;
                                $locationLabel = collect([$member->city?->name, $member->region?->name])
                                    ->filter()->join(', ');
                            @endphp

                            {{-- ─── CARD ─── --}}
                            <article class="km-directory-card group transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_rgba(39,54,67,0.14)]"
                                     style="display:flex;flex-direction:column;position:relative;overflow:hidden;">

                                {{-- Zona header: contiene banner (100 px) + avatar a cavallo al 50%.
                                     km-directory-header-zone ha altezza = calc(100px + 3.5rem) via CSS. --}}
                                <div class="km-directory-header-zone">

                                    {{-- Banner: position:absolute, inset:0, height:100px (dal CSS) --}}
                                    <div class="km-directory-banner"
                                         @if($coverImage) style="background-image:linear-gradient(180deg,rgba(16,24,32,0.05),rgba(16,24,32,0.32)),url('{{ e($coverImage) }}');" @endif>
                                    </div>

                                    {{-- Avatar: position:absolute, left:50%, top:100px, translate(-50%,-50%) (dal CSS) --}}
                                    <div class="km-directory-avatar-wrap">
                                    @if ($hasVideo)
                                        {{-- Membro con video: cerchio cliccabile + anteprima al hover --}}
                                        <button type="button"
                                                x-data="{ playing: false }"
                                                @mouseenter="
                                                    playing = true;
                                                    $nextTick(() => {
                                                        const v = $el.querySelector('video.km-video-preview');
                                                        if (v) { v.currentTime = 0; v.play().catch(() => {}); }
                                                        $el.querySelector('video.km-video-preview')?.classList.add('is-playing');
                                                    });
                                                "
                                                @mouseleave="
                                                    playing = false;
                                                    const v = $el.querySelector('video.km-video-preview');
                                                    if (v) { v.pause(); v.currentTime = 0; v.classList.remove('is-playing'); }
                                                "
                                                @click="
                                                    const v = $el.querySelector('video.km-video-preview');
                                                    if (v) { v.pause(); }
                                                    window.dispatchEvent(new CustomEvent('open-video', {detail:{embed:@js($embedUrl),local:@js($localUrl)}}));
                                                "
                                                class="km-directory-avatar-button"
                                                title="Guarda la videopresentazione">

                                            <div class="km-directory-avatar" style="position:relative;display:inline-flex;width:7rem;height:7rem;border-radius:9999px;border:4px solid #fff;box-shadow:0 14px 34px rgba(0,0,0,0.14);overflow:hidden;align-items:center;justify-content:center;background:linear-gradient(180deg,#fff 0%,#f5f5f4 100%);flex-shrink:0;">
                                                {{-- Layer base: foto o iniziale --}}
                                                @if ($photoUrl)
                                                    <img src="{{ $photoUrl }}" alt="{{ $displayName }}"
                                                         class="h-full w-full object-cover">
                                                @elseif ($circleType === 'yt')
                                                    <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg"
                                                         alt="{{ $displayName }}" class="h-full w-full object-cover">
                                                @else
                                                    <span class="text-2xl font-bold text-stone-500">{{ strtoupper(substr($displayName,0,1)) }}</span>
                                                @endif

                                                {{-- Layer video: visibile solo su hover (fade-in/out) --}}
                                                @if ($circleType === 'local' && $localUrl)
                                                    <video class="km-video-preview"
                                                           src="{{ $localUrl }}"
                                                           muted playsinline preload="none"
                                                           loop>
                                                    </video>
                                                @endif
                                            </div>

                                            {{-- Badge play --}}
                                            <span class="km-directory-avatar-play-badge">
                                                <svg class="h-3.5 w-3.5 translate-x-px text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/>
                                                </svg>
                                            </span>
                                        </button>
                                    @else
                                        {{-- Membro senza video: solo cerchio statico --}}
                                        <a href="{{ $profileUrl }}" class="km-directory-avatar-button" title="{{ $displayName }}">
                                            <div class="km-directory-avatar" style="position:relative;display:inline-flex;width:7rem;height:7rem;border-radius:9999px;border:4px solid #fff;box-shadow:0 14px 34px rgba(0,0,0,0.14);overflow:hidden;align-items:center;justify-content:center;background:linear-gradient(180deg,#fff 0%,#f5f5f4 100%);flex-shrink:0;">
                                                @if ($photoUrl)
                                                    <img src="{{ $photoUrl }}" alt="{{ $displayName }}"
                                                         class="h-full w-full object-cover">
                                                @else
                                                    <span class="text-2xl font-bold text-stone-500">{{ strtoupper(substr($displayName,0,1)) }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endif
                                </div>{{-- /km-directory-avatar-wrap --}}

                                {{-- Contenuto: parte subito dopo l'avatar nel flusso → impossibile essere coperto --}}
                                <div class="km-directory-body" style="padding:0.5rem 1rem 0.75rem 1.25rem;flex:1;display:flex;flex-direction:column;min-width:0;">

                                    {{-- Nome + professione: sinistra --}}
                                    <div class="min-w-0">
                                        <h2 class="text-[0.93rem] font-semibold leading-snug text-stone-950 truncate">
                                            <a href="{{ $profileUrl }}"
                                               class="transition hover:text-[color:var(--km-accent-strong)]">{{ $displayName }}</a>
                                        </h2>
                                        @if ($profession)
                                            <p class="mt-0.5 text-[0.74rem] leading-tight text-stone-400 truncate">{{ $profession }}</p>
                                        @endif
                                    </div>

                                    <div class="my-2 border-t border-stone-100"></div>

                                    {{-- Contatti: sinistra --}}
                                    <div class="space-y-1.5">
                                        @if ($locationLabel !== '')
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-emerald-50 text-emerald-600">
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 3.39-3.34 6.63-4.39 7.56a.85.85 0 01-1.12 0c-1.05-.93-4.39-4.17-4.39-7.56zM10 10.5A2.45 2.45 0 1010 5.6a2.45 2.45 0 000 4.9z" clip-rule="evenodd"/></svg>
                                                </span>
                                                <span class="truncate text-[0.76rem] text-stone-600">{{ $locationLabel }}</span>
                                            </div>
                                        @endif

                                        @if ($member->show_phone && $member->phone)
                                            <a href="tel:{{ preg_replace('/\s+/','',$member->phone) }}"
                                               class="flex items-center gap-2 min-w-0 group/link">
                                                <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-rose-50 text-rose-500">
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3.5A1.5 1.5 0 013.5 2h2.17a1.5 1.5 0 011.45 1.12l.65 2.6a1.5 1.5 0 01-.4 1.43l-1.2 1.2a11.04 11.04 0 005.31 5.31l1.2-1.2a1.5 1.5 0 011.43-.4l2.6.65A1.5 1.5 0 0118 14.33v2.17A1.5 1.5 0 0116.5 18h-1C8.596 18 2 11.404 2 3.5z"/></svg>
                                                </span>
                                                <span class="truncate text-[0.76rem] text-stone-600 group-hover/link:text-[color:var(--km-accent-strong)] transition">{{ $member->phone }}</span>
                                            </a>
                                        @endif

                                        @if ($member->show_email)
                                            <a href="mailto:{{ $member->user->email }}"
                                               class="flex items-center gap-2 min-w-0 group/link">
                                                <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-sky-50 text-sky-500">
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M2.94 6.34A2 2 0 014.6 5.5h10.8a2 2 0 011.66.84L10 10.94 2.94 6.34z"/><path d="M2 7.56V13.5a2 2 0 002 2h12a2 2 0 002-2V7.56l-7.45 4.85a1 1 0 01-1.1 0L2 7.56z"/></svg>
                                                </span>
                                                <span class="truncate text-[0.76rem] text-stone-600 group-hover/link:text-[color:var(--km-accent-strong)] transition">{{ $member->user->email }}</span>
                                            </a>
                                        @endif

                                        @if ($websiteUrl)
                                            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener"
                                               class="flex items-center gap-2 min-w-0 group/link">
                                                <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-lime-50 text-lime-600">
                                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm5.25-8a6.98 6.98 0 00-.9-3.42h-2.08c.24 1.03.38 2.2.4 3.42h2.58zm-4.58 0a16.2 16.2 0 00-.45-3.42H9.78c-.23 1.02-.39 2.19-.45 3.42h1.34zm-1.34 2c.06 1.23.22 2.4.45 3.42h.44c.23-1.02.39-2.19.45-3.42H9.33zm-2 0H4.75a6.98 6.98 0 00.9 3.42h2.08a17.37 17.37 0 01-.4-3.42zm0-2c.02-1.22.16-2.39.4-3.42H5.65A6.98 6.98 0 004.75 10h2.58zm4.92 5.1a5 5 0 001.72-1.68h-1.36c-.11.61-.23 1.18-.36 1.68zm1.72-8.52a5 5 0 00-1.72-1.68c.13.5.25 1.07.36 1.68h1.36zM8.09 4.9A5 5 0 006.37 6.58h1.36c.11-.61.23-1.18.36-1.68zm-1.72 8.52a5 5 0 001.72 1.68c-.13-.5-.25-1.07-.36-1.68H6.37z" clip-rule="evenodd"/></svg>
                                                </span>
                                                <span class="truncate text-[0.76rem] text-stone-600 group-hover/link:text-[color:var(--km-accent-strong)] transition">
                                                    {{ preg_replace('/^https?:\/\//', '', rtrim($rawWebsite, '/')) }}
                                                </span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Barra azioni --}}
                                <div class="flex items-center gap-2 border-t px-4 pb-3 pt-2.5"
                                     style="border-color:rgba(70,93,112,.10); margin-top: auto;">
                                    <a href="{{ $profileUrl }}" title="Vedi profilo"
                                       class="km-directory-action-button km-directory-action-button-primary"
                                       style="width:2.5rem;height:2.5rem;">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                    </a>

                                    @if(auth()->check() && auth()->id() !== $member->user_id)
                                        <form method="POST" action="{{ route('conversations.start') }}" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                            <button type="submit" title="Invia messaggio"
                                                    class="km-directory-action-button" style="width:2.5rem;height:2.5rem;">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3.105 2.288a.75.75 0 00-.826.95l1.414 4.926A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.897 28.897 0 0015.293-7.155.75.75 0 000-1.115A28.897 28.897 0 003.105 2.288z"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('one-to-ones.store') }}" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                            <input type="hidden" name="meeting_mode" value="online">
                                            <input type="hidden" name="goal" value="Conosciamoci e valutiamo possibili sinergie professionali.">
                                            <input type="hidden" name="redirect_to" value="directory">
                                            <button type="submit" title="Proponi One-to-one"
                                                    class="km-directory-action-button" style="width:2.5rem;height:2.5rem;">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3zm13.5-9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-11 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                            </article>
                        @empty
                            <div class="km-panel p-8 col-span-full">
                                <h2 class="text-xl font-semibold text-stone-950">Nessun profilo trovato</h2>
                                <p class="mt-2 text-sm text-stone-500">Allarga i filtri o riparti dalla directory completa.</p>
                                <a href="{{ route('directory.index') }}" class="km-button-secondary mt-4 inline-flex">Azzera filtri</a>
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
