<x-app-layout>
    @push('body-class') km-bg-dark @endpush

    @push('styles')
    <style>
        /* ── Layout principale ────────────────────────────────────── */
        .pc-shell {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 10rem);
            min-height: 32rem;
            max-width: 52rem;
            margin: 0 auto;
        }
        /* ── Testata ─────────────────────────────────────────────── */
        .pc-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--km-line-dark);
            background: rgba(4,35,46,.70);
            backdrop-filter: blur(12px);
            border-radius: var(--km-radius-lg) var(--km-radius-lg) 0 0;
            flex-shrink: 0;
        }
        .pc-header-icon {
            width: 2.75rem; height: 2.75rem;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--km-green), var(--km-green-2));
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        /* ── Area messaggi ───────────────────────────────────────── */
        .pc-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: .65rem;
            background: linear-gradient(180deg, rgba(4,35,46,.85) 0%, rgba(2,20,28,.90) 100%);
            scroll-behavior: smooth;
        }
        .pc-messages::-webkit-scrollbar { width: 5px; }
        .pc-messages::-webkit-scrollbar-track { background: transparent; }
        .pc-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 99px; }
        /* ── Separatore data ─────────────────────────────────────── */
        .pc-date-sep {
            text-align: center;
            font-size: .7rem;
            color: rgba(255,255,255,.35);
            letter-spacing: .06em;
            text-transform: uppercase;
            margin: .5rem 0;
            position: relative;
        }
        .pc-date-sep::before, .pc-date-sep::after {
            content: '';
            position: absolute; top: 50%;
            width: calc(50% - 3rem);
            height: 1px;
            background: rgba(255,255,255,.08);
        }
        .pc-date-sep::before { left: 0; }
        .pc-date-sep::after  { right: 0; }
        /* ── Bolla messaggio ─────────────────────────────────────── */
        .pc-row { display: flex; align-items: flex-end; gap: .6rem; }
        .pc-row.mine { flex-direction: row-reverse; }
        .pc-avatar {
            width: 2rem; height: 2rem; border-radius: 999px;
            object-fit: cover; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.18);
            background: linear-gradient(135deg, #173a47, #071a22);
        }
        .pc-avatar-fallback {
            width: 2rem; height: 2rem; border-radius: 999px; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.18);
            background: linear-gradient(135deg, var(--km-green), var(--km-green-2));
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem; font-weight: 700; color: #fff;
        }
        .pc-bubble-wrap { display: flex; flex-direction: column; max-width: 72%; }
        .pc-row.mine .pc-bubble-wrap { align-items: flex-end; }
        .pc-author {
            font-size: .68rem;
            color: rgba(255,255,255,.4);
            margin-bottom: .2rem;
            padding: 0 .4rem;
        }
        .pc-bubble {
            display: inline-block;
            padding: .55rem .85rem;
            border-radius: 1.2rem 1.2rem 1.2rem .35rem;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.07);
            color: var(--km-text);
            font-size: .88rem;
            line-height: 1.45;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .pc-row.mine .pc-bubble {
            border-radius: 1.2rem 1.2rem .35rem 1.2rem;
            background: linear-gradient(135deg, rgba(var(--km-green-rgb,85,150,60),.30), rgba(var(--km-green-rgb,85,150,60),.18));
            border-color: rgba(139,197,63,.20);
            color: #fff;
        }
        .pc-time {
            font-size: .62rem;
            color: rgba(255,255,255,.28);
            padding: .15rem .4rem 0;
        }
        /* ── Input area ──────────────────────────────────────────── */
        .pc-input-bar {
            display: flex;
            align-items: flex-end;
            gap: .65rem;
            padding: .85rem 1.25rem;
            border-top: 1px solid var(--km-line-dark);
            background: rgba(4,35,46,.80);
            backdrop-filter: blur(12px);
            border-radius: 0 0 var(--km-radius-lg) var(--km-radius-lg);
            flex-shrink: 0;
        }
        .pc-textarea {
            flex: 1;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: .9rem;
            padding: .6rem 1rem;
            color: var(--km-text);
            font-size: .9rem;
            resize: none;
            line-height: 1.4;
            max-height: 7rem;
            outline: none;
            transition: border-color .2s;
        }
        .pc-textarea::placeholder { color: rgba(255,255,255,.28); }
        .pc-textarea:focus { border-color: rgba(139,197,63,.45); }
        .pc-send-btn {
            width: 2.6rem; height: 2.6rem; border-radius: 999px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--km-green), var(--km-green-2));
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: opacity .2s, transform .15s;
            color: #fff;
        }
        .pc-send-btn:disabled { opacity: .4; cursor: default; }
        .pc-send-btn:not(:disabled):hover { transform: scale(1.07); }
        /* ── Empty state ─────────────────────────────────────────── */
        .pc-empty {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: .75rem; color: rgba(255,255,255,.3);
            font-size: .9rem;
        }
        /* ── Spinner polling ─────────────────────────────────────── */
        .pc-connecting {
            text-align: center; font-size: .72rem;
            color: rgba(255,255,255,.25); padding: .4rem 0;
        }
        @media (max-width: 640px) {
            .pc-shell { height: calc(100vh - 8rem); min-height: 24rem; }
        }
    </style>
    @endpush

    @php
        $currentUserId = auth()->id();
        $csrfToken     = csrf_token();
        $pollUrl       = route('planet.chat.poll',  $chapter);
        $storeUrl      = route('planet.chat.store', $chapter);
        $lastId        = $messages->last()?->id ?? 0;

        $todayLabel     = __('planet_chat.today');
        $yesterdayLabel = __('planet_chat.yesterday');

        // Raggruppa messaggi per data per mostrare separatori
        $groupedMessages = $messages->groupBy(fn($m) => $m['date']);
    @endphp

    <div class="km-shell py-4">
        <div class="pc-shell"
             x-data="planetChat({
                 pollUrl:   '{{ $pollUrl }}',
                 storeUrl:  '{{ $storeUrl }}',
                 csrf:      '{{ $csrfToken }}',
                 lastId:    {{ $lastId }},
                 currentUserId: {{ $currentUserId }},
                 todayLabel:     '{{ $todayLabel }}',
                 yesterdayLabel: '{{ $yesterdayLabel }}'
             })"
             x-init="init()">

            {{-- Testata ──────────────────────────────────────────────── --}}
            <div class="pc-header km-panel">
                <div class="pc-header-icon">💬</div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-white">
                        {{ __('planet_chat.title') }}
                    </div>
                    <div class="truncate text-xs text-white/40">
                        {{ $chapter->name }} &middot;
                        {{ trans('planet_chat.members_online', ['count' => $memberCount]) }}
                    </div>
                </div>
                {{-- Badge "connesso" ──────────────────────── --}}
                <div class="flex items-center gap-1.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs text-white/30 hidden sm:inline">live</span>
                </div>
            </div>

            {{-- Messaggi ─────────────────────────────────────────────── --}}
            <div class="pc-messages" id="pc-scroll" x-ref="scroll">

                {{-- Messaggi iniziali (server-side) ──────────────────── --}}
                @forelse ($groupedMessages as $date => $group)
                    <div class="pc-date-sep">
                        @if ($date === now()->format('d/m/Y'))
                            {{ __('planet_chat.today') }}
                        @elseif ($date === now()->subDay()->format('d/m/Y'))
                            {{ __('planet_chat.yesterday') }}
                        @else
                            {{ $date }}
                        @endif
                    </div>

                    @foreach ($group as $msg)
                        @php $isMine = $msg['user_id'] === $currentUserId; @endphp
                        <div class="pc-row {{ $isMine ? 'mine' : '' }}">
                            {{-- Avatar ──────────────────────────────── --}}
                            @if ($msg['avatar'])
                                <img src="{{ $msg['avatar'] }}" class="pc-avatar" alt="{{ $msg['author'] }}">
                            @else
                                <div class="pc-avatar-fallback">{{ $msg['initials'] }}</div>
                            @endif
                            <div class="pc-bubble-wrap">
                                @unless ($isMine)
                                    <div class="pc-author">{{ $msg['author'] }}</div>
                                @endunless
                                <div class="pc-bubble">{{ $msg['body'] }}</div>
                                <div class="pc-time">{{ $msg['created_at'] }}</div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="pc-empty" x-show="messages.length === 0">
                        <span style="font-size:2.5rem;">💬</span>
                        <span>{{ __('planet_chat.no_messages') }}</span>
                    </div>
                @endforelse

                {{-- Messaggi nuovi (Alpine) ───────────────────────────── --}}
                <template x-for="(msg, idx) in messages" :key="msg.id">
                    <div>
                        {{-- Separatore data per nuovi messaggi --}}
                        <template x-if="idx === 0 || msg.date !== messages[idx-1]?.date">
                            <div class="pc-date-sep" x-text="formatDate(msg.date)"></div>
                        </template>

                        <div class="pc-row" :class="msg.user_id == currentUserId ? 'mine' : ''">
                            <template x-if="msg.avatar">
                                <img :src="msg.avatar" :alt="msg.author" class="pc-avatar">
                            </template>
                            <template x-if="!msg.avatar">
                                <div class="pc-avatar-fallback" x-text="msg.initials"></div>
                            </template>
                            <div class="pc-bubble-wrap">
                                <template x-if="msg.user_id != currentUserId">
                                    <div class="pc-author" x-text="msg.author"></div>
                                </template>
                                <div class="pc-bubble" x-text="msg.body"></div>
                                <div class="pc-time" x-text="msg.created_at"></div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Anchor per auto-scroll ────────────────────────────── --}}
                <div id="pc-bottom"></div>
            </div>

            {{-- Errore invio ──────────────────────────────────────────── --}}
            <div x-show="error" x-cloak
                 class="px-4 py-2 text-sm text-rose-400 bg-rose-900/20 border-t border-rose-800/30"
                 x-text="error"></div>

            {{-- Input ────────────────────────────────────────────────── --}}
            <div class="pc-input-bar">
                <textarea
                    x-model="draft"
                    @keydown.enter.prevent="if(!$event.shiftKey) send()"
                    @input="autoResize($el)"
                    class="pc-textarea"
                    rows="1"
                    placeholder="{{ __('planet_chat.placeholder') }}"
                    maxlength="2000"
                    :disabled="sending"></textarea>

                <button class="pc-send-btn"
                        @click="send()"
                        :disabled="!draft.trim() || sending"
                        title="{{ __('planet_chat.send') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function planetChat({ pollUrl, storeUrl, csrf, lastId, currentUserId, todayLabel, yesterdayLabel }) {
        return {
            messages: [],
            draft: '',
            sending: false,
            error: '',
            lastId: lastId,
            currentUserId: currentUserId,
            pollTimer: null,
            POLL_INTERVAL: 3000,

            init() {
                // Aspetta che il DOM sia pronto prima di scrollare al fondo
                this.$nextTick(() => {
                    this.scrollToBottomInstant();
                    this.startPolling();
                });
            },

            startPolling() {
                this.pollTimer = setInterval(() => this.poll(), this.POLL_INTERVAL);
            },

            async poll() {
                try {
                    const res = await fetch(`${pollUrl}?since=${this.lastId}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.messages && data.messages.length > 0) {
                        this.messages.push(...data.messages);
                        this.lastId = data.messages[data.messages.length - 1].id;
                        this.scrollBottom();
                    }
                } catch (e) {
                    // silenzioso — rete temporaneamente assente
                }
            },

            async send() {
                const body = this.draft.trim();
                if (!body || this.sending) return;
                this.sending = true;
                this.error   = '';
                try {
                    const res = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ body }),
                    });
                    if (!res.ok) {
                        this.error = '{{ __("planet_chat.error_send") }}';
                        return;
                    }
                    const data = await res.json();
                    this.messages.push(data.message);
                    this.lastId = data.message.id;
                    this.draft  = '';
                    this.scrollBottom();
                    const ta = this.$el.querySelector('.pc-textarea');
                    if (ta) ta.style.height = 'auto';
                } catch (e) {
                    this.error = '{{ __("planet_chat.error_send") }}';
                } finally {
                    this.sending = false;
                }
            },

            scrollBottom() {
                this.$nextTick(() => {
                    const box = document.getElementById('pc-scroll');
                    if (box) box.scrollTop = box.scrollHeight;
                });
            },

            scrollToBottomInstant() {
                const box = document.getElementById('pc-scroll');
                if (box) box.scrollTop = box.scrollHeight;
            },

            autoResize(el) {
                el.style.height = 'auto';
                el.style.height = Math.min(el.scrollHeight, 112) + 'px';
            },

            formatDate(dateStr) {
                const today     = new Date();
                const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
                const fmt = (d) => d.getDate().toString().padStart(2,'0') + '/'
                                 + (d.getMonth()+1).toString().padStart(2,'0') + '/'
                                 + d.getFullYear();
                if (dateStr === fmt(today))     return todayLabel;
                if (dateStr === fmt(yesterday)) return yesterdayLabel;
                return dateStr;
            },

            destroy() {
                if (this.pollTimer) clearInterval(this.pollTimer);
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
