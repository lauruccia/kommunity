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
            <span class="min-w-0 break-words">{{ $profile->city?->name ?? 'Città n.d.' }}{{ $profile->region?->name ? ', '.$profile->region?->name : '' }}</span>
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

        @if ($profile->linkedin_url)
            <a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 truncate text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-700">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </span>
                <span class="truncate">LinkedIn</span>
            </a>
        @endif

        @if ($profile->instagram_url)
            <a href="{{ $profile->instagram_url }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 truncate text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-pink-50 text-pink-500">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                    </svg>
                </span>
                <span class="truncate">Instagram</span>
            </a>
        @endif

        @if ($profile->facebook_url)
            <a href="{{ $profile->facebook_url }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 truncate text-[color:var(--km-accent-strong)]">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </span>
                <span class="truncate">Facebook</span>
            </a>
        @endif

    </div>
</div>

{{-- Presenza business --}}
@php
    // Fallback: M2M (categories) → legacy FK (category) → "Da definire"
    $categoryNames = $profile->categories->pluck('name')->filter()->values();
    if ($categoryNames->isEmpty() && $profile->category) {
        $categoryNames = collect([$profile->category->name])->filter()->values();
    }
@endphp
<div class="km-panel p-6">
    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Presenza business</h3>
    <div class="mt-4 space-y-3 text-sm text-stone-700">
        <div>
            <span class="font-semibold">Categorie:</span>
            @if ($categoryNames->isNotEmpty())
                {{ $categoryNames->join(', ') }}
            @else
                Da definire
            @endif
        </div>
        @if ($profile->sector)
        <div><span class="font-semibold">Settore:</span> {{ $profile->sector->name }}</div>
        @endif
        <div><span class="font-semibold">Pianeta:</span> {{ $profile->chapter?->name ?? 'Non assegnato' }}</div>
        <div><span class="font-semibold">Contatto preferito:</span> {{ $profile->preferred_contact_method?->label() ?? 'Email' }}</div>
    </div>
</div>

{{-- Video --}}
@if ($profile->hasVideo())
<div class="km-panel overflow-hidden p-0">
    @if ($canViewIntroVideo ?? false)
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
    @else
        @php
            $viewerId = auth()->id();
            $isPendingOutgoing = $videoAccessRequest
                && $videoAccessRequest->status === 'pending'
                && $videoAccessRequest->requester_id === $viewerId;
            $isPendingIncoming = $videoAccessRequest
                && $videoAccessRequest->status === 'pending'
                && $videoAccessRequest->recipient_id === $viewerId;
        @endphp

        <div class="space-y-4 p-6">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Videopresentazione</h3>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                    Questo video e' privato. L'accesso si sblocca solo con richiesta accettata e scambio reciproco.
                </p>
            </div>

            @if ($isPendingIncoming)
                <div class="grid gap-2">
                    <form method="POST" action="{{ route('profile-video-access.respond', $videoAccessRequest) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit" class="km-button-primary w-full justify-center">Accetta scambio video</button>
                    </form>

                    <form method="POST" action="{{ route('profile-video-access.respond', $videoAccessRequest) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="declined">
                        <button type="submit" class="km-button-secondary w-full justify-center">Rifiuta</button>
                    </form>
                </div>
            @elseif ($isPendingOutgoing)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Richiesta inviata. In attesa di conferma.
                </div>
            @else
                <form method="POST" action="{{ route('profile-video-access.store', $user) }}">
                    @csrf
                    <button type="submit" class="km-button-primary w-full justify-center">Richiedi accesso video</button>
                </form>
            @endif
        </div>
    @endif
</div>
@endif

{{-- Referenze dalla community --}}
@if (!empty($publicEndorsements) && $publicEndorsements->isNotEmpty())
@php $sidebarEndorsements = $publicEndorsements->sortByDesc(fn($r) => in_array($r->priority, ['1','2','3','4','5'], true) ? (int)$r->priority : 3)->take(3); @endphp
<div class="km-panel p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Referenze</h3>
        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
            {{ $publicEndorsements->count() }} {{ $publicEndorsements->count() === 1 ? 'referenza' : 'referenze' }}
        </span>
    </div>
    <div class="mt-4 space-y-3">
        @foreach ($sidebarEndorsements as $ref)
        @php
            $refStars = match(true) {
                in_array($ref->priority, ['1','2','3','4','5'], true) => (int) $ref->priority,
                $ref->priority === 'high' => 5,
                $ref->priority === 'low'  => 1,
                default => 3,
            };
        @endphp
        <div class="rounded-xl border border-stone-100 bg-stone-50 p-3">
            <div class="flex items-start gap-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-700">
                    {{ strtoupper(substr($ref->sender?->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-xs font-semibold text-stone-700 truncate max-w-[100px]">{{ $ref->sender?->name ?? 'Membro' }}</span>
                        <span class="flex gap-0.5">
                            @for ($si = 1; $si <= 5; $si++)
                                <svg class="h-3 w-3 {{ $si <= $refStars ? 'text-yellow-400' : 'text-stone-200' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs font-medium text-stone-600 line-clamp-2">{{ $ref->title }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @if ($publicEndorsements->count() > 3)
    <a href="{{ route('members.referrals', $onepage->slug) }}"
       class="mt-4 flex items-center gap-1.5 text-sm font-semibold text-[color:var(--km-accent-strong)] hover:underline">
        Leggi tutte le {{ $publicEndorsements->count() }} referenze
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
    </a>
    @else
    <a href="{{ route('members.referrals', $onepage->slug) }}"
       class="mt-4 flex items-center gap-1.5 text-sm font-semibold text-[color:var(--km-accent-strong)] hover:underline">
        Vedi tutte
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
    </a>
    @endif
</div>
@endif
