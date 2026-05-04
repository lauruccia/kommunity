<x-app-layout>
    @php
        $currentUserId = auth()->id();
        $otherParticipant = $conversation->participants->firstWhere('id', '!=', $currentUserId);
        $profile = $otherParticipant?->memberProfile;
        $detailAvatar = $profile?->avatarUrl();
        $company = $profile?->company_name;
        $role = $profile?->profession?->name ?? $profile?->category?->name ?? null;
        $phone = ($profile?->show_phone ?? false) ? $profile?->phone : null;
        $email = ($profile?->show_email ?? false) ? $otherParticipant?->email : null;
        $city = $profile?->city?->name;
        $joinedAt = $otherParticipant?->created_at?->translatedFormat('F Y');
        $profileUrl = ($otherParticipant && $otherParticipant->memberOnepage?->slug)
            ? route('members.show', $otherParticipant->memberOnepage->slug)
            : null;
        $activeFilter = $filters['filter'] ?? 'all';
        $otherLastReadAt = optional($otherParticipant?->pivot)->last_read_at;
        $showOnlineStatus = (bool) ($otherParticipant?->show_online_status ?? true);
        $isOnline = $showOnlineStatus
            && $otherParticipant?->last_seen_at
            && $otherParticipant->last_seen_at->gt(now()->subMinutes(5));
        $presenceLabel = $showOnlineStatus
            ? ($isOnline ? 'Online' : 'Offline')
            : 'Stato nascosto';

        $formatMessageTime = fn ($date) => ! $date
            ? ''
            : ($date->isToday() ? $date->format('H:i') : ($date->isYesterday() ? 'Ieri' : $date->format('d/m')));

        $messageReceipt = function ($message, $conversation) use ($currentUserId) {
            if (! $message || (int) $message->user_id !== (int) $currentUserId) {
                return null;
            }

            $other = $conversation->participants->firstWhere('id', '!=', $currentUserId);
            $lastReadAt = optional($other?->pivot)->last_read_at;
            $readReceiptsAllowed = (bool) ($other?->show_read_receipts ?? true);

            if ($readReceiptsAllowed && $lastReadAt && $message->created_at && $message->created_at->lte(\Illuminate\Support\Carbon::parse($lastReadAt))) {
                return ['label' => 'Letto', 'icon' => '✓✓', 'class' => 'km-chat-check-read'];
            }

            return ['label' => 'Consegnato', 'icon' => '✓', 'class' => 'km-chat-check-delivered'];
        };
    @endphp

    @push('body-class') km-bg-dark @endpush

    @push('styles')
        <style>
            .km-chat-layout{
                display:grid;
                grid-template-columns:minmax(19rem,23rem) minmax(0,1fr) minmax(18rem,22rem);
                gap:1rem;
                height:calc(100vh - 10.25rem);
                min-height:34rem;
            }
            .km-chat-panel{
                border:1px solid var(--km-line-dark);
                background:linear-gradient(145deg,rgba(4,35,46,.88),rgba(2,25,34,.78));
                box-shadow:inset 0 1px 0 rgba(255,255,255,.025),0 24px 80px rgba(0,0,0,.20);
                backdrop-filter:blur(16px);
                border-radius:var(--km-radius-lg);
                color:var(--km-text);
                overflow:hidden;
            }
            .km-chat-avatar{
                width:3rem;
                height:3rem;
                border-radius:999px;
                border:1px solid rgba(255,255,255,.24);
                object-fit:cover;
                background:linear-gradient(145deg,#173a47,#071a22);
                flex-shrink:0;
            }
            .km-chat-avatar-lg{width:6rem;height:6rem;}
            .km-chat-avatar-fallback{display:flex;align-items:center;justify-content:center;color:var(--km-green-2);font-weight:900;}
            .km-chat-thread{border-bottom:1px solid rgba(153,194,202,.10);transition:background .16s ease,border-color .16s ease;}
            .km-chat-thread:hover,.km-chat-thread-active{background:linear-gradient(90deg,rgba(139,197,63,.18),rgba(45,212,191,.08));}
            .km-chat-bubble{
                max-width:min(34rem,72%);
                border:1px solid rgba(153,194,202,.13);
                border-radius:1rem;
                background:linear-gradient(145deg,rgba(255,255,255,.08),rgba(255,255,255,.045));
            }
            .km-chat-bubble-own{
                margin-left:auto;
                border-color:rgba(139,197,63,.22);
                background:linear-gradient(145deg,rgba(75,132,75,.52),rgba(33,84,60,.46));
                box-shadow:0 12px 34px rgba(139,197,63,.08);
            }
            .km-chat-day{display:grid;grid-template-columns:1fr auto 1fr;gap:1rem;align-items:center;color:rgba(255,255,255,.42);font-size:.75rem;}
            .km-chat-day::before,.km-chat-day::after{content:"";height:1px;background:rgba(153,194,202,.14);}
            .km-chat-composer{position:sticky;bottom:0;background:linear-gradient(180deg,rgba(3,24,34,.78),rgba(3,24,34,.96));}
            .km-chat-action{
                display:inline-flex;
                width:2.9rem;
                height:2.9rem;
                align-items:center;
                justify-content:center;
                border-radius:1rem;
                border:1px solid rgba(255,255,255,.10);
                background:rgba(255,255,255,.045);
                color:rgba(255,255,255,.82);
                transition:border-color .2s ease,color .2s ease,background .2s ease;
            }
            .km-chat-action:hover{border-color:rgba(139,197,63,.35);color:var(--km-green-2);background:rgba(139,197,63,.08);}
            .km-chat-check-delivered{color:rgba(255,255,255,.42);}
            .km-chat-check-read{color:var(--km-green-2);font-weight:900;text-shadow:0 0 10px rgba(154,216,74,.45);}
            @media (max-width:1280px){
                .km-chat-layout{grid-template-columns:minmax(18rem,22rem) minmax(0,1fr);}
                .km-chat-detail{display:none;}
            }
            @media (max-width:860px){
                .km-chat-layout{display:block;height:auto;min-height:0;}
                .km-chat-list{display:none;}
                .km-chat-main{height:calc(100vh - 6.5rem);min-height:36rem;}
                .km-chat-bubble{max-width:86%;}
            }
        </style>
    @endpush

    <main class="km-shell-wide py-3">
        <section class="km-chat-layout">
            <aside class="km-chat-panel km-chat-list flex min-h-0 flex-col">
                <div class="border-b border-white/[.08] p-4">
                    <button type="button" data-open-message-modal class="km-cta-primary flex w-full justify-center text-sm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg>
                        Nuovo messaggio
                    </button>
                    <form method="GET" action="{{ route('conversations.index') }}" class="mt-4 flex gap-2">
                        <label class="relative min-w-0 flex-1">
                            <input name="search" value="{{ $filters['search'] ?? '' }}" class="km-dark-input h-11 rounded-xl pr-10" placeholder="Cerca conversazione...">
                            <svg class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        </label>
                        <button type="submit" class="km-cta-secondary h-11 w-11 justify-center p-0" aria-label="Cerca">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        </button>
                    </form>
                    <div class="mt-4 flex items-center gap-2 text-xs font-black">
                        <a href="{{ route('conversations.index') }}" class="{{ $activeFilter === 'all' ? 'km-cta-primary' : 'km-cta-secondary' }} px-3 py-2">Tutte</a>
                        <a href="{{ route('conversations.index', ['filter' => 'unread']) }}" class="{{ $activeFilter === 'unread' ? 'km-cta-primary' : 'km-cta-secondary' }} px-3 py-2">Non lette</a>
                        <a href="{{ route('conversations.index', ['filter' => 'favorites']) }}" class="{{ $activeFilter === 'favorites' ? 'km-cta-primary' : 'km-cta-secondary' }} px-3 py-2">Preferite</a>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    @forelse ($conversations as $item)
                        @php
                            $participant = $item->getAttribute('other_participant');
                            $lastMessage = $item->getAttribute('last_message');
                            $hasUnread = $item->getAttribute('has_unread');
                            $unreadMessages = (int) $item->getAttribute('unread_count');
                            $avatar = $participant?->memberProfile?->avatarUrl();
                            $receipt = $messageReceipt($lastMessage, $item);
                        @endphp
                        @php
                            $participantIsOnline = ($participant?->show_online_status ?? false)
                                && $participant?->last_seen_at
                                && $participant->last_seen_at->gt(now()->subMinutes(5));
                        @endphp
                        <a href="{{ route('conversations.show', $item) }}" class="km-chat-thread {{ $item->id === $conversation->id ? 'km-chat-thread-active' : '' }} flex gap-3 px-4 py-3 text-white">
                            <div class="relative">
                                @if ($avatar)
                                    <img src="{{ $avatar }}" alt="{{ $participant?->name }}" class="km-chat-avatar">
                                @else
                                    <div class="km-chat-avatar km-chat-avatar-fallback text-lg">{{ \Illuminate\Support\Str::of($participant?->name ?? 'K')->substr(0, 1)->upper() }}</div>
                                @endif
                                @if ($participantIsOnline)
                                    <span class="absolute -right-0.5 bottom-0 h-3 w-3 rounded-full border-2 border-[#052532] bg-[color:var(--km-green-2)]"></span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="truncate text-sm font-black">{{ $participant?->name ?? $item->subject }}</h3>
                                    <span class="shrink-0 text-[11px] text-white/45">{{ $formatMessageTime($lastMessage?->created_at) }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    @if ($receipt)
                                        <span class="{{ $receipt['class'] }} text-xs" title="{{ $receipt['label'] }}">{{ $receipt['icon'] }}</span>
                                    @endif
                                    <p class="truncate text-xs text-white/55">{{ $lastMessage?->body ?: 'Nessun messaggio ancora.' }}</p>
                                </div>
                            </div>
                            @if ($hasUnread)
                                <span class="mt-5 flex h-5 min-w-5 items-center justify-center rounded-full bg-[color:var(--km-green)] px-1.5 text-[10px] font-black text-[#061018]">{{ max(1, $unreadMessages) }}</span>
                            @endif
                        </a>
                    @empty
                        <div class="p-5 text-sm text-white/55">Nessuna conversazione attiva.</div>
                    @endforelse
                </div>
            </aside>

            <section class="km-chat-panel km-chat-main flex min-h-0 flex-col">
                <header class="flex items-center justify-between border-b border-white/[.08] px-4 py-3 sm:px-5">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('conversations.index') }}" class="km-chat-action md:hidden" aria-label="Torna alle conversazioni">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                        <div class="relative">
                            @if ($detailAvatar)
                                <img src="{{ $detailAvatar }}" alt="{{ $otherParticipant?->name }}" class="km-chat-avatar">
                            @else
                                <div class="km-chat-avatar km-chat-avatar-fallback text-lg">{{ \Illuminate\Support\Str::of($otherParticipant?->name ?? 'K')->substr(0, 1)->upper() }}</div>
                            @endif
                            @if ($isOnline)
                                <span class="absolute -right-0.5 bottom-0 h-3 w-3 rounded-full border-2 border-[#052532] bg-[color:var(--km-green-2)]"></span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h1 class="truncate text-base font-black text-white sm:text-lg">{{ $otherParticipant?->name ?? $conversation->subject }}</h1>
                            <p class="truncate text-xs text-white/55"><span class="{{ $isOnline ? 'text-[color:var(--km-green-2)]' : 'text-white/45' }}">{{ $presenceLabel }}</span>{{ $role ? ' · '.$role : '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($profileUrl)
                            <a class="km-cta-secondary text-sm" href="{{ $profileUrl }}">Profilo</a>
                        @endif
                    </div>
                </header>

                <div id="chat-message-list" class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-6">
                    @php $lastDay = null; @endphp
                    @forelse ($messages as $message)
                        @php
                            $isOwnMessage = (int) $message->user_id === (int) $currentUserId;
                            $dayLabel = $message->created_at->isToday() ? 'Oggi' : $message->created_at->translatedFormat('d F Y');
                            $avatar = $message->user?->memberProfile?->avatarUrl();
                            $receipt = $messageReceipt($message, $conversation);
                        @endphp
                        @if ($lastDay !== $dayLabel)
                            <div class="km-chat-day">{{ $dayLabel }}</div>
                            @php $lastDay = $dayLabel; @endphp
                        @endif

                        <div class="flex items-end gap-3 {{ $isOwnMessage ? 'justify-end' : '' }}">
                            @unless ($isOwnMessage)
                                @if ($avatar)
                                    <img src="{{ $avatar }}" alt="{{ $message->user?->name }}" class="h-9 w-9 rounded-full object-cover">
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/[.08] text-sm font-black text-[color:var(--km-green-2)]">{{ \Illuminate\Support\Str::of($message->user?->name ?? 'U')->substr(0, 1)->upper() }}</div>
                                @endif
                            @endunless
                            <article class="km-chat-bubble {{ $isOwnMessage ? 'km-chat-bubble-own' : '' }} px-4 py-3">
                                <p class="whitespace-pre-line text-sm leading-6 text-white/90">{{ $message->body }}</p>
                                @if ($message->attachment)
                                    <div class="mt-3 rounded-xl border border-white/[.10] bg-black/10 p-3 text-xs text-white/70">
                                        <span class="font-bold text-white">Allegato</span>
                                        <span class="ml-2">{{ basename($message->attachment) }}</span>
                                    </div>
                                @endif
                                <div class="mt-1 flex justify-end gap-1 text-xs text-white/45">
                                    <span>{{ $message->created_at->format('H:i') }}</span>
                                    @if ($receipt)
                                        <span class="{{ $receipt['class'] }}" title="{{ $receipt['label'] }}">{{ $receipt['icon'] }}</span>
                                    @endif
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center text-center">
                            <div>
                                <h2 class="text-xl font-black text-white">Nessun messaggio</h2>
                                <p class="mt-2 text-sm text-white/55">Scrivi il primo messaggio per iniziare la conversazione.</p>
                            </div>
                        </div>
                    @endforelse
                    <div id="chat-bottom-anchor" aria-hidden="true"></div>
                </div>

                <form id="chat-composer-form" method="POST" action="{{ route('conversations.messages.store', $conversation) }}" class="km-chat-composer border-t border-white/[.08] p-4">
                    @csrf
                    <div class="flex items-end gap-3 rounded-2xl border border-white/[.12] bg-white/[.045] px-3 py-2">
                        <textarea name="body" rows="1" class="min-h-[2.5rem] flex-1 resize-none border-0 bg-transparent py-2 text-sm text-white outline-none placeholder:text-white/35 focus:ring-0" placeholder="Scrivi un messaggio..." required></textarea>
                        <button type="submit" class="km-button-primary h-11 w-11 rounded-xl p-0" aria-label="Invia">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </div>
                </form>
            </section>

            <aside class="km-chat-panel km-chat-detail min-h-0 overflow-y-auto p-5">
                <div class="text-center">
                    <div class="relative mx-auto inline-flex">
                        @if ($detailAvatar)
                            <img src="{{ $detailAvatar }}" alt="{{ $otherParticipant?->name }}" class="km-chat-avatar km-chat-avatar-lg">
                        @else
                            <div class="km-chat-avatar km-chat-avatar-lg km-chat-avatar-fallback text-3xl">{{ \Illuminate\Support\Str::of($otherParticipant?->name ?? 'K')->substr(0, 1)->upper() }}</div>
                        @endif
                        @if ($isOnline)
                            <span class="absolute bottom-1 right-1 h-4 w-4 rounded-full border-2 border-[#052532] bg-[color:var(--km-green-2)]"></span>
                        @endif
                    </div>
                    <h2 class="mt-4 text-xl font-black text-white">{{ $otherParticipant?->name ?? $conversation->subject }}</h2>
                    <p class="mt-1 text-sm text-white/55">{{ $role ?: ($company ?: 'Membro Kommunity') }}</p>
                    <p class="mt-1 text-xs {{ $isOnline ? 'text-[color:var(--km-green-2)]' : 'text-white/45' }}">{{ $presenceLabel }}</p>
                </div>

                @if ($profileUrl)
                    <div class="mt-5">
                        <a href="{{ $profileUrl }}" class="km-cta-primary flex justify-center text-sm">Apri profilo</a>
                    </div>
                @endif

                <section class="mt-6 border-t border-white/[.08] pt-5">
                    <p class="km-eyebrow">Informazioni</p>
                    <div class="mt-3 space-y-3 text-sm text-white/70">
                        @if ($email)<p class="truncate">Email: <a href="mailto:{{ $email }}" class="text-white hover:text-[color:var(--km-green-2)]">{{ $email }}</a></p>@endif
                        @if ($phone)<p>Telefono: <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="text-white hover:text-[color:var(--km-green-2)]">{{ $phone }}</a></p>@endif
                        @if ($city)<p>Citta': <span class="text-white">{{ $city }}</span></p>@endif
                        @if ($joinedAt)<p>Iscritto da: <span class="text-white">{{ $joinedAt }}</span></p>@endif
                    </div>
                </section>

                <section class="mt-6 border-t border-white/[.08] pt-5">
                    <p class="km-eyebrow">Privacy</p>
                    <div class="mt-3 rounded-2xl border border-white/[.08] bg-white/[.035] p-4 text-sm leading-6 text-white/60">
                        Lo stato online e le conferme di lettura sono mostrati solo se l'utente li abilita dal profilo.
                    </div>
                </section>
            </aside>
        </section>
    </main>

    <div id="message-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="km-chat-panel w-full max-w-2xl">
            <div class="flex items-center justify-between border-b border-white/[.10] px-5 py-4">
                <div>
                    <p class="km-eyebrow">Nuovo messaggio</p>
                    <h2 class="mt-1 text-xl font-black text-white">Avvia conversazione</h2>
                </div>
                <button type="button" data-close-message-modal class="km-cta-secondary">Chiudi</button>
            </div>
            <form method="POST" action="{{ route('conversations.start') }}" class="space-y-4 px-5 py-5">
                @csrf
                <select name="recipient_id" class="km-dark-input h-12 w-full" required>
                    <option value="">Seleziona membro</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
                <textarea name="message" rows="5" class="km-dark-input w-full" placeholder="Scrivi il primo messaggio" required></textarea>
                <button type="submit" class="km-button-primary w-full">Apri conversazione</button>
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

            const messageList = document.getElementById('chat-message-list');
            const bottomAnchor = document.getElementById('chat-bottom-anchor');
            const scrollToBottom = () => {
                if (!messageList) return;
                messageList.scrollTop = messageList.scrollHeight;
                bottomAnchor?.scrollIntoView({ block: 'end' });
            };

            window.addEventListener('load', () => {
                requestAnimationFrame(scrollToBottom);
                setTimeout(scrollToBottom, 80);
            });

            document.getElementById('chat-composer-form')?.addEventListener('submit', () => {
                sessionStorage.setItem('km-scroll-chat-bottom', '1');
            });

            if (sessionStorage.getItem('km-scroll-chat-bottom') === '1') {
                sessionStorage.removeItem('km-scroll-chat-bottom');
                requestAnimationFrame(scrollToBottom);
            }
        })();
    </script>
</x-app-layout>
