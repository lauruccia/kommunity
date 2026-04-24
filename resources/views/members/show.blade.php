<x-app-layout>
    <x-slot name="header">
        @php
            $heroBackground = $onepage->coverImageUrl()
                ? "background-image:linear-gradient(90deg,rgba(38,52,63,0.82),rgba(72,97,81,0.58)),url('".$onepage->coverImageUrl()."'); background-size:cover; background-position:center;"
                : "background:linear-gradient(135deg,#425767 0%,#4f6778 58%,#5b7d4b 100%);";
        @endphp
        <div class="rounded-[2rem] p-6 text-white shadow-[0_22px_60px_rgba(66,87,103,0.22)]" style="{{ $heroBackground }}">
            @php
                $whatsappUrl = null;

                if ($profile->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number) {
                    $whatsappUrl = 'https://wa.me/'.preg_replace('/\D+/', '', $profile->whatsapp_number).'?text='.urlencode('Ciao '.$user->name.', ti contatto dalla tua pagina su Kommunity.');
                }
            @endphp
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/70">Pagina personale</p>
                    <h1 class="mt-3 font-serif text-3xl font-semibold sm:text-4xl lg:text-5xl">{{ $onepage->hero_title ?: $user->name }}</h1>
                    <p class="mt-3 text-base text-white/80 lg:text-lg">{{ $onepage->hero_subtitle ?: 'Profilo professionale membro Kommunity' }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('conversations.start') }}">
                        @csrf
                        <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                        <input type="hidden" name="message" value="Ciao {{ $user->name }}, ti contatto dalla tua pagina personale su Kommunity.">
                        <button type="submit" class="km-button-secondary border-white/25 bg-white/10 text-white hover:bg-white/20">Messaggio diretto</button>
                    </form>
                    <form method="POST" action="{{ route('one-to-ones.store') }}">
                        @csrf
                        <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                        <input type="hidden" name="meeting_mode" value="online">
                        <input type="hidden" name="goal" value="Vorrei approfondire il tuo profilo e valutare una collaborazione.">
                        <button type="submit" class="km-button-primary bg-white text-stone-950 hover:bg-stone-100">Prenota one-to-one</button>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            @php
                $tabs = [
                    'profile' => 'Profilo',
                    'kommunity' => 'Kommunity',
                    'collaborations' => 'Collaborazioni',
                ];
            @endphp
            <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="space-y-6">
                <div class="km-panel overflow-hidden p-0">
                    <div class="relative p-6 pt-8 text-center">
                        @if ($profile->avatarUrl() || $profile->logoUrl())
                            <img src="{{ $profile->avatarUrl() ?: $profile->logoUrl() }}" alt="{{ $user->name }}" class="mx-auto h-24 w-24 rounded-full border-4 border-white object-cover shadow-[0_12px_28px_rgba(38,52,63,0.16)]">
                        @else
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-stone-900 text-4xl font-semibold text-white shadow-[0_12px_28px_rgba(38,52,63,0.16)]">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <h2 class="mt-4 text-2xl font-semibold text-stone-950">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-stone-500">{{ $profile->company_name ?: 'Attivita\' professionale' }}</p>
                        <div class="mt-4 text-sm text-stone-600">
                            @if ($profile->professions->isNotEmpty())
                                {{ $profile->professions->pluck('name')->join(', ') }}
                            @else
                                {{ $profile->profession?->name ?? 'Professione da definire' }}
                            @endif
                        </div>
                    </div>
                    <div class="border-t border-stone-200 px-6 py-5">
                        <div class="space-y-3 text-sm text-stone-700">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-500">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 3.39-3.34 6.63-4.39 7.56a.85.85 0 01-1.12 0c-1.05-.93-4.39-4.17-4.39-7.56zM10 10.5A2.45 2.45 0 1010 5.6a2.45 2.45 0 000 4.9z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <span>{{ $profile->city?->name ?? 'Citta\' n.d.' }}{{ $profile->region?->name ? ', '.$profile->region->name : '' }}</span>
                            </div>
                            @if ($profile->show_email)
                                <a href="mailto:{{ $user->email }}" class="flex items-center gap-2.5 text-[color:var(--km-accent-strong)]">
                                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-500">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M2.94 6.34A2 2 0 014.6 5.5h10.8a2 2 0 011.66.84L10 10.94 2.94 6.34z"/>
                                            <path d="M2 7.56V13.5a2 2 0 002 2h12a2 2 0 002-2V7.56l-7.45 4.85a1 1 0 01-1.1 0L2 7.56z"/>
                                        </svg>
                                    </span>
                                    <span>{{ $user->email }}</span>
                                </a>
                            @endif
                            @if ($profile->show_phone && $profile->phone)
                                <a href="tel:{{ $profile->phone }}" class="flex items-center gap-2.5 text-[color:var(--km-accent-strong)]">
                                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M2 3.5A1.5 1.5 0 013.5 2h2.17a1.5 1.5 0 011.45 1.12l.65 2.6a1.5 1.5 0 01-.4 1.43l-1.2 1.2a11.04 11.04 0 005.31 5.31l1.2-1.2a1.5 1.5 0 011.43-.4l2.6.65A1.5 1.5 0 0118 14.33v2.17A1.5 1.5 0 0116.5 18h-1C8.596 18 2 11.404 2 3.5z"/>
                                        </svg>
                                    </span>
                                    <span>{{ $profile->phone }}</span>
                                </a>
                            @endif
                            @if ($profile->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number)
                                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-[color:var(--km-accent-strong)]">
                                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M19.05 4.91A9.82 9.82 0 0012.03 2C6.56 2 2.12 6.43 2.12 11.9c0 1.75.46 3.46 1.33 4.96L2 22l5.29-1.39a9.9 9.9 0 004.74 1.2h.01c5.47 0 9.9-4.44 9.9-9.91a9.83 9.83 0 00-2.89-6.99zm-7.02 15.22h-.01a8.23 8.23 0 01-4.19-1.14l-.3-.18-3.14.82.84-3.06-.2-.31a8.2 8.2 0 01-1.26-4.36c0-4.53 3.69-8.22 8.24-8.22a8.16 8.16 0 015.82 2.41 8.16 8.16 0 012.4 5.82c0 4.54-3.69 8.22-8.2 8.22zm4.5-6.16c-.25-.12-1.47-.72-1.7-.8-.23-.09-.39-.12-.56.12-.16.25-.64.8-.78.96-.14.17-.28.19-.53.07-.25-.12-1.03-.38-1.96-1.22-.73-.64-1.22-1.43-1.36-1.67-.14-.24-.01-.37.11-.49.11-.11.25-.28.37-.42.12-.14.16-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.86-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.65.31-.22.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.17 1.73 2.64 4.19 3.7.58.25 1.03.39 1.38.5.58.18 1.11.16 1.53.1.47-.07 1.47-.6 1.68-1.19.21-.59.21-1.09.14-1.19-.06-.1-.22-.16-.47-.28z"/>
                                        </svg>
                                    </span>
                                    <span>{{ $profile->whatsapp_number }}</span>
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
                </div>

                @if ($profile->hasVideo())
                <div class="km-panel overflow-hidden p-0">
                    @if ($profile->videoEmbedUrl())
                        <div class="mx-auto w-full {{ $profile->prefersPortraitVideo() ? 'max-w-[420px]' : '' }}" style="aspect-ratio:{{ $profile->prefersPortraitVideo() ? '9/16' : '16/9' }};">
                            <iframe src="{{ $profile->videoEmbedUrl() }}"
                                    class="h-full w-full"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                            </iframe>
                        </div>
                    @elseif ($profile->introVideoUrl())
                        <div x-data="{ portrait: false }" class="p-4 sm:p-6">
                            <div class="mx-auto w-full" :class="portrait ? 'max-w-[420px]' : 'max-w-full'" :style="portrait ? 'aspect-ratio:9/16' : 'aspect-ratio:16/9'">
                                <video controls playsinline class="h-full w-full rounded-[1.4rem] bg-black object-contain"
                                       @loadedmetadata="portrait = $event.target.videoHeight > $event.target.videoWidth">
                                    <source src="{{ $profile->introVideoUrl() }}">
                                </video>
                            </div>
                        </div>
                    @endif
                </div>
                @endif

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

                <div class="km-panel p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Azioni rapide</h3>
                    <div class="mt-4 flex flex-col gap-3">
                        <a href="{{ route('directory.index') }}" class="km-button-secondary w-full">Torna alla directory</a>
                        <a href="{{ route('one-to-ones.index', ['member' => $user->id]) }}" class="km-button-secondary w-full">Gestisci one-to-one</a>
                    </div>
                </div>
            </aside>

            <section class="space-y-6">
                <div class="km-panel overflow-hidden p-0">
                    <div class="relative h-[340px] bg-[linear-gradient(135deg,#425767_0%,#d7e3d1_100%)]" @if($onepage->coverImageUrl()) style="background-image:url('{{ $onepage->coverImageUrl() }}'); background-size:cover; background-position:center;" @endif>
                        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(22,31,41,0.06),rgba(22,31,41,0.26))]"></div>
                    </div>
                </div>

                <div class="km-panel p-6">
                    <div class="flex items-center justify-between border-b border-stone-200 pb-4">
                        <div class="flex gap-6 text-sm font-medium text-stone-600">
                            @foreach ($tabs as $tabKey => $tabLabel)
                                <a href="{{ route('members.show', ['slug' => $onepage->slug, 'tab' => $tabKey]) }}" class="{{ $currentTab === $tabKey ? 'border-b-2 border-[color:var(--km-accent)] pb-3 text-stone-950' : 'pb-3 text-stone-500 transition hover:text-stone-900' }}">
                                    {{ $tabLabel }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-6 pt-6">
                        @if ($currentTab === 'kommunity')
                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Presenza nella kommunity</h2>
                                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                    <div class="rounded-[1.6rem] bg-stone-100 p-5">
                                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Pianeta</p>
                                        <p class="mt-3 text-lg font-semibold text-stone-950">{{ $profile->chapter?->name ?? 'Nessun capitolo assegnato' }}</p>
                                        <p class="mt-2 text-sm leading-7 text-stone-600">{{ $profile->chapter?->description ?: 'Il membro non e\' ancora collegato a un capitolo territoriale.' }}</p>
                                    </div>
                                    <div class="rounded-[1.6rem] bg-stone-100 p-5">
                                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Networking</p>
                                        <p class="mt-3 text-sm leading-7 text-stone-700">{{ $profile->networking_goals ?: 'Disponibile a creare sinergie, referral qualificati e nuove collaborazioni nella kommunity.' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Tipologie che desidera conoscere</h2>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @forelse ($profile->companyInterestTypes as $companyInterestType)
                                        <span class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-700">{{ $companyInterestType->name }}</span>
                                    @empty
                                        <div class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">
                                            Nessuna tipologia selezionata.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Discussioni recenti</h2>
                                <div class="mt-2 grid gap-4">
                                    @forelse ($communityThreads as $thread)
                                        <a href="{{ route('forum.show', $thread) }}" class="rounded-[1.6rem] border border-stone-200 bg-white p-5 transition hover:bg-stone-50">
                                            <p class="text-xs uppercase tracking-[0.18em] text-stone-500">{{ $thread->category?->name ?? 'Kommunity' }}</p>
                                            <p class="mt-2 text-lg font-semibold text-stone-950">{{ $thread->title }}</p>
                                            <p class="mt-2 text-sm text-stone-600">{{ $thread->excerpt }}</p>
                                        </a>
                                    @empty
                                        <div class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">
                                            Nessuna discussione pubblicata da questo membro.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @elseif ($currentTab === 'collaborations')
                            <div>
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <h2 class="font-serif text-3xl font-semibold text-stone-950">Storico collaborazioni</h2>
                                        <p class="mt-3 text-sm leading-7 text-stone-600">One-to-one e referenze tra te e questo membro.</p>
                                    </div>
                                    <a href="{{ route('one-to-ones.index', ['member' => $user->id]) }}" class="km-button-secondary">Apri gestione one-to-one</a>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-serif text-2xl font-semibold text-stone-950">One-to-one condivisi</h3>
                                <div class="mt-4 grid gap-4">
                                    @forelse ($sharedOneToOnes as $oneToOne)
                                        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">
                                                    {{ $oneToOne->requester_id === auth()->id() ? 'Inviato da te' : 'Ricevuto da te' }}
                                                </span>
                                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-800">
                                                    {{ $oneToOne->status->label() }}
                                                </span>
                                            </div>
                                            <p class="mt-3 text-sm leading-7 text-stone-700">{{ $oneToOne->goal }}</p>
                                            <p class="mt-2 text-sm text-stone-500">{{ optional($oneToOne->requested_at)->format('d/m/Y H:i') ?: 'Data da confermare' }}</p>
                                        </div>
                                    @empty
                                        <div class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">
                                            Nessun one-to-one registrato tra te e questo membro.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h3 class="font-serif text-2xl font-semibold text-stone-950">Referenze condivise</h3>
                                <div class="mt-4 grid gap-4">
                                    @forelse ($sharedReferrals as $referral)
                                        <div class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">
                                                    {{ $referral->sender_id === auth()->id() ? 'Inviata da te' : 'Ricevuta da te' }}
                                                </span>
                                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-stone-700">
                                                    {{ $referral->status->label() }}
                                                </span>
                                            </div>
                                            <p class="mt-3 text-lg font-semibold text-stone-950">{{ $referral->title }}</p>
                                            <p class="mt-2 text-sm leading-7 text-stone-700">{{ $referral->description }}</p>
                                        </div>
                                    @empty
                                        <div class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 p-5 text-sm text-stone-500">
                                            Nessuna referenza condivisa tra te e questo membro.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Chi sono</h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">{{ $onepage->about_text ?: ($profile->bio ?: 'Profilo professionale in fase di completamento.') }}</p>
                            </div>

                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Servizi e competenze</h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">{{ $onepage->services_text ?: ($profile->services ?: 'Questa sezione raccogliera\' servizi e competenze professionali del membro.') }}</p>
                                @if ($profile->skills)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach (collect(explode(',', $profile->skills))->map(fn ($item) => trim($item))->filter() as $skill)
                                            <span class="rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-700">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Obiettivi di networking</h2>
                                <p class="mt-2 text-base leading-8 text-stone-700">{{ $profile->networking_goals ?: 'Disponibile a creare sinergie, referral qualificati e nuove collaborazioni nella kommunity.' }}</p>
                            </div>

                            <div>
                                <h2 class="font-serif text-3xl font-semibold text-stone-950">Gallery</h2>
                                @if ($user->memberGalleryImages->isNotEmpty())
                                    @php $galleryUrls = $user->memberGalleryImages->map(fn($i) => $i->imageUrl())->values()->all(); @endphp
                                    {{-- Lightbox Alpine.js --}}
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
                                        {{-- Griglia thumbnail cliccabili --}}
                                        <div class="mt-2 grid grid-cols-3 gap-3 xl:grid-cols-4">
                                            @foreach ($user->memberGalleryImages as $idx => $galleryImage)
                                                <button
                                                    type="button"
                                                    @click="current = {{ $idx }}; open = true"
                                                    class="group overflow-hidden rounded-[1.2rem] border border-stone-200 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-[color:var(--km-accent)]"
                                                >
                                                    <img src="{{ $galleryImage->imageUrl() }}"
                                                         alt="{{ $user->name }}"
                                                         class="h-36 w-full object-cover transition duration-300 group-hover:scale-105">
                                                </button>
                                            @endforeach
                                        </div>

                                        {{-- Overlay lightbox --}}
                                        <div
                                            x-show="open"
                                            x-cloak
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4"
                                            @click.self="open = false"
                                        >
                                            {{-- Immagine corrente --}}
                                            <div class="relative flex max-h-[90vh] max-w-5xl w-full items-center justify-center">
                                                <template x-for="(url, i) in images" :key="i">
                                                    <img
                                                        x-show="current === i"
                                                        :src="url"
                                                        x-transition:enter="transition ease-out duration-150"
                                                        x-transition:enter-start="opacity-0 scale-95"
                                                        x-transition:enter-end="opacity-100 scale-100"
                                                        class="max-h-[80vh] max-w-full rounded-[1.4rem] object-contain shadow-2xl"
                                                        alt="Gallery"
                                                    >
                                                </template>
                                            </div>

                                            {{-- Chiudi --}}
                                            <button @click="open = false"
                                                    class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                                            </button>

                                            {{-- Prev --}}
                                            <button @click="prev()" x-show="images.length > 1"
                                                    class="absolute left-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 010 1.06L8.06 10l3.72 3.72a.75.75 0 11-1.06 1.06l-4.25-4.25a.75.75 0 010-1.06l4.25-4.25a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                                            </button>

                                            {{-- Next --}}
                                            <button @click="next()" x-show="images.length > 1"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur hover:bg-white/30">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 011.06 0l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                                            </button>

                                            {{-- Dot indicator --}}
                                            <div x-show="images.length > 1"
                                                 class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
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
                                        La gallery verra' popolata dal membro con immagini dei propri progetti e attivita'.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </section>
            </div>
        </div>
    </div>
@push('modals')
@endpush
</x-app-layout>
