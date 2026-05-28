<x-app-layout>

<x-slot name="header">
    <div class="w-full rounded-[2rem] bg-[linear-gradient(135deg,#425767_0%,#4d6474_52%,#5b7d4b_100%)] p-6 text-white shadow-[0_22px_60px_rgba(66,87,103,0.22)]">
        <p class="text-xs uppercase tracking-[0.24em] text-white/70">Il mio profilo</p>
        <h1 class="mt-2 font-serif text-2xl font-semibold sm:text-3xl">I miei inviti</h1>
        <p class="mt-1 text-sm text-white/70">Invita amici e monitora lo stato dei tuoi inviti.</p>
    </div>
</x-slot>

<div class="pb-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        {{-- Feedback flash --}}
        @if (session('invite_success'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-700">
                ✓ {{ session('invite_success') }}
            </div>
        @endif

        {{-- KPI --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-stone-900">{{ $stats['email_sent'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Inviti via email</p>
            </div>
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $stats['email_accepted'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Accettati</p>
            </div>
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-sky-600">{{ $stats['link_total'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Via link referral</p>
            </div>
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-amber-500">{{ $stats['link_subscribed'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Con abbonamento</p>
            </div>
        </div>

        {{-- ── Form invita via email ──────────────────────────────────────────── --}}
        <div class="km-panel mt-5 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Invia un invito via email</p>
            <p class="mt-1 text-xs text-stone-400">Il tuo amico riceverà un'email con il link per registrarsi direttamente nel tuo Pianeta.</p>

            <form method="POST" action="{{ route('my.invites.invite') }}" class="mt-4 space-y-3">
                @csrf

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="invite_email" class="block text-xs font-medium text-stone-600 mb-1">Email *</label>
                        <input id="invite_email" type="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="amico@esempio.com"
                               class="km-input w-full {{ $errors->has('email') ? 'border-rose-400' : '' }}">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="invite_chapter" class="block text-xs font-medium text-stone-600 mb-1">Pianeta *</label>
                        @if ($userPlanets->isEmpty())
                            <p class="text-xs text-stone-400 italic mt-2">Non appartieni ancora a nessun Pianeta.</p>
                        @else
                            <select id="invite_chapter" name="chapter_id"
                                    class="km-input w-full {{ $errors->has('chapter_id') ? 'border-rose-400' : '' }}">
                                @foreach ($userPlanets as $planet)
                                    <option value="{{ $planet->id }}" @selected(old('chapter_id') == $planet->id)>
                                        {{ $planet->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('chapter_id')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>

                <div>
                    <label for="invite_message" class="block text-xs font-medium text-stone-600 mb-1">Messaggio personale <span class="text-stone-400">(facoltativo)</span></label>
                    <textarea id="invite_message" name="message" rows="2"
                              placeholder="Ciao! Ti invito a entrare nella mia rete professionale su Kommunity…"
                              class="km-input w-full resize-none">{{ old('message') }}</textarea>
                </div>

                @if ($userPlanets->isNotEmpty())
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition"
                            style="background: linear-gradient(135deg,#537d4d,#3f6239);">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        Invia invito
                    </button>
                @endif
            </form>
        </div>

        {{-- ── Tabella inviti via email ─────────────────────────────────────────── --}}
        <div class="km-panel mt-5 overflow-hidden p-0">
            <div class="border-b border-stone-200 bg-stone-50 px-5 py-3 flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Inviti via email inviati</p>
                <span class="rounded-full bg-stone-200 px-2 py-0.5 text-xs font-semibold text-stone-600">{{ $sentInvitations->count() }}</span>
            </div>

            @if ($sentInvitations->isEmpty())
                <div class="flex flex-col items-center justify-center gap-2 px-6 py-10 text-center">
                    <svg class="h-8 w-8 text-stone-300" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <p class="text-sm font-semibold text-stone-600">Nessun invito via email ancora</p>
                    <p class="text-xs text-stone-400">Usa il form qui sopra per invitare qualcuno direttamente.</p>
                </div>
            @else
                {{-- Header tabella --}}
                <div class="border-b border-stone-200 bg-stone-50/60 px-5 py-2.5">
                    <div class="grid grid-cols-[1fr_auto_auto_auto] items-center gap-3 text-xs font-semibold uppercase tracking-[0.12em] text-stone-400">
                        <span>Email · Pianeta</span>
                        <span class="hidden sm:block text-center w-20">Inviato</span>
                        <span class="text-center w-24">Stato</span>
                        <span class="w-16"></span>
                    </div>
                </div>

                <div class="divide-y divide-stone-100">
                    @foreach ($sentInvitations as $inv)
                        @php
                            $statusColor = match($inv->status) {
                                'accepted' => 'bg-emerald-100 text-emerald-700',
                                'pending'  => 'bg-amber-100 text-amber-700',
                                'expired'  => 'bg-stone-100 text-stone-500',
                                'revoked'  => 'bg-rose-100 text-rose-600',
                                default    => 'bg-stone-100 text-stone-500',
                            };
                            $statusLabel = match($inv->status) {
                                'accepted' => '✓ Accettato',
                                'pending'  => '⏳ In attesa',
                                'expired'  => 'Scaduto',
                                'revoked'  => 'Annullato',
                                default    => ucfirst($inv->status),
                            };
                        @endphp
                        <div class="grid grid-cols-[1fr_auto_auto_auto] items-center gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-stone-800">{{ $inv->email }}</p>
                                <p class="mt-0.5 text-xs text-stone-400">
                                    {{ $inv->chapter?->name ?? '—' }}
                                    <span class="sm:hidden">· {{ $inv->created_at->format('d/m/Y') }}</span>
                                </p>
                            </div>

                            <span class="hidden sm:block w-20 text-center text-xs text-stone-500">
                                {{ $inv->created_at->format('d/m/Y') }}
                            </span>

                            <span class="flex w-24 justify-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </span>

                            <span class="flex w-16 justify-end">
                                @if ($inv->status === 'pending')
                                    <form method="POST" action="{{ route('my.invites.revoke', $inv) }}"
                                          onsubmit="return confirm('Annullare questo invito?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs font-medium text-rose-500 hover:text-rose-700 hover:underline">
                                            Annulla
                                        </button>
                                    </form>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Tabella registrati via link referral ────────────────────────────── --}}
        <div class="km-panel mt-5 overflow-hidden p-0">
            <div class="border-b border-stone-200 bg-stone-50 px-5 py-3 flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Registrati tramite il tuo link</p>
                <span class="rounded-full bg-stone-200 px-2 py-0.5 text-xs font-semibold text-stone-600">{{ $invitedUsers->count() }}</span>
            </div>

            @if ($invitedUsers->isEmpty())
                <div class="flex flex-col items-center justify-center gap-2 px-6 py-10 text-center">
                    <svg class="h-8 w-8 text-stone-300" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 14.094A5.973 5.973 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/>
                    </svg>
                    <p class="text-sm font-semibold text-stone-600">Nessuno ancora</p>
                    <p class="text-xs text-stone-400">Condividi il tuo link dal profilo per far crescere la rete.</p>
                </div>
            @else
                {{-- Header --}}
                <div class="border-b border-stone-200 bg-stone-50/60 px-5 py-2.5">
                    <div class="grid grid-cols-[1fr_auto_auto_auto_auto] items-center gap-4 text-xs font-semibold uppercase tracking-[0.12em] text-stone-400">
                        <span>Membro</span>
                        <span class="hidden sm:block text-center w-20">Registrato</span>
                        <span class="text-center w-10" title="Email verificata">Email</span>
                        <span class="text-center w-10" title="Profilo completato">Profilo</span>
                        <span class="text-center w-10" title="Abbonamento">Abb.</span>
                    </div>
                </div>

                <div class="divide-y divide-stone-100">
                    @foreach ($invitedUsers as $invited)
                        @php
                            $emailVerified   = (bool) $invited->email_verified_at;
                            $profileComplete = (bool) $invited->memberProfile?->onboarding_completed;
                            $hasSubscription = $invited->subscriptions->isNotEmpty();
                            $slug            = $invited->memberOnepage?->slug;
                        @endphp
                        <div class="grid grid-cols-[1fr_auto_auto_auto_auto] items-center gap-4 px-5 py-3">
                            <div class="min-w-0">
                                @if ($slug)
                                    <a href="{{ route('members.show', $slug) }}"
                                       class="truncate text-sm font-semibold text-stone-800 hover:text-[color:var(--km-accent-strong)] transition">
                                        {{ $invited->name }}
                                    </a>
                                @else
                                    <span class="truncate text-sm font-semibold text-stone-800">{{ $invited->name }}</span>
                                @endif
                                <p class="mt-0.5 text-xs text-stone-400 sm:hidden">
                                    {{ $invited->created_at->format('d/m/Y') }}
                                </p>
                            </div>

                            <span class="hidden sm:block w-20 text-center text-xs text-stone-500">
                                {{ $invited->created_at->format('d/m/Y') }}
                            </span>

                            <span class="flex w-10 justify-center" title="{{ $emailVerified ? 'Email verificata' : 'Non verificata' }}">
                                @if ($emailVerified)
                                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="h-4 w-4 text-stone-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>

                            <span class="flex w-10 justify-center" title="{{ $profileComplete ? 'Profilo completato' : 'Non completato' }}">
                                @if ($profileComplete)
                                    <svg class="h-4 w-4 text-sky-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="h-4 w-4 text-stone-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>

                            <span class="flex w-10 justify-center" title="{{ $hasSubscription ? 'Con abbonamento' : 'Nessun abbonamento' }}">
                                @if ($hasSubscription)
                                    <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="h-4 w-4 text-stone-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-stone-100 bg-stone-50 px-5 py-3">
                    <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-stone-400">
                        <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg> Email verificata</span>
                        <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg> Profilo completato</span>
                        <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg> Abbonamento attivo</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6">
            <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-[color:var(--km-accent-strong)] hover:underline">
                ← Torna al profilo
            </a>
        </div>

    </div>
</div>

</x-app-layout>
