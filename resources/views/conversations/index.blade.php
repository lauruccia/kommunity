<x-app-layout>
    <x-slot name="header">
        <div class="km-portal-panel p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Messaggistica privata</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold text-white sm:text-3xl lg:text-4xl">Conversazioni tra membri</h1>
        </div>
    </x-slot>

    <div class="km-portal-bg km-portal-page pb-12 pt-6">
        <div class="km-shell grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="km-portal-panel p-6 order-last lg:order-first">
                <h2 class="text-lg font-semibold text-white">Nuovo messaggio</h2>
                <form method="POST" action="{{ route('conversations.start') }}" class="mt-4 space-y-4">
                    @csrf
                    <select name="recipient_id" class="km-portal-input" required>
                        <option value="">Seleziona membro</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <textarea name="message" rows="5" class="km-portal-input" placeholder="Scrivi il primo messaggio" required></textarea>
                    <button type="submit" class="km-button-primary w-full">Apri conversazione</button>
                </form>
            </aside>

            <section class="space-y-4">
                <div class="km-portal-panel p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-white/60">Inbox membri</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white">Conversazioni attive</h2>
                        </div>
                        <div class="inline-flex rounded-full bg-white/[.075] px-4 py-2 text-sm font-medium text-white/80">
                            {{ $unreadCount }} non lette
                        </div>
                    </div>
                    <form method="GET" class="mt-4 grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-portal-input" placeholder="Cerca per nome o testo ultimo messaggio">
                        <select name="filter" class="km-portal-input">
                            <option value="all" @selected(($filters['filter'] ?? 'all') === 'all')>Tutte</option>
                            <option value="unread" @selected(($filters['filter'] ?? 'all') === 'unread')>Solo non lette</option>
                        </select>
                        <button type="submit" class="km-button-primary">Applica filtri</button>
                    </form>
                </div>

                @forelse ($conversations as $conversation)
                    @php
                        $otherParticipant = $conversation->getAttribute('other_participant');
                        $lastMessage = $conversation->getAttribute('last_message');
                        $hasUnread = $conversation->getAttribute('has_unread');
                    @endphp
                    <a href="{{ route('conversations.show', $conversation) }}" class="block km-panel p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-3">
                                    <h2 class="min-w-0 truncate text-xl font-semibold text-white">{{ $otherParticipant?->name ?? $conversation->subject }}</h2>
                                    @if ($hasUnread)
                                        <span class="rounded-full bg-[color:var(--km-accent)] px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Nuovo</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-white/60">{{ $otherParticipant?->memberProfile?->company_name ?: 'Membro Kommunity' }}</p>
                                <p class="mt-2 line-clamp-2 text-sm leading-7 text-white/75">{{ $lastMessage?->body }}</p>
                            </div>
                            <div class="text-xs text-white/60 sm:whitespace-nowrap">{{ optional($lastMessage?->created_at)->format('d/m H:i') }}</div>
                        </div>
                    </a>
                @empty
                    <div class="km-portal-panel p-6">
                        <p class="text-sm text-white/75">Nessuna conversazione attiva.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
