<x-app-layout>
    @php
        $currentUserId = auth()->id();
        $otherParticipant = $conversation->participants->firstWhere('id', '!=', $currentUserId);
        $detailAvatar = $otherParticipant?->memberProfile?->avatarUrl();
        $company = $otherParticipant?->memberProfile?->company_name ?: 'Membro Kommunity';
        $phone = $otherParticipant?->memberProfile?->phone ?: '+39 345 678 9012';
        $email = $otherParticipant?->email ?: 'contatto@example.com';
        $activeFilter = $filters['filter'] ?? 'all';
    @endphp

    <style>
        :root {
            --km-msg-bg: #001821;
            --km-msg-panel: rgba(4, 34, 45, .78);
            --km-msg-panel-2: rgba(7, 43, 55, .64);
            --km-msg-line: rgba(153, 194, 202, .17);
            --km-msg-line-strong: rgba(169, 214, 221, .26);
            --km-msg-text: #f5fbfd;
            --km-msg-muted: rgba(222, 235, 238, .68);
            --km-msg-soft: rgba(222, 235, 238, .48);
            --km-msg-green: #79c843;
            --km-msg-green-2: #55aa54;
            --km-msg-red: #ef6262;
        }

        body {
            background:
                radial-gradient(circle at 80% 0%, rgba(121, 200, 67, .16), transparent 28%),
                radial-gradient(circle at 8% 22%, rgba(45, 212, 191, .10), transparent 30%),
                linear-gradient(135deg, #00121a, var(--km-msg-bg) 48%, #042d31) !important;
            color: var(--km-msg-text);
        }

        .km-msg-shell { width: min(1840px, calc(100% - 48px)); margin: 0 auto; overflow-x: hidden; }
        .km-msg-card {
            background: linear-gradient(145deg, rgba(4, 35, 46, .86), rgba(2, 25, 34, .74));
            border: 1px solid var(--km-msg-line);
            border-radius: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.025), 0 24px 80px rgba(0,0,0,.18);
            backdrop-filter: blur(16px);
        }
        .km-msg-layout { display: grid; grid-template-columns: minmax(0, 420px) minmax(0, 1fr) minmax(0, 400px); gap: 18px; align-items: stretch; }
        .km-msg-avatar { width: 54px; height: 54px; border-radius: 999px; border: 1px solid rgba(255,255,255,.28); object-fit: cover; background: linear-gradient(145deg, #173a47, #071a22); }
        .km-msg-avatar-lg { width: 78px; height: 78px; }
        .km-msg-input {
            border: 1px solid var(--km-msg-line);
            background: rgba(2, 24, 33, .72);
            color: var(--km-msg-text);
            outline: none;
        }
        .km-msg-input:focus { border-color: rgba(121, 200, 67, .42); box-shadow: 0 0 0 3px rgba(121, 200, 67, .08); }
        .km-msg-primary { background: linear-gradient(135deg, var(--km-msg-green-2), var(--km-msg-green)); color: #f8fff5; }
        .km-msg-thread { border-bottom: 1px solid rgba(153, 194, 202, .10); transition: background .16s ease; }
        .km-msg-thread:hover, .km-msg-thread-active { background: linear-gradient(90deg, rgba(121, 200, 67, .22), rgba(54, 122, 84, .12)); }
        .km-msg-bubble { max-width: 62%; border-radius: 10px; border: 1px solid rgba(153, 194, 202, .10); background: rgba(255,255,255,.065); }
        .km-msg-bubble-own { margin-left: auto; background: linear-gradient(145deg, rgba(75, 132, 75, .42), rgba(33, 84, 60, .44)); }
        .km-msg-day { display: grid; grid-template-columns: 1fr auto 1fr; gap: 18px; align-items: center; color: var(--km-msg-soft); }
        .km-msg-day::before, .km-msg-day::after { content: ""; height: 1px; background: rgba(153, 194, 202, .12); }

        /* Schermi medi: nascondi il pannello dettagli, lista + chat */
        @media (max-width: 1280px) {
            .km-msg-layout { grid-template-columns: minmax(0, 340px) minmax(0, 1fr); }
            .km-msg-detail { display: none; }
        }

        /* Schermi piccoli: colonna unica */
        @media (max-width: 860px) {
            .km-msg-shell { width: calc(100% - 24px); }
            .km-msg-layout { display: flex; flex-direction: column; }
            .km-msg-chat { margin-top: 18px; }
            .km-msg-bubble { max-width: 86%; }
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
                    @forelse ($conversations as $item)
                        @php
                            $participant = $item->getAttribute('other_participant');
                            $lastMessage = $item->getAttribute('last_message');
                            $hasUnread = $item->getAttribute('has_unread');
                            $avatar = $participant?->memberProfile?->avatarUrl();
                        @endphp
                        <a href="{{ route('conversations.show', $item) }}" class="km-msg-thread {{ $item->id === $conversation->id ? 'km-msg-thread-active' : '' }} flex items-center gap-4 px-7 py-4 text-white">
                            @if ($avatar)
                                <img src="{{ $avatar }}" alt="{{ $participant?->name }}" class="km-msg-avatar">
                            @else
                                <div class="km-msg-avatar flex items-center justify-center text-xl font-semibold">{{ \Illuminate\Support\Str::of($participant?->name ?? 'K')->substr(0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="truncate text-lg font-semibold">{{ $participant?->name ?? $item->subject }}</h3>
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

            <section class="km-msg-card km-msg-chat flex min-h-[760px] flex-col overflow-hidden">
                <div class="flex items-center justify-between border-b border-white/10 px-6 py-5">
                    <div class="flex items-center gap-4">
                        @if ($detailAvatar)
                            <img src="{{ $detailAvatar }}" alt="{{ $otherParticipant?->name }}" class="km-msg-avatar">
                        @else
                            <div class="km-msg-avatar flex items-center justify-center text-xl font-semibold">{{ \Illuminate\Support\Str::of($otherParticipant?->name ?? 'K')->substr(0, 1) }}</div>
                        @endif
                        <div>
                            <h2 class="text-xl font-semibold text-white">{{ $otherParticipant?->name ?? $conversation->subject }}</h2>
                            <p class="text-sm text-[color:var(--km-msg-green)]">Online</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="km-msg-input flex h-11 w-11 items-center justify-center rounded-full"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.7 5.47 6.03.88-4.36 4.25 1.03 6-5.4-2.84-5.4 2.84 1.03-6-4.36-4.25 6.03-.88L12 3Z"/></svg></button>
                        <button type="button" class="km-msg-input flex h-11 w-11 items-center justify-center rounded-full"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 12h.01M19 12h.01M5 12h.01"/></svg></button>
                    </div>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                    @php $lastDay = null; @endphp
                    @foreach ($messages as $message)
                        @php
                            $isOwnMessage = $message->user_id === $currentUserId;
                            $dayLabel = $message->created_at->isToday() ? 'Oggi' : $message->created_at->translatedFormat('d F Y');
                            $avatar = $message->user?->memberProfile?->avatarUrl();
                        @endphp
                        @if ($lastDay !== $dayLabel)
                            <div class="km-msg-day text-sm">{{ $dayLabel }}</div>
                            @php $lastDay = $dayLabel; @endphp
                        @endif

                        <div class="flex items-end gap-3 {{ $isOwnMessage ? 'justify-end' : '' }}">
                            @unless ($isOwnMessage)
                                @if ($avatar)
                                    <img src="{{ $avatar }}" alt="{{ $message->user?->name }}" class="h-9 w-9 rounded-full object-cover">
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-sm font-semibold">{{ \Illuminate\Support\Str::of($message->user?->name ?? 'U')->substr(0, 1) }}</div>
                                @endif
                            @endunless
                            <div class="km-msg-bubble {{ $isOwnMessage ? 'km-msg-bubble-own' : '' }} px-4 py-3">
                                <p class="text-sm leading-6 text-white/90">{{ $message->body }}</p>
                                <div class="mt-1 text-right text-xs" style="color: var(--km-msg-soft);">{{ $message->created_at->format('H:i') }} @if($isOwnMessage)<span class="text-[color:var(--km-msg-green)]">✓✓</span>@endif</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('conversations.messages.store', $conversation) }}" class="border-t border-white/10 p-5">
                    @csrf
                    <div class="km-msg-input flex items-end gap-3 rounded-xl px-4 py-3">
                        <div class="flex gap-4 pb-2 text-white/65">
                            <span>⌘</span><span>☺</span><span class="rounded border border-white/30 px-1 text-xs">GIF</span>
                        </div>
                        <textarea name="body" rows="2" class="min-h-[44px] flex-1 resize-none py-2 outline-none" style="background:transparent;color:#f5fbfd;" placeholder="Scrivi un messaggio..." required></textarea>
                        <button type="submit" class="km-msg-primary flex h-12 w-12 items-center justify-center rounded-lg">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </div>
                </form>
            </section>

            <aside class="km-msg-card km-msg-detail overflow-hidden">
                <div class="border-b border-white/10 px-6 py-5">
                    <h2 class="text-lg font-semibold text-white">Dettagli conversazione</h2>
                </div>
                <div class="space-y-7 p-6">
                    <div class="flex items-center gap-5">
                        @if ($detailAvatar)
                            <img src="{{ $detailAvatar }}" alt="{{ $otherParticipant?->name }}" class="km-msg-avatar km-msg-avatar-lg">
                        @else
                            <div class="km-msg-avatar km-msg-avatar-lg flex items-center justify-center text-2xl font-semibold">{{ \Illuminate\Support\Str::of($otherParticipant?->name ?? 'K')->substr(0, 1) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="truncate text-xl font-semibold text-white">{{ $otherParticipant?->name ?? $conversation->subject }}</h3>
                                <span class="rounded-full bg-[rgba(121,200,67,.20)] px-3 py-1 text-sm font-semibold text-[color:var(--km-msg-green)]">Online</span>
                            </div>
                            <p style="color: var(--km-msg-muted);">{{ $company }}</p>
                        </div>
                    </div>

                    <div class="divide-y divide-white/10 text-white/90">
                        <div class="flex items-center gap-4 py-4"><span>✉</span><span>{{ $email }}</span></div>
                        <div class="flex items-center gap-4 py-4"><span>⌕</span><span>{{ $phone }}</span></div>
                        <div class="flex items-center gap-4 py-4"><span>♙</span><span>Visualizza profilo</span></div>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold text-white">Media, file e link</h3>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-sm">8</span>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <div class="flex aspect-square items-center justify-center rounded-lg border border-white/10 bg-white/10 text-sm font-semibold">PDF</div>
                            <div class="aspect-square rounded-lg border border-white/10 bg-gradient-to-br from-emerald-900 to-slate-800"></div>
                            <div class="aspect-square rounded-lg border border-white/10 bg-gradient-to-br from-sky-500 via-violet-500 to-red-400"></div>
                            <div class="flex aspect-square items-center justify-center rounded-lg border border-white/10 bg-white/5 text-lg">+5</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-4 font-semibold text-white">Opzioni conversazione</h3>
                        <div class="divide-y divide-white/10">
                            <div class="flex items-center justify-between py-4"><span>Notifiche</span><span class="h-7 w-12 rounded-full bg-[color:var(--km-msg-green)] p-1"><span class="block h-5 w-5 translate-x-5 rounded-full bg-white"></span></span></div>
                            <div class="flex items-center justify-between py-4"><span>Aggiungi ai preferiti</span><span class="h-7 w-12 rounded-full bg-[color:var(--km-msg-green)] p-1"><span class="block h-5 w-5 translate-x-5 rounded-full bg-white"></span></span></div>
                            <button type="button" class="flex w-full items-center gap-4 py-4 text-left text-[color:var(--km-msg-red)]">⊘ Blocca utente</button>
                            <button type="button" class="flex w-full items-center gap-4 py-4 text-left text-[color:var(--km-msg-red)]">♜ Elimina conversazione</button>
                        </div>
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
