<x-app-layout>
    @php $activeFilter = $filters['filter'] ?? 'all'; @endphp

    <style>
        :root {
            --km-msg-bg: #001821;
            --km-msg-panel: rgba(4, 34, 45, .78);
            --km-msg-line: rgba(153, 194, 202, .17);
            --km-msg-text: #f5fbfd;
            --km-msg-muted: rgba(222, 235, 238, .68);
            --km-msg-soft: rgba(222, 235, 238, .48);
            --km-msg-green: #79c843;
            --km-msg-green-2: #55aa54;
        }

        body {
            background:
                radial-gradient(circle at 80% 0%, rgba(121, 200, 67, .16), transparent 28%),
                radial-gradient(circle at 8% 22%, rgba(45, 212, 191, .10), transparent 30%),
                linear-gradient(135deg, #00121a, var(--km-msg-bg) 48%, #042d31) !important;
            color: var(--km-msg-text);
        }

        .km-msg-shell { width: min(1840px, calc(100% - 64px)); margin: 0 auto; }
        .km-msg-card {
            background: linear-gradient(145deg, rgba(4, 35, 46, .86), rgba(2, 25, 34, .74));
            border: 1px solid var(--km-msg-line);
            border-radius: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.025), 0 24px 80px rgba(0,0,0,.18);
            backdrop-filter: blur(16px);
        }
        .km-msg-layout { display: grid; grid-template-columns: 470px minmax(620px, 1fr) 440px; gap: 18px; align-items: stretch; }
        .km-msg-avatar { width: 54px; height: 54px; border-radius: 999px; border: 1px solid rgba(255,255,255,.28); object-fit: cover; background: linear-gradient(145deg, #173a47, #071a22); }
        .km-msg-input { border: 1px solid var(--km-msg-line); background: rgba(2, 24, 33, .72); color: var(--km-msg-text); outline: none; }
        .km-msg-input:focus { border-color: rgba(121, 200, 67, .42); box-shadow: 0 0 0 3px rgba(121, 200, 67, .08); }
        .km-msg-primary { background: linear-gradient(135deg, var(--km-msg-green-2), var(--km-msg-green)); color: #f8fff5; }
        .km-msg-thread { border-bottom: 1px solid rgba(153, 194, 202, .10); transition: background .16s ease; }
        .km-msg-thread:hover { background: linear-gradient(90deg, rgba(121, 200, 67, .22), rgba(54, 122, 84, .12)); }

        @media (max-width: 1400px) {
            .km-msg-layout { grid-template-columns: 380px minmax(520px, 1fr); }
            .km-msg-detail { grid-column: 1 / -1; }
        }

        @media (max-width: 980px) {
            .km-msg-shell { width: min(100% - 28px, 760px); }
            .km-msg-layout { display: block; }
            .km-msg-chat, .km-msg-detail { margin-top: 18px; }
        }
    </style>

    <div class="km-msg-shell py-7">
        <header class="km-msg-card mb-6 flex items-center gap-5 px-6 py-7">
            <div class="flex h-24 w-40 items-center justify-center rounded-xl border border-white/10 bg-white/10">
                <div class="text-center">
                    <div class="text-4xl font-bold leading-none text-[color:var(--km-msg-green)]">msg</div>
                    <div class="mt-1 text-xs uppercase tracking-[.28em] text-white">Kommunity</div>
                </div>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[.35em] text-[color:var(--km-msg-green)]">Messaggistica privata</p>
                <h1 class="mt-3 text-3xl font-semibold text-white">Conversazioni tra membri</h1>
                <p class="mt-2 text-base" style="color: var(--km-msg-muted);">Messaggi diretti e privati con gli altri membri.</p>
            </div>
        </header>

        <main class="km-msg-layout">
            <aside class="km-msg-card overflow-hidden">
                <div class="p-6">
                    <button type="button" data-open-message-modal class="km-msg-primary flex h-14 w-full items-center justify-center gap-2 rounded-lg text-base font-semibold">
                        <span class="text-2xl leading-none">+</span>
                        Nuovo messaggio
                    </button>

                    <form method="GET" action="{{ route('conversations.index') }}" class="mt-5 flex gap-3">
                        <label class="relative min-w-0 flex-1">
                            <input name="search" value="{{ $filters['search'] ?? '' }}" class="km-msg-input h-12 w-full rounded-xl px-4 pr-12" placeholder="Cerca conversazione...">
                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        </label>
                        <button type="submit" class="km-msg-input flex h-12 w-12 items-center justify-center rounded-xl">
                            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                        </button>
                    </form>

                    <div class="mt-5 flex items-center gap-4 text-sm font-semibold">
                        <a href="{{ route('conversations.index') }}" class="{{ $activeFilter === 'all' ? 'km-msg-primary' : 'text-white' }} rounded-full px-4 py-2">Tutte</a>
                        <a href="{{ route('conversations.index', ['filter' => 'unread']) }}" class="{{ $activeFilter === 'unread' ? 'km-msg-primary' : 'text-white' }} rounded-full px-4 py-2">Non lette</a>
                        <span class="rounded-full bg-[rgba(121,200,67,.25)] px-2.5 py-1 text-[color:var(--km-msg-green)]">{{ $unreadCount }}</span>
                        <a href="{{ route('conversations.index', ['filter' => 'favorites']) }}" class="{{ $activeFilter === 'favorites' ? 'km-msg-primary' : 'text-white' }} rounded-full px-4 py-2">Preferite</a>
                    </div>
                </div>

                <div>
                    @forelse ($conversations as $conversation)
                        @php
                            $participant = $conversation->getAttribute('other_participant');
                            $lastMessage = $conversation->getAttribute('last_message');
                            $hasUnread = $conversation->getAttribute('has_unread');
                            $avatar = $participant?->memberProfile?->avatarUrl();
                        @endphp
                        <a href="{{ route('conversations.show', $conversation) }}" class="km-msg-thread flex items-center gap-4 px-7 py-4 text-white">
                            @if ($avatar)
                                <img src="{{ $avatar }}" alt="{{ $participant?->name }}" class="km-msg-avatar">
                            @else
                                <div class="km-msg-avatar flex items-center justify-center text-xl font-semibold">{{ \Illuminate\Support\Str::of($participant?->name ?? 'K')->substr(0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="truncate text-lg font-semibold">{{ $participant?->name ?? $conversation->subject }}</h3>
                                    <span class="text-xs" style="color: var(--km-msg-soft);">{{ optional($lastMessage?->created_at)->isToday() ? optional($lastMessage?->created_at)->format('H:i') : optional($lastMessage?->created_at)->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 truncate text-sm" style="color: var(--km-msg-muted);">{{ $lastMessage?->body ?: 'Nessun messaggio ancora.' }}</p>
                            </div>
                            @if ($hasUnread)
                                <span class="rounded-full bg-[color:var(--km-msg-green)] px-2 py-1 text-xs font-bold text-white">2</span>
                            @endif
                        </a>
                    @empty
                        <div class="px-7 py-8 text-sm" style="color: var(--km-msg-muted);">Nessuna conversazione attiva.</div>
                    @endforelse
                </div>
            </aside>

            <section class="km-msg-card km-msg-chat flex min-h-[760px] items-center justify-center p-8 text-center">
                <div>
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 text-4xl text-[color:var(--km-msg-green)]">msg</div>
                    <h2 class="mt-5 text-2xl font-semibold text-white">Seleziona una conversazione</h2>
                    <p class="mt-2 max-w-md" style="color: var(--km-msg-muted);">Apri una chat dalla lista oppure crea un nuovo messaggio per iniziare una conversazione privata.</p>
                    <button type="button" data-open-message-modal class="km-msg-primary mt-6 inline-flex h-12 items-center justify-center rounded-lg px-6 font-semibold">Nuovo messaggio</button>
                </div>
            </section>

            <aside class="km-msg-card km-msg-detail p-6">
                <h2 class="text-lg font-semibold text-white">Dettagli conversazione</h2>
                <div class="mt-6 space-y-4 text-sm" style="color: var(--km-msg-muted);">
                    <p>Seleziona una conversazione per visualizzare profilo, contatti, media e opzioni.</p>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center justify-between"><span>Conversazioni</span><strong class="text-white">{{ $conversations->count() }}</strong></div>
                        <div class="mt-3 flex items-center justify-between"><span>Non lette</span><strong class="text-white">{{ $unreadCount }}</strong></div>
                    </div>
                </div>
            </aside>
        </main>
    </div>

    <div id="message-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="km-msg-card w-full max-w-2xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <h2 class="text-xl font-semibold text-white">Nuovo messaggio</h2>
                <button type="button" data-close-message-modal class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold text-white/80">Chiudi</button>
            </div>
            <form method="POST" action="{{ route('conversations.start') }}" class="space-y-4 px-5 py-5">
                @csrf
                <select name="recipient_id" class="km-msg-input h-12 w-full rounded-xl px-4" required>
                    <option value="">Seleziona membro</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <textarea name="message" rows="5" class="km-msg-input w-full rounded-xl px-4 py-3" placeholder="Scrivi il primo messaggio" required></textarea>
                <button type="submit" class="km-msg-primary h-12 w-full rounded-lg font-semibold">Apri conversazione</button>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('message-modal');
            const open = () => { modal?.classList.remove('hidden'); modal?.classList.add('flex'); };
            const close = () => { modal?.classList.add('hidden'); modal?.classList.remove('flex'); };
            document.querySelectorAll('[data-open-message-modal]').forEach((button) => button.addEventListener('click', open));
            document.querySelectorAll('[data-close-message-modal]').forEach((button) => button.addEventListener('click', close));
            modal?.addEventListener('click', (event) => { if (event.target === modal) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
        })();
    </script>
</x-app-layout>
