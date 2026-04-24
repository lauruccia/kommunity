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
                        x-data="{
                            open: false,
                            embedUrl: '',
                            localUrl: '',
                            openVideo(detail) {
                                this.embedUrl = detail.embed || '';
                                this.localUrl = detail.local || '';
                                this.open = true;
                            },
                            closeVideo() {
                                const localVideo = this.$refs.modalVideo;
                                if (localVideo) {
                                    localVideo.pause();
                                    localVideo.removeAttribute('src');
                                    localVideo.load();
                                }
                                this.open = false;
                                this.embedUrl = '';
                                this.localUrl = '';
                            }
                        }"
                        @open-video.window="openVideo($event.detail)"
                        @keydown.escape.window="if (open) closeVideo()"
                    >
                        <template x-if="open">
                            <div
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                                @click.self="closeVideo()"
                            >
                                <div class="relative w-full max-w-3xl overflow-hidden rounded-[1.6rem] bg-black shadow-2xl" style="aspect-ratio:16/9;">
                                    <template x-if="embedUrl">
                                        <iframe :src="embedUrl" class="h-full w-full" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                                    </template>
                                    <template x-if="!embedUrl && localUrl">
                                        <video x-ref="modalVideo" :src="localUrl" controls autoplay class="h-full w-full"></video>
                                    </template>
                                    <button @click="closeVideo()" class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
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

                                $coverImage = $onepage?->coverImageUrl() ?: ($ytId ? 'https://img.youtube.com/vi/'.$ytId.'/hqdefault.jpg' : ($photoUrl ?: null));
                                $displayName = $member->user->name;
                                $websiteUrl = $member->website
                                    ? (Str::startsWith($member->website, ['http://', 'https://']) ? $member->website : 'https://'.$member->website)
                                    : null;
                                $locationLabel = collect([
                                    $member->city?->name,
                                    $member->region?->name,
                                ])->filter()->join(', ');

                            @endphp

                            <article class="km-directory-card group transition duration-300 hover:-translate-y-1">
                                <div class="relative">
                                    <div class="km-directory-banner" style="height:82px;background-size:cover;background-position:center;{{ $coverImage ? "background-image:linear-gradient(180deg,rgba(16,24,32,0.12),rgba(16,24,32,0.46)), url('".$coverImage."');" : "background-image:linear-gradient(180deg,#dbe7f3 0%, #c7d7ea 38%, #aac2dd 100%);" }}">
                                    </div>

                                    @if ($hasVideo)
                                        <button
                                            type="button"
                                            @click="window.dispatchEvent(new CustomEvent('open-video', { detail: { embed: @js($embedUrl), local: @js($localUrl) } }))"
                                            x-data="{
                                                preview() {
                                                    const video = this.$refs.previewVideo;
                                                    if (!video) return;
                                                    video.currentTime = 0;
                                                    video.play().catch(() => {});
                                                },
                                                reset() {
                                                    const video = this.$refs.previewVideo;
                                                    if (!video) return;
                                                    video.pause();
                                                    video.currentTime = 0;
                                                }
                                            }"
                                            @mouseenter="preview()"
                                            @mouseleave="reset()"
                                            @focus="preview()"
                                            @blur="reset()"
                                            class="km-directory-avatar-button km-directory-avatar-floating"
                                            style="position:absolute;left:1rem;top:1rem;bottom:auto;"
                                            title="Guarda la videopresentazione"
                                        >
                                            <div class="km-directory-avatar">
                                                @if ($circleType === 'yt')
                                                    <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg"
                                                         alt="Videopresentazione {{ $member->user->name }}"
                                                         class="h-full w-full object-cover">
                                                @elseif ($circleType === 'local')
                                                    <video src="{{ $localUrl }}"
                                                           x-ref="previewVideo"
                                                           muted playsinline preload="metadata"
                                                           class="h-full w-full object-cover"
                                                           style="pointer-events:none;">
                                                    </video>
                                                @elseif ($photoUrl)
                                                    <img src="{{ $photoUrl }}"
                                                         alt="{{ $member->user->name }}"
                                                         class="h-full w-full bg-white p-2"
                                                         style="object-fit:{{ $mediaFit }};">
                                                @else
                                                    <span style="font-size:2rem; font-weight:700; color:#57534e;">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <span class="km-directory-avatar-play-badge">
                                                <svg class="h-4 w-4 translate-x-[1px] text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M6.3 2.84A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.27l9.34-5.89a1.5 1.5 0 000-2.54L6.3 2.84z"/>
                                                </svg>
                                            </span>
                                        </button>
                                    @else
                                        <div class="km-directory-avatar km-directory-avatar-floating" style="position:absolute;left:1rem;top:1rem;bottom:auto;">
                                            @if ($photoUrl)
                                                <img src="{{ $photoUrl }}"
                                                     alt="{{ $member->user->name }}"
                                                     class="h-full w-full bg-white p-2"
                                                     style="object-fit:{{ $mediaFit }};">
                                            @else
                                                <span style="font-size:2rem; font-weight:700; color:#57534e;">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-1 flex-col px-5 pb-3 pt-4">
                                    <div aria-hidden="true" style="height:4.9rem;"></div>
                                    <div class="min-h-[3.2rem]">
                                        <h2 class="text-[1.18rem] font-semibold leading-[1.1] text-stone-950">{{ $displayName }}</h2>
                                        @if ($profession)
                                            <p class="mt-0.5 text-[0.83rem] leading-4 text-stone-500">{{ $profession }}</p>
                                        @endif
                                    </div>

                                    <div class="mt-2.5 space-y-1">
                                        @if ($locationLabel !== '')
                                            <div class="km-directory-detail" style="display:flex;align-items:center;gap:.6rem;min-width:0;font-size:.9rem;line-height:1.15;color:#334155;white-space:nowrap;">
                                                <span class="km-directory-detail-icon bg-emerald-50 text-emerald-600" style="display:inline-flex;align-items:center;justify-content:center;width:1.6rem;height:1.6rem;flex:0 0 1.6rem;border-radius:999px;">
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 3.39-3.34 6.63-4.39 7.56a.85.85 0 01-1.12 0c-1.05-.93-4.39-4.17-4.39-7.56zM10 10.5A2.45 2.45 0 1010 5.6a2.45 2.45 0 000 4.9z" clip-rule="evenodd"/></svg>
                                                </span>
                                                <span style="display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;">{{ $locationLabel }}</span>
                                            </div>
                                        @endif

                                        @if ($member->show_phone && $member->phone)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}" class="km-directory-detail km-directory-detail-link" style="display:flex;align-items:center;gap:.6rem;min-width:0;font-size:.9rem;line-height:1.15;color:#334155;text-decoration:none;white-space:nowrap;">
                                                <span class="km-directory-detail-icon bg-rose-50 text-rose-500" style="display:inline-flex;align-items:center;justify-content:center;width:1.6rem;height:1.6rem;flex:0 0 1.6rem;border-radius:999px;">
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3.5A1.5 1.5 0 013.5 2h2.17a1.5 1.5 0 011.45 1.12l.65 2.6a1.5 1.5 0 01-.4 1.43l-1.2 1.2a11.04 11.04 0 005.31 5.31l1.2-1.2a1.5 1.5 0 011.43-.4l2.6.65A1.5 1.5 0 0118 14.33v2.17A1.5 1.5 0 0116.5 18h-1C8.596 18 2 11.404 2 3.5z"/></svg>
                                                </span>
                                                <span style="display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;">{{ $member->phone }}</span>
                                            </a>
                                        @endif

                                        @if ($member->show_email)
                                            <a href="mailto:{{ $member->user->email }}" class="km-directory-detail km-directory-detail-link" style="display:flex;align-items:center;gap:.6rem;min-width:0;font-size:.9rem;line-height:1.15;color:#334155;text-decoration:none;white-space:nowrap;">
                                                <span class="km-directory-detail-icon bg-sky-50 text-sky-500" style="display:inline-flex;align-items:center;justify-content:center;width:1.6rem;height:1.6rem;flex:0 0 1.6rem;border-radius:999px;">
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2.94 6.34A2 2 0 014.6 5.5h10.8a2 2 0 011.66.84L10 10.94 2.94 6.34z"/><path d="M2 7.56V13.5a2 2 0 002 2h12a2 2 0 002-2V7.56l-7.45 4.85a1 1 0 01-1.1 0L2 7.56z"/></svg>
                                                </span>
                                                <span style="display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;">{{ $member->user->email }}</span>
                                            </a>
                                        @endif

                                        @if ($websiteUrl)
                                            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="km-directory-detail km-directory-detail-link" style="display:flex;align-items:center;gap:.6rem;min-width:0;font-size:.9rem;line-height:1.15;color:#334155;text-decoration:none;white-space:nowrap;">
                                                <span class="km-directory-detail-icon bg-lime-50 text-lime-600" style="display:inline-flex;align-items:center;justify-content:center;width:1.6rem;height:1.6rem;flex:0 0 1.6rem;border-radius:999px;">
                                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm5.25-8a6.98 6.98 0 00-.9-3.42h-2.08c.24 1.03.38 2.2.4 3.42h2.58zm-4.58 0a16.2 16.2 0 00-.45-3.42H9.78c-.23 1.02-.39 2.19-.45 3.42h1.34zm-1.34 2c.06 1.23.22 2.4.45 3.42h.44c.23-1.02.39-2.19.45-3.42H9.33zm-2 0H4.75a6.98 6.98 0 00.9 3.42h2.08a17.37 17.37 0 01-.4-3.42zm0-2c.02-1.22.16-2.39.4-3.42H5.65A6.98 6.98 0 004.75 10h2.58zm4.92 5.1a5 5 0 001.72-1.68h-1.36c-.11.61-.23 1.18-.36 1.68zm1.72-8.52a5 5 0 00-1.72-1.68c.13.5.25 1.07.36 1.68h1.36zM8.09 4.9A5 5 0 006.37 6.58h1.36c.11-.61.23-1.18.36-1.68zm-1.72 8.52a5 5 0 001.72 1.68c-.13-.5-.25-1.07-.36-1.68H6.37z" clip-rule="evenodd"/></svg>
                                                </span>
                                                <span style="display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;">{{ preg_replace('/^https?:\/\//', '', $member->website) }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- ── Barra azioni ─────────────────────────────────────── --}}
                                <div class="km-directory-actions" style="display:flex;align-items:center;gap:.5rem;border-top:1px solid rgba(70,93,112,.12);padding:0.7rem 1.25rem 0.9rem;">

                                    <div class="flex shrink-0 items-center gap-2" style="display:flex;align-items:center;gap:.5rem;">
                                        <a href="{{ $profileUrl }}"
                                           title="Profilo"
                                           class="km-directory-action-button km-directory-action-button-primary"
                                           style="display:inline-flex;align-items:center;justify-content:center;width:2.85rem;height:2.85rem;border-radius:999px;background:linear-gradient(135deg,#55794f 0%,#426240 100%);color:#fff;box-shadow:0 14px 28px rgba(66,98,64,.22);">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                        </a>

                                        @if(auth()->check() && auth()->id() !== $member->user_id)
                                            <form method="POST" action="{{ route('conversations.start') }}" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                                <button type="submit" title="Invia messaggio"
                                                        class="km-directory-action-button"
                                                        style="display:inline-flex;align-items:center;justify-content:center;width:2.85rem;height:2.85rem;border-radius:999px;border:1px solid rgba(70,93,112,.14);background:#f8fafc;color:#334155;box-shadow:0 6px 18px rgba(15,23,42,.08);">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M3.105 2.288a.75.75 0 00-.826.95l1.414 4.926A1.5 1.5 0 005.135 9.25h6.115a.75.75 0 010 1.5H5.135a1.5 1.5 0 00-1.442 1.086l-1.414 4.926a.75.75 0 00.826.95 28.897 28.897 0 0015.293-7.155.75.75 0 000-1.115A28.897 28.897 0 003.105 2.288z"/>
                                                    </svg>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('one-to-ones.store') }}" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                                <input type="hidden" name="meeting_mode" value="online">
                                                <input type="hidden" name="goal" value="Conosciamoci e valutiamo possibili sinergie professionali.">
                                                <input type="hidden" name="redirect_to" value="directory">
                                                <button type="submit" title="Proponi One-to-one"
                                                        class="km-directory-action-button"
                                                        style="display:inline-flex;align-items:center;justify-content:center;width:2.85rem;height:2.85rem;border-radius:999px;border:1px solid rgba(70,93,112,.14);background:#f8fafc;color:#334155;box-shadow:0 6px 18px rgba(15,23,42,.08);">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3zm13.5-9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-11 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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
