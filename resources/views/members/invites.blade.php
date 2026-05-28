<x-app-layout>

<x-slot name="header">
    <div class="w-full rounded-[2rem] bg-[linear-gradient(135deg,#425767_0%,#4d6474_52%,#5b7d4b_100%)] p-6 text-white shadow-[0_22px_60px_rgba(66,87,103,0.22)]">
        <p class="text-xs uppercase tracking-[0.24em] text-white/70">Il mio profilo</p>
        <h1 class="mt-2 font-serif text-2xl font-semibold sm:text-3xl">I miei inviti</h1>
        <p class="mt-1 text-sm text-white/70">Monitora le persone che hai invitato su Kommunity.</p>
    </div>
</x-slot>

<div class="pb-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        {{-- KPI --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-stone-900">{{ $stats['total'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Invitati totali</p>
            </div>
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $stats['verified'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Email verificata</p>
            </div>
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-sky-600">{{ $stats['profile_done'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Profilo completato</p>
            </div>
            <div class="km-panel p-4 text-center">
                <p class="text-2xl font-bold text-amber-500">{{ $stats['subscribed'] }}</p>
                <p class="mt-0.5 text-xs text-stone-500">Con abbonamento</p>
            </div>
        </div>

        {{-- Link di invito rapido --}}
        <div class="km-panel mt-5 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Il tuo link di invito</p>
            @php
                $myReferralUrl = url(auth()->user()->referralRegistrationUrl());
                $waUrl = 'https://wa.me/?text=' . urlencode('Ciao! Ti invito a unirti alla mia rete su Kommunity 👋' . "\n" . $myReferralUrl);
            @endphp
            <div class="mt-3 flex items-center gap-2">
                <input type="text" readonly value="{{ $myReferralUrl }}"
                       class="km-input flex-1 bg-stone-50 text-xs"
                       onclick="this.select()">
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $myReferralUrl }}').then(() => { this.textContent='✓ Copiato'; setTimeout(()=>this.textContent='Copia',1500); })"
                        class="shrink-0 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                    Copia
                </button>
                <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                   class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-[#25D366] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#1ebe5e]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.05 4.91A9.82 9.82 0 0012.03 2C6.56 2 2.12 6.43 2.12 11.9c0 1.75.46 3.46 1.33 4.96L2 22l5.29-1.39a9.9 9.9 0 004.74 1.2h.01c5.47 0 9.9-4.44 9.9-9.91a9.83 9.83 0 00-2.89-6.99zm-7.02 15.22h-.01a8.23 8.23 0 01-4.19-1.14l-.3-.18-3.14.82.84-3.06-.2-.31a8.2 8.2 0 01-1.26-4.36c0-4.53 3.69-8.22 8.24-8.22a8.16 8.16 0 015.82 2.41 8.16 8.16 0 012.4 5.82c0 4.54-3.69 8.22-8.2 8.22zm4.5-6.16c-.25-.12-1.47-.72-1.7-.8-.23-.09-.39-.12-.56.12-.16.25-.64.8-.78.96-.14.17-.28.19-.53.07-.25-.12-1.03-.38-1.96-1.22-.73-.64-1.22-1.43-1.36-1.67-.14-.24-.01-.37.11-.49.11-.11.25-.28.37-.42.12-.14.16-.24.25-.4.08-.17.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.86-.2-.48-.41-.42-.56-.42h-.48c-.16 0-.43.06-.65.31-.22.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.17 1.73 2.64 4.19 3.7.58.25 1.03.39 1.38.5.58.18 1.11.16 1.53.1.47-.07 1.47-.6 1.68-1.19.21-.59.21-1.09.14-1.19-.06-.1-.22-.16-.47-.28z"/></svg>
                    WhatsApp
                </a>
            </div>
        </div>

        {{-- Tabella invitati --}}
        <div class="km-panel mt-5 overflow-hidden p-0">

            @if ($invitedUsers->isEmpty())
                <div class="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
                    <svg class="h-10 w-10 text-stone-300" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 14.094A5.973 5.973 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-stone-700">Nessun invitato ancora</p>
                        <p class="mt-1 text-sm text-stone-400">Condividi il tuo link di invito per far crescere la tua rete.</p>
                    </div>
                </div>
            @else
                {{-- Header tabella --}}
                <div class="border-b border-stone-200 bg-stone-50 px-5 py-3">
                    <div class="grid grid-cols-[1fr_auto_auto_auto_auto] items-center gap-4 text-xs font-semibold uppercase tracking-[0.14em] text-stone-400">
                        <span>Membro</span>
                        <span class="hidden sm:block text-center">Registrato</span>
                        <span class="text-center">Email</span>
                        <span class="text-center">Profilo</span>
                        <span class="text-center">Abbonamento</span>
                    </div>
                </div>

                {{-- Righe --}}
                <div class="divide-y divide-stone-100">
                    @foreach ($invitedUsers as $invited)
                        @php
                            $emailVerified   = (bool) $invited->email_verified_at;
                            $profileComplete = (bool) $invited->memberProfile?->onboarding_completed;
                            $hasSubscription = $invited->subscriptions->isNotEmpty();
                            $slug            = $invited->memberOnepage?->slug;
                        @endphp
                        <div class="grid grid-cols-[1fr_auto_auto_auto_auto] items-center gap-4 px-5 py-3.5">

                            {{-- Nome + data registrazione (mobile) --}}
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

                            {{-- Data registrazione (desktop) --}}
                            <span class="hidden sm:block w-24 text-center text-xs text-stone-500">
                                {{ $invited->created_at->format('d/m/Y') }}
                            </span>

                            {{-- Email verificata --}}
                            <span class="flex w-10 justify-center" title="{{ $emailVerified ? 'Email verificata' : 'Email non verificata' }}">
                                @if ($emailVerified)
                                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="h-4 w-4 text-stone-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>

                            {{-- Profilo completato --}}
                            <span class="flex w-10 justify-center" title="{{ $profileComplete ? 'Profilo completato' : 'Profilo non completato' }}">
                                @if ($profileComplete)
                                    <svg class="h-4 w-4 text-sky-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="h-4 w-4 text-stone-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>

                            {{-- Abbonamento --}}
                            <span class="flex w-10 justify-center" title="{{ $hasSubscription ? 'Abbonamento attivo' : 'Nessun abbonamento' }}">
                                @if ($hasSubscription)
                                    <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="h-4 w-4 text-stone-300" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>

                        </div>
                    @endforeach
                </div>

                {{-- Legenda icone --}}
                <div class="border-t border-stone-100 bg-stone-50 px-5 py-3">
                    <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-stone-400">
                        <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg> Email verificata</span>
                        <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg> Profilo completato</span>
                        <span class="flex items-center gap-1"><svg class="h-3.5 w-3.5 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg> Abbonamento attivo</span>
                    </div>
                </div>
            @endif

        </div>

        {{-- Torna al profilo --}}
        <div class="mt-6">
            <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-[color:var(--km-accent-strong)] hover:underline">
                ← Torna al profilo
            </a>
        </div>

    </div>
</div>

</x-app-layout>
