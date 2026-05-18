<x-app-layout>




<style>

.km-directory-avatar img,
.km-directory-avatar span{
    position:relative;
    z-index:1;
}

.km-directory-card{
    position:relative!important;
    display:flex!important;
    flex-direction:column!important;
    overflow:visible!important;
    border-radius:1.75rem;
    background:#fff;
    box-shadow:0 8px 28px rgba(66,87,103,0.10);
}

/* Avatar centrato che fuoriesce dalla card del 50% in alto */

.km-directory-avatar-wrap{
    position:absolute!important;
    left:50%!important;
    top:0!important;
    transform:translate(-50%, -50%)!important;
    z-index:5!important;
}

.km-directory-avatar{
    position:relative!important;
    width:6rem!important;
    height:6rem!important;
    border-radius:9999px!important;
    border:4px solid #fff!important;
    box-shadow:0 8px 24px rgba(0,0,0,.15);
    overflow:hidden!important;
    display:flex!important;
    align-items:center;
    justify-content:center;
    background:#e2e8f0;
}

.km-directory-avatar img{
    width:100%!important;
    height:100%!important;
    object-fit:cover!important;
}

/* Spazio in alto per l'avatar che esce dalla card (metà avatar = 3rem + gap) */
.km-directory-body{
    padding:3.75rem 1rem .9rem 1rem!important;
    margin-top:0!important;
    flex:1!important;
    display:flex!important;
    flex-direction:column!important;
}

/* MOBILE */
@media(max-width:639px){

    .km-directory-avatar{
        width:5rem!important;
        height:5rem!important;
    }

    .km-directory-body{
        padding:3.25rem .75rem .75rem .75rem!important;
    }
}
</style>


<x-slot name="header">
    <div class="w-full rounded-[2rem] bg-[linear-gradient(135deg,#425767_0%,#4d6474_52%,#5b7d4b_100%)] p-6 text-white shadow-[0_22px_60px_rgba(66,87,103,0.22)]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-white/70">K-Members</p>
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
        {{ session('success') }}
    </div>
@endif

<div class="pb-12">
    <div class="w-full px-2 sm:px-3 lg:px-4" x-data="{ filtersOpen: window.innerWidth >= 768 }">

        <div class="mb-4 md:hidden">
            <button @click="filtersOpen = !filtersOpen"
                    class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-5 py-2.5 text-sm font-medium text-stone-700 shadow-sm transition hover:bg-stone-50">
                <span x-text="filtersOpen ? 'Chiudi filtri' : 'Filtri'">Filtri</span>
            </button>
        </div>

        <div class="flex gap-5">

            <aside
    class="md:sticky md:top-6 md:self-start md:w-[260px] lg:w-[280px] shrink-0 space-y-4"
    x-show="filtersOpen || window.innerWidth >= 768"
    x-cloak
    style="min-width:0;"
>
                <div class="km-panel overflow-hidden p-0 shadow-[0_14px_34px_rgba(66,87,103,0.08)]">
                    <div class="border-b border-stone-200 bg-[color:var(--km-soft)] px-5 py-3.5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-stone-950">Professioni</h2>
                            @if($filters['profession'] ?? null)
                                <a href="{{ route('directory.index', array_filter(array_merge($filters, ['profession' => null]))) }}"
                                   class="text-xs text-stone-400 hover:text-stone-700">Azzera</a>
                            @endif
                        </div>
                    </div>

                    <div class="divide-y divide-stone-100 text-sm">
                        @foreach ($professions as $profession)
                            <a href="{{ route('directory.index', array_filter(array_merge($filters, ['profession' => $profession->id]))) }}"
                               class="block px-5 py-2.5 font-medium transition hover:bg-stone-50 {{ ($filters['profession'] ?? null) == $profession->id ? 'text-emerald-700 bg-emerald-50/60' : 'text-stone-700' }}">
                                {{ $profession->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @php
                    $provinceOptions = $provinces->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'region_id' => $p->region_id])->values();
                    $cityOptions = $regions->flatMap(fn ($r) => $r->cities)
                        ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'province_id' => $c->province_id ?? null, 'region_id' => $c->region_id])
                        ->values();
                @endphp

                <div class="km-panel p-5 shadow-[0_14px_34px_rgba(66,87,103,0.08)]"
                     x-data="{
                         regionId: '{{ $filters['region'] ?? '' }}',
                         provinceId: '{{ $filters['province'] ?? '' }}',
                         provinces: @js($provinceOptions),
                         cities: @js($cityOptions),
                         get filteredProvinces() {
                             return this.regionId ? this.provinces.filter(p => p.region_id == this.regionId) : this.provinces;
                         },
                         get filteredCities() {
                             if (this.provinceId) return this.cities.filter(c => c.province_id == this.provinceId);
                             if (this.regionId) return this.cities.filter(c => c.region_id == this.regionId);
                             return this.cities;
                         }
                     }">
                    <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Filtri rapidi</h3>

                    <form method="GET" class="mt-3 space-y-3">
                        @if($filters['profession'] ?? null)
                            <input type="hidden" name="profession" value="{{ $filters['profession'] }}">
                        @endif

                        @if($filters['search'] ?? null)
                            <input type="hidden" name="search" value="{{ $filters['search'] }}">
                        @endif

                        <div>
                            <label class="mb-1 block text-xs font-medium text-stone-500">Regione</label>
                            <select name="region" class="km-input text-sm" x-model="regionId" @change="provinceId=''">
                                <option value="">Tutte le regioni</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" @selected(($filters['region'] ?? null) == $region->id)>
                                        {{ $region->name }}
                                    </option>
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
                                    <option value="{{ $chapter->id }}" @selected(($filters['chapter'] ?? null) == $chapter->id)>
                                        {{ $chapter->name }}
                                    </option>
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

            <section class="min-w-0 flex-1 space-y-4">

                <form method="GET" class="flex gap-2">
                    <input type="text" name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           class="km-input flex-1 bg-white shadow-sm"
                           placeholder="Nome, professione, categoria…">

                    <button type="submit" class="km-button-primary px-5 shrink-0">Cerca</button>

                    @if(array_filter($filters))
                        <a href="{{ route('directory.index') }}" class="km-button-secondary px-4 shrink-0">×</a>
                    @endif

                    @foreach(['profession','region','province','city','chapter'] as $fk)
                        @if($filters[$fk] ?? null)
                            <input type="hidden" name="{{ $fk }}" value="{{ $filters[$fk] }}">
                        @endif
                    @endforeach
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" style="column-gap:1rem; row-gap:4.5rem; margin-top:3.5rem;">

                    @forelse ($members as $member)
                        @php
                            $onepage = $member->user->memberOnepage ?? null;

                            $photoUrl = $member->avatarUrl() ?: null;

                            $profession = $member->professions->first()?->name
                                ?? $member->profession?->name
                                ?? $member->profession_other
                                ?? null;

                            $profileUrl = $onepage?->slug ? route('members.show', $onepage->slug) : '#';

                            $coverImage = $onepage?->coverImageUrl() ?: null;
                            $displayName = $member->user->name;

                            $rawWebsite = $member->website ?? null;
                            $websiteUrl = $rawWebsite
                                ? (\Illuminate\Support\Str::startsWith($rawWebsite, ['http://', 'https://'])
                                    ? $rawWebsite
                                    : 'https://' . $rawWebsite)
                                : null;

                            $locationLabel = collect([$member->city?->name, $member->region?->name])
                                ->filter()
                                ->join(', ');
                        @endphp

                        <article class="km-directory-card group transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_50px_rgba(39,54,67,0.14)]">

                                <div class="km-directory-avatar-wrap">
                                    <a href="{{ $profileUrl }}" class="km-directory-avatar-button" title="{{ $displayName }}">
                                        <div class="km-directory-avatar">
                                            @if ($photoUrl)
                                                <img src="{{ $photoUrl }}" alt="{{ $displayName }}">
                                            @else
                                                <span class="text-2xl font-bold text-slate-400">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </div>

                            <div class="km-directory-body">

                                <div class="min-w-0 text-center">
                                    <h2 class="text-[0.93rem] font-semibold leading-snug text-stone-950 truncate">
                                        <a href="{{ $profileUrl }}" class="transition hover:text-[color:var(--km-accent-strong)]">
                                            {{ $displayName }}
                                        </a>
                                    </h2>

                                    @if ($profession)
                                        <p class="mt-0.5 text-[0.74rem] leading-tight text-stone-400 truncate">
                                            {{ $profession }}
                                        </p>
                                    @endif


                                </div>

                                <div class="my-2 border-t border-stone-100"></div>

                                <div class="space-y-1.5">
                                    @if ($locationLabel !== '')
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-emerald-50 text-emerald-600">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 3.39-3.34 6.63-4.39 7.56a.85.85 0 01-1.12 0c-1.05-.93-4.39-4.17-4.39-7.56zM10 10.5A2.45 2.45 0 1010 5.6a2.45 2.45 0 000 4.9z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                            <span class="truncate text-[0.76rem] text-stone-600">{{ $locationLabel }}</span>
                                        </div>
                                    @endif

                                    @if ($member->show_phone && $member->phone)
                                        <a href="tel:{{ preg_replace('/\s+/', '', $member->phone) }}" class="flex items-center gap-2 min-w-0 group/link">
                                            <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-rose-50 text-rose-500">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M2 3.5A1.5 1.5 0 013.5 2h2.17a1.5 1.5 0 011.45 1.12l.65 2.6a1.5 1.5 0 01-.4 1.43l-1.2 1.2a11.04 11.04 0 005.31 5.31l1.2-1.2a1.5 1.5 0 011.43-.4l2.6.65A1.5 1.5 0 0118 14.33v2.17A1.5 1.5 0 0116.5 18h-1C8.596 18 2 11.404 2 3.5z"/>
                                                </svg>
                                            </span>
                                            <span class="truncate text-[0.76rem] text-stone-600 group-hover/link:text-[color:var(--km-accent-strong)] transition">
                                                {{ $member->phone }}
                                            </span>
                                        </a>
                                    @endif

                                    @if ($member->show_email)
                                        <a href="mailto:{{ $member->user->email }}" class="flex items-center gap-2 min-w-0 group/link">
                                            <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-sky-50 text-sky-500">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M2.94 6.34A2 2 0 014.6 5.5h10.8a2 2 0 011.66.84L10 10.94 2.94 6.34z"/>
                                                    <path d="M2 7.56V13.5a2 2 0 002 2h12a2 2 0 002-2V7.56l-7.45 4.85a1 1 0 01-1.1 0L2 7.56z"/>
                                                </svg>
                                            </span>
                                            <span class="truncate text-[0.76rem] text-stone-600 group-hover/link:text-[color:var(--km-accent-strong)] transition">
                                                {{ $member->user->email }}
                                            </span>
                                        </a>
                                    @endif

                                    @if ($websiteUrl)
                                        <a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2 min-w-0 group/link">
                                            <span class="inline-flex items-center justify-center w-5 h-5 shrink-0 rounded-full bg-lime-50 text-lime-600">
                                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm5.25-8a6.98 6.98 0 00-.9-3.42h-2.08c.24 1.03.38 2.2.4 3.42h2.58zm-4.58 0a16.2 16.2 0 00-.45-3.42H9.78c-.23 1.02-.39 2.19-.45 3.42h1.34zm-1.34 2c.06 1.23.22 2.4.45 3.42h.44c.23-1.02.39-2.19.45-3.42H9.33zm-2 0H4.75a6.98 6.98 0 00.9 3.42h2.08a17.37 17.37 0 01-.4-3.42zm0-2c.02-1.22.16-2.39.4-3.42H5.65A6.98 6.98 0 004.75 10h2.58zm4.92 5.1a5 5 0 001.72-1.68h-1.36c-.11.61-.23 1.18-.36 1.68zm1.72-8.52a5 5 0 00-1.72-1.68c.13.5.25 1.07.36 1.68h1.36zM8.09 4.9A5 5 0 006.37 6.58h1.36c.11-.61.23-1.18.36-1.68zm-1.72 8.52a5 5 0 001.72 1.68c-.13-.5-.25-1.07-.36-1.68H6.37z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                            <span class="truncate text-[0.76rem] text-stone-600 group-hover/link:text-[color:var(--km-accent-strong)] transition">
                                                {{ preg_replace('/^https?:\/\//', '', rtrim($rawWebsite, '/')) }}
                                            </span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 border-t px-4 pb-3 pt-2.5" style="border-color:rgba(70,93,112,.10); margin-top:auto;">
                                <a href="{{ $profileUrl }}" title="Vedi profilo"
                                   class="km-directory-action-button km-directory-action-button-primary"
                                   style="width:2.5rem;height:2.5rem;">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                    </svg>
                                </a>

                                @if(auth()->check() && auth()->id() !== $member->user_id)
                                    <form method="POST" action="{{ route('conversations.start') }}" style="margin:0;">
                                        @csrf
                                        <input type="hidden" name="recipient_id" value="{{ $member->user_id }}">
                                        <button type="submit" title="Invia messaggio"
                                                class="km-directory-action-button"
                                                style="width:2.5rem;height:2.5rem;">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
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
                                                style="width:2.5rem;height:2.5rem;">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3zm13.5-9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zm-11 0a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
                                            </svg>
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

                {{-- Barra info + selettore "per pagina" --}}
                <div class="km-panel flex flex-wrap items-center justify-between gap-3 px-4 py-2">
                    <span class="text-sm text-stone-500">
                        {{ $members->total() }} {{ $members->total() === 1 ? 'membro trovato' : 'membri trovati' }}
                    </span>

                    <form method="GET" class="flex items-center gap-2">
                        {{-- Preserva tutti i filtri attivi --}}
                        @foreach(['search','profession','region','province','city','chapter'] as $_fk)
                            @if($filters[$_fk] ?? null)
                                <input type="hidden" name="{{ $_fk }}" value="{{ $filters[$_fk] }}">
                            @endif
                        @endforeach

                        <label for="per_page_select" class="text-sm text-stone-500 whitespace-nowrap">Per pagina:</label>
                        <select id="per_page_select" name="per_page"
                                onchange="this.form.submit()"
                                class="km-input py-1 text-sm">
                            @foreach($perPageOptions as $_opt)
                                <option value="{{ $_opt }}" {{ $perPage === $_opt ? 'selected' : '' }}>{{ $_opt }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="km-panel p-4">
                    {{ $members->links() }}
                </div>

            </section>
        </div>
    </div>
</div>

</x-app-layout>
