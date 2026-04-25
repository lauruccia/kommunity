{{--
    Partial: contenuto sidebar profilo membro.
    Usato sia nel <aside> desktop che nel drawer mobile.
    Variabili richieste: $user, $profile, $whatsappUrl
--}}

{{-- Contatti --}}
<div class="km-panel p-6">
    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Contatti</h3>
    <div class="mt-4 space-y-3 text-sm text-stone-700">

        <div class="flex items-center gap-2.5">
            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 3.39-3.34 6.63-4.39 7.56a.85.85 0 01-1.12 0c-1.05-.93-4.39-4.17-4.39-7.56zM10 10.5A2.45 2.45 0 1010 5.6a2.45 2.45 0 000 4.9z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="min-w-0 break-words">{{ $profile->city?->name ?? 'Città n.d.' }}{{ $profile->region?->name ? ', '.$profile->region->name : '' }}</span>
        </div>

        @if ($profile->show_email)
            <a href="mailto:{{ $user->email }}" class="flex items-center gap-2.5 text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-500">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M2.94 6.34A2 2 0 014.6 5.5h10.8a2 2 0 011.66.84L10 10.94 2.94 6.34z"/>
                        <path d="M2 7.56V13.5a2 2 0 002 2h12a2 2 0 002-2V7.56l-7.45 4.85a1 1 0 01-1.1 0L2 7.56z"/>
                    </svg>
                </span>
                <span class="min-w-0 break-all">{{ $user->email }}</span>
            </a>
        @endif

        @if ($profile->show_phone && $profile->phone)
            <a href="tel:{{ $profile->phone }}" class="flex items-center gap-2.5 text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M2 3.5A1.5 1.5 0 013.5 2h2.17a1.5 1.5 0 011.45 1.12l.65 2.6a1.5 1.5 0 01-.4 1.43l-1.2 1.2a11.04 11.04 0 005.31 5.31l1.2-1.2a1.5 1.5 0 011.43-.4l2.6.65A1.5 1.5 0 0118 14.33v2.17A1.5 1.5 0 0116.5 18h-1C8.596 18 2 11.404 2 3.5z"/>
                    </svg>
                </span>
                <span class="min-w-0 break-words">{{ $profile->phone }}</span>
            </a>
        @endif

        @if ($profile->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number)
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M19.05 4.91A9.82 9.82 0 0012.03 2C6.56 2 2.12 6.43 2.12 11.9c0 1.75.46 3.46 1.33 4.96L2 22l5.29-1.39a9.9 9.9 0 004.74 1.2h.01c5.47 0 9.9-4.44 9.9-9.91a9.83 9.83 0 00-2.89-6.99zm-7.02 15.22h-.01a8.23 8.23 0 01-4.19-1.14l-.3-.18-3.14.82.84-3.06-.2-.31a8.2 8.2 0 01-1.26-4.36c0-4.53 3.69-8.22 8.24-8.22a8.16 8.16 0 015.82 2.41 8.16 8.16 0 012.4 5.82c0 4.54-3.69 8.22-8.2 8.22zm4.5-6.16c-.25-.12-1.47-.72-1.7-.8-.23-.09-.39-.12-.56.12-.16.25-.64.8-.78.96-.14.17-.28.19-.53.07-.25-.12-1.03-.38-1.96-1.22-.73-.64-1.22-1.43-1.36-1.67-.14-.24-.01-.37.11-.49.11-.11.25-.28.37-.42.12-.14.16-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.86-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.65.31-.22.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.17 1.73 2.64 4.19 3.7.58.25 1.03.39 1.38.5.58.18 1.11.16 1.53.1.47-.07 1.47-.6 1.68-1.19.21-.59.21-1.09.14-1.19-.06-.1-.22-.16-.47-.28z"/>
                    </svg>
                </span>
                <span class="min-w-0 break-words">{{ $profile->whatsapp_number }}</span>
            </a>
        @endif

        @if ($profile->website)
            <a href="{{ $profile->website }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 truncate text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-lime-50 text-lime-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm5.25-8a6.98 6.98 0 00-.9-3.42h-2.08c.24 1.03.38 2.2.4 3.42h2.58zm-4.58 0a16.2 16.2 0 00-.45-3.42H9.78c-.23 1.02-.39 2.19-.45 3.42h1.34zm-1.34 2c.06 1.23.22 2.4.45 3.42h.44c.23-1.02.39-2.19.45-3.42H9.33zm-2 0H4.75a6.98 6.98 0 00.9 3.42h2.08a17.37 17.37 0 01-.4-3.42zm0-2c.02-1.22.16-2.39.4-3.42H5.65A6.98 6.98 0 004.75 10h2.58zm4.92 5.1a5 5 0 001.72-1.68h-1.36c-.11.61-.23 1.18-.36 1.68zm1.72-8.52a5 5 0 00-1.72-1.68c.13.5.25 1.07.36 1.68h1.36zM8.09 4.9A5 5 0 006.37 6.58h1.36c.11-.61.23-1.18.36-1.68zm-1.72 8.52a5 5 0 001.72 1.68c-.13-.5-.25-1.07-.36-1.68H6.37z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="truncate">{{ $profile->website }}</span>
            </a>
        @endif

    </div>
</div>

{{-- Presenza business --}}
<div class="km-panel p-6">
    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Presenza business</h3>
    <div class="mt-4 space-y-3 text-sm text-stone-700">
        <div>
            <span class="font-semibold">Categorie:</span>
            @if ($profile->categories->isNotEmpty())
                {{ $profile->categories->pluck('name')->join(', ') }}
            @else
                Da definire
            @endif
        </div>
        <div><span class="font-semibold">Pianeta:</span> {{ $profile->chapter?->name ?? 'Non assegnato' }}</div>
        <div><span class="font-semibold">Invitato da:</span> {{ $user->invited_by_name ?: 'Non indicato' }}</div>
        <div><span class="font-semibold">Contatto preferito:</span> {{ $profile->preferred_contact_method?->label() ?? 'Email' }}</div>
    </div>
</div>

{{-- Video --}}
@if ($profile->hasVideo())
<div class="km-panel overflow-hidden p-0">
    @if ($profile->videoEmbedUrl())
        <div class="mx-auto w-full bg-black {{ $profile->prefersPortraitVideo() ? 'max-w-[520px]' : '' }}" style="aspect-ratio:{{ $profile->prefersPortraitVideo() ? '9/16' : '16/9' }};">
            <iframe src="{{ $profile->videoEmbedUrl() }}"
                    class="h-full w-full bg-black"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>
        </div>
    @elseif ($profile->introVideoUrl())
        <div x-data="{ portrait: false }" class="bg-black">
            <div class="mx-auto w-full bg-black" :class="portrait ? 'max-w-[520px]' : 'max-w-full'" :style="portrait ? 'aspect-ratio:9/16' : 'aspect-ratio:16/9'">
                <video controls playsinline class="h-full w-full bg-black object-cover"
                       @loadedmetadata="portrait = $event.target.videoHeight > $event.target.videoWidth">
                    <source src="{{ $profile->introVideoUrl() }}">
                </video>
            </div>
        </div>
    @endif
</div>
@endif
