<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kommunity') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />

        {{-- PWA: manifest + icone --}}
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#0b0d12">
        <link rel="apple-touch-icon" href="/images/icon-192.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            $kmCssCandidates = [
                public_path('css/kommunity.css'),
                base_path('../public_html/css/kommunity.css'),
            ];
            $kmCssVer = '1';
            foreach ($kmCssCandidates as $kmCssPath) {
                if (file_exists($kmCssPath)) { $kmCssVer = filemtime($kmCssPath); break; }
            }
        @endphp
        <link rel="stylesheet" href="{{ asset('css/kommunity.css') }}?v={{ $kmCssVer }}">

        @stack('styles')
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans antialiased text-stone-900 @stack('body-class')">
        <div class="min-h-screen">
            @if(session('impersonating_admin_id'))
                <div class="bg-amber-500 text-white text-sm font-semibold px-4 py-2 flex items-center justify-between sticky top-0 z-50 shadow">
                    <span>
                        Stai navigando come <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}).
                    </span>
                    <form method="POST" action="/admin/impersonate/stop" class="inline">
                        @csrf
                        <button type="submit" class="ml-4 bg-white text-amber-700 rounded px-3 py-1 text-xs font-bold hover:bg-amber-100 transition">
                            Esci dall'impersonificazione
                        </button>
                    </form>
                </div>
            @endif

            @unless($hideNavigation ?? false)
                @include('layouts.navigation')
            @endunless

            @php
                $toastSuccess = session('success')
                    ?: (session('status') === 'profile-updated-ai' ? 'Profilo aggiornato e rielaborato dall\'AI!'
                    : (session('status') === 'profile-updated' ? 'Profilo aggiornato con successo!' : null));
                $toastError   = session('error');
                $toastWarning = session('warning');
            @endphp
            @if($toastSuccess || $toastError || $toastWarning)
                <div class="km-toast-stack" aria-live="polite">
                    @if($toastSuccess)
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="km-toast km-toast-success">
                            <span class="km-toast-icon">&#10003;</span>
                            <p class="km-toast-body">{{ $toastSuccess }}</p>
                            <button type="button" @click="show = false" class="km-toast-close" aria-label="Chiudi">&times;</button>
                        </div>
                    @endif
                    @if($toastError)
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 7000)" x-show="show" x-transition class="km-toast km-toast-error">
                            <span class="km-toast-icon">!</span>
                            <p class="km-toast-body">{{ $toastError }}</p>
                            <button type="button" @click="show = false" class="km-toast-close" aria-label="Chiudi">&times;</button>
                        </div>
                    @endif
                    @if($toastWarning)
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition class="km-toast km-toast-warning">
                            <span class="km-toast-icon">!</span>
                            <p class="km-toast-body">{{ $toastWarning }}</p>
                            <button type="button" @click="show = false" class="km-toast-close" aria-label="Chiudi">&times;</button>
                        </div>
                    @endif
                </div>
            @endif

            @isset($header)
                <header class="pt-8">
                    <div class="km-shell">{{ $header }}</div>
                </header>
            @endisset
            <main>{{ $slot }}</main>
        </div>

        @stack('modals')
        @stack('scripts')

        @auth
        @unless(request()->routeIs('planet.chat.*'))
        @php
            $_fabUser       = auth()->user();
            // Usa il pianeta attivo scelto dall'utente, con fallback al primo membro attivo
            $_fabActiveId   = $_fabUser->memberProfile?->active_chapter_id;
            $_fabMembership = \App\Models\ChapterMember::where('user_id', $_fabUser->id)
                ->where('status', 'active')
                ->when($_fabActiveId, fn($q) => $q->where('chapter_id', $_fabActiveId))
                ->with('chapter:id,name')
                ->first();
            $fabChapterId   = $_fabMembership?->chapter_id;
            $fabChapterName = $_fabMembership?->chapter?->name ?? '';
            $fabUnread      = 0;
            if ($fabChapterId) {
                $fabUnread = \App\Models\PlanetChatMessage::where('chapter_id', $fabChapterId)
                    ->where('user_id', '!=', $_fabUser->id)
                    ->when($_fabMembership->chat_last_read_at,
                        fn($q) => $q->where('created_at', '>', $_fabMembership->chat_last_read_at)
                    )
                    ->count();
                $fabUnread = min($fabUnread, 99);
            }
        @endphp
@if($fabChapterId)
        <div class="km-chat-fab"
             x-data="kmChatPopup({
                 pollUrl:       '{{ route('planet.chat.poll', $fabChapterId) }}',
                 storeUrl:      '{{ route('planet.chat.store', $fabChapterId) }}',
                 csrf:          '{{ csrf_token() }}',
                 currentUserId: {{ auth()->id() }},
                 initUnread:    {{ $fabUnread }}
             })"
             @click.outside="close()">

            <div class="km-chat-popup" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="km-chat-popup-header">
                    <div class="km-chat-popup-title">
                        <span class="km-chat-popup-dot"></span>
                        <span>{{ $fabChapterName }}</span>
                    </div>
                    <div class="km-chat-popup-actions">
                        <a href="{{ route('planet.chat.show', $fabChapterId) }}" class="km-chat-popup-open-btn"
                           title="{{ app()->getLocale() === 'it' ? 'Apri chat' : 'Open chat' }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                <polyline points="9 21 3 21 3 15"/><line x1="3" y1="21" x2="14" y2="10"/>
                            </svg>
                        </a>
                        <button @click.stop="close()" class="km-chat-popup-close" aria-label="Chiudi">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="km-chat-popup-messages" x-ref="msgbox">
                    <template x-if="loading">
                        <div class="km-chat-popup-loading"><span>...</span></div>
                    </template>
                    <template x-if="!loading && messages.length === 0">
                        <div class="km-chat-popup-empty">
                            {{ app()->getLocale() === 'it' ? 'Nessun messaggio ancora' : 'No messages yet' }}
                        </div>
                    </template>
                    <template x-for="msg in messages" :key="msg.id">
                        <div class="km-chat-popup-row" :class="msg.user_id == currentUserId ? 'mine' : ''">
                            <template x-if="msg.user_id != currentUserId && msg.avatar">
                                <img :src="msg.avatar" :alt="msg.author" class="km-chat-popup-avatar">
                            </template>
                            <template x-if="msg.user_id != currentUserId && !msg.avatar">
                                <div class="km-chat-popup-avatar-fb" x-text="msg.initials"></div>
                            </template>
                            <div class="km-chat-popup-bubble-wrap">
                                <template x-if="msg.user_id != currentUserId">
                                    <div class="km-chat-popup-author" x-text="msg.author"></div>
                                </template>
                                <div class="km-chat-popup-bubble" x-text="msg.body"></div>
                                <div class="km-chat-popup-time" x-text="msg.created_at"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="km-chat-popup-input-bar">
                    <textarea x-model="draft"
                        @keydown.enter.prevent="if(!$event.shiftKey) send()"
                        @input="autoResize($el)"
                        class="km-chat-popup-textarea"
                        rows="1"
                        placeholder="{{ app()->getLocale() === 'it' ? 'Scrivi un messaggio...' : 'Write a message...' }}"
                        maxlength="2000"
                        :disabled="sending"></textarea>
                    <button class="km-chat-popup-send" @click="send()" :disabled="!draft.trim() || sending">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button @click.stop="toggle()" class="km-chat-fab-btn"
                    :class="{ 'has-unread': unread > 0, 'is-open': open }"
                    aria-label="{{ app()->getLocale() === 'it' ? 'Chat pianeta' : 'Planet chat' }}">
                <svg x-show="!open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <svg x-show="open" x-cloak width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                <span class="km-chat-fab-badge" x-show="unread > 0 && !open" x-cloak x-text="unread > 99 ? '99+' : unread"></span>
            </button>
        </div>

        <script>
        function kmChatPopup({ pollUrl, storeUrl, csrf, currentUserId, initUnread }) {
            return {
                open:          false,
                loading:       false,
                messages:      [],
                draft:         '',
                sending:       false,
                lastId:        0,
                unread:        initUnread,
                currentUserId: currentUserId,
                pollTimer:     null,

                toggle() { this.open ? this.close() : this.openPopup(); },

                async openPopup() {
                    this.open    = true;
                    this.loading = true;
                    this.unread  = 0;
                    await this.loadRecent();
                    this.loading = false;
                    this.$nextTick(() => this.scrollBottom());
                    this.startPolling();
                },

                close() {
                    this.open = false;
                    this.stopPolling();
                },

                async loadRecent() {
                    try {
                        const res  = await fetch(pollUrl + '?since=0&limit=15', { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data.messages) {
                            this.messages = data.messages;
                            this.lastId   = data.messages.length ? data.messages[data.messages.length - 1].id : 0;
                        }
                    } catch (e) {}
                },

                startPolling() { this.pollTimer = setInterval(() => this.poll(), 3000); },
                stopPolling()  { if (this.pollTimer) { clearInterval(this.pollTimer); this.pollTimer = null; } },

                async poll() {
                    try {
                        const res  = await fetch(pollUrl + '?since=' + this.lastId, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (data.messages && data.messages.length > 0) {
                            this.messages.push(...data.messages);
                            this.lastId = data.messages[data.messages.length - 1].id;
                            this.$nextTick(() => this.scrollBottom());
                        }
                    } catch (e) {}
                },

                async send() {
                    const body = this.draft.trim();
                    if (!body || this.sending) return;
                    this.sending = true;
                    try {
                        const res  = await fetch(storeUrl, {
                            method:  'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body:    JSON.stringify({ body }),
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (data.message) {
                            this.messages.push(data.message);
                            this.lastId = data.message.id;
                            this.draft  = '';
                            this.$nextTick(() => {
                                this.scrollBottom();
                                const ta = this.$el.querySelector('.km-chat-popup-textarea');
                                if (ta) ta.style.height = 'auto';
                            });
                        }
                    } catch (e) {}
                    finally { this.sending = false; }
                },

                scrollBottom() {
                    const box = this.$refs.msgbox;
                    if (box) box.scrollTop = box.scrollHeight;
                },

                autoResize(el) {
                    el.style.height = 'auto';
                    el.style.height = Math.min(el.scrollHeight, 80) + 'px';
                },
            };
        }
        </script>
        @endif
        @endunless
        @endauth

        @include('partials.cookie-banner')

        @auth
            @include('partials.push-consent-banner')
        @endauth
    </body>
</html>
