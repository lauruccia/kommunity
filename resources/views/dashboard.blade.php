<x-app-layout>
    <x-slot name="header">
        <div class="km-panel p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Dashboard membro</p>
                    <h1 class="mt-2 text-2xl font-semibold text-stone-950 lg:text-3xl">Gestisci presenza, relazioni e attivit&agrave; nella community</h1>
                    <p class="mt-2 text-sm leading-6 text-stone-500">
                        Profilo business, mini sito personale, incontri one-to-one, eventi, forum e messaggi sono ora concentrati in un'unica area operativa.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('profile.edit') }}" class="km-button-secondary">Completa onboarding</a>

                    @if(optional($user->memberOnepage)->slug)
                        <a href="{{ route('members.show', $user->memberOnepage->slug) }}" class="km-button-primary">
                            Apri pagina personale
                        </a>
                    @else
                        <span class="km-button-primary opacity-60 cursor-not-allowed">
                            Pagina personale non disponibile
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell space-y-6">
            <section class="grid gap-5 lg:grid-cols-4">
                <article class="km-panel p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Onboarding</p>
                    <p class="mt-3 text-2xl font-semibold text-stone-950">
                        {{ optional($user->memberProfile)->onboarding_completed ? '100%' : 'in corso' }}
                    </p>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ optional($user->memberProfile)->onboarding_completed ? 'Profilo pronto per la directory.' : 'Completa i dati business e le preferenze di networking.' }}
                    </p>
                </article>

                <article class="km-panel p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">One-to-one ricevuti</p>
                    <p class="mt-3 text-2xl font-semibold text-stone-950">{{ $receivedOneToOnes->count() }}</p>
                    <p class="mt-2 text-sm text-stone-600">Richieste recenti da membri della community.</p>
                </article>

                <article class="km-panel p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Eventi in arrivo</p>
                    <p class="mt-3 text-2xl font-semibold text-stone-950">{{ $upcomingEvents->count() }}</p>
                    <p class="mt-2 text-sm text-stone-600">Appuntamenti pubblicati e aperti alla registrazione.</p>
                </article>

                <article class="km-panel p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Messaggi recenti</p>
                    <p class="mt-3 text-2xl font-semibold text-stone-950">{{ $recentMessages->count() }}</p>
                    <p class="mt-2 text-sm text-stone-600">Conversazioni attive con altri membri.</p>
                </article>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-6">
                    <div class="km-panel p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Profilo business</p>
                                <h2 class="mt-1 text-xl font-semibold text-stone-950">{{ $user->name }}</h2>
                                <p class="text-sm text-stone-600">
                                    {{ optional($user->memberProfile)->company_name ?: 'Azienda da inserire' }}
                                </p>
                            </div>

                            <div class="flex h-20 w-20 items-center justify-center rounded-[2rem] bg-stone-900 text-3xl font-semibold text-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-[1.6rem] bg-stone-100 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Categoria</p>
                                <p class="mt-2 text-sm font-medium text-stone-900">
                                    {{ optional(optional($user->memberProfile)->category)->name ?? 'Da definire' }}
                                </p>
                            </div>

                            <div class="rounded-[1.6rem] bg-stone-100 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Citta'</p>
                                <p class="mt-2 text-sm font-medium text-stone-900">
                                    {{ optional(optional($user->memberProfile)->city)->name ?? 'Da indicare' }}
                                </p>
                            </div>

                            <div class="rounded-[1.6rem] bg-stone-100 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Contatto preferito</p>
                                <p class="mt-2 text-sm font-medium text-stone-900">
                                    {{ optional(optional($user->memberProfile)->preferred_contact_method)->label() ?? 'Email' }}
                                </p>
                            </div>

                            <div class="rounded-[1.6rem] bg-stone-100 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Visibile in directory</p>
                                <p class="mt-2 text-sm font-medium text-stone-900">
                                    {{ optional($user->memberProfile)->is_visible_in_directory ? 'Si' : 'No' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="km-panel p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">One-to-one ricevuti</p>
                                <h2 class="mt-1 text-xl font-semibold text-stone-950">Ultime richieste</h2>
                            </div>
                            <a href="{{ route('one-to-ones.index') }}" class="text-sm font-medium text-[color:var(--km-accent-strong)]">Gestisci</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($receivedOneToOnes as $requestItem)
                                <div class="rounded-[1.6rem] border border-stone-200 bg-white p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold text-stone-950">{{ $requestItem->requester->name }}</p>
                                            <p class="text-xs uppercase tracking-[0.16em] text-stone-500">
                                                {{ $requestItem->meeting_mode === 'online' ? 'Online' : 'In presenza' }} · {{ $requestItem->status->label() }}
                                            </p>
                                        </div>
                                        <div class="text-right text-xs text-stone-500">{{ optional($requestItem->requested_at)->format('d/m H:i') }}</div>
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-stone-600">{{ $requestItem->goal }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-stone-600">Nessuna richiesta ricevuta per ora.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="km-panel p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Eventi</p>
                                <h2 class="mt-1 text-xl font-semibold text-stone-950">Prossimi appuntamenti</h2>
                            </div>
                            <a href="{{ route('events.index') }}" class="text-sm font-medium text-[color:var(--km-accent-strong)]">Vedi tutti</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($upcomingEvents as $event)
                                <a href="{{ route('events.show', $event) }}" class="block rounded-[1.6rem] bg-stone-100 p-4 transition hover:bg-stone-200/80">
                                    <p class="text-sm font-semibold text-stone-950">{{ $event->title }}</p>
                                    <p class="mt-1 text-sm text-stone-600">{{ $event->location ?: 'Online' }}</p>
                                    <p class="mt-2 text-xs uppercase tracking-[0.16em] text-stone-500">{{ $event->starts_at->format('d M Y · H:i') }}</p>
                                </a>
                            @empty
                                <p class="text-sm text-stone-600">Nessun evento in arrivo.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="km-panel p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Forum</p>
                                <h2 class="mt-1 text-xl font-semibold text-stone-950">Discussioni attive</h2>
                            </div>
                            <a href="{{ route('forum.index') }}" class="text-sm font-medium text-[color:var(--km-accent-strong)]">Apri forum</a>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($latestThreads as $thread)
                                <a href="{{ route('forum.show', $thread) }}" class="block rounded-[1.6rem] border border-stone-200 bg-white p-4">
                                    <p class="text-sm font-semibold text-stone-950">{{ $thread->title }}</p>
                                    <p class="mt-1 text-sm text-stone-600">{{ $thread->category?->name ?? 'Community' }}</p>
                                </a>
                            @empty
                                <p class="text-sm text-stone-600">Nessuna discussione disponibile.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="km-panel p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Referenze inviate</p>
                                <h2 class="mt-1 text-xl font-semibold text-stone-950">Pipeline relazionale</h2>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            @forelse ($sentReferrals as $referral)
                                <div class="rounded-[1.6rem] bg-stone-100 p-4">
                                    <p class="text-sm font-semibold text-stone-950">{{ $referral->title }}</p>
                                    <p class="mt-1 text-sm text-stone-600">{{ $referral->recipient->name }}</p>
                                    <p class="mt-2 text-xs uppercase tracking-[0.16em] text-stone-500">{{ $referral->status->label() }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-stone-600">Non hai ancora inviato referenze.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
