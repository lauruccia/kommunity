<x-app-layout>
    <x-slot name="header">
        <div class="km-portal-panel p-6">
            <div>
                <div class="km-portal-eyebrow mb-3">Kommunity</div>
                <h1 class="km-portal-title text-3xl">Piani di abbonamento</h1>
                <p class="km-portal-muted mt-2">Scegli il piano più adatto alla tua presenza nella community.</p>
            </div>
        </div>
    </x-slot>

    <div class="km-portal-bg km-portal-page km-shell py-10">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                <svg class="h-5 w-5 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                <svg class="h-5 w-5 shrink-0 text-rose-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 112 0v-4a1 1 0 00-2 0v4zm0 4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Stato abbonamento attuale --}}
        @if ($currentSubscription)
            @php
                $subscriptionCardIsLight = in_array($currentSubscription->status->value, ['active', 'trial', 'pending'], true);
            @endphp
            <div class="mb-8 rounded-[1.6rem] border p-6
                @if($currentSubscription->status->value === 'active') border-emerald-200 bg-emerald-50
                @elseif($currentSubscription->status->value === 'trial') border-sky-200 bg-sky-50
                @elseif($currentSubscription->status->value === 'pending') border-amber-200 bg-amber-50
                @else border-white/10 bg-white/[.045]
                @endif">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold
                                @if($currentSubscription->status->value === 'active') text-emerald-700
                                @elseif($currentSubscription->status->value === 'trial') text-sky-700
                                @elseif($currentSubscription->status->value === 'pending') text-amber-700
                                @else text-white/75
                                @endif">
                                {{ $currentSubscription->status->label() }}
                            </span>
                            <span class="{{ $subscriptionCardIsLight ? 'text-stone-400' : 'text-white/45' }}">·</span>
                            <span class="text-sm {{ $subscriptionCardIsLight ? 'text-stone-700' : 'text-white/75' }}">{{ $currentSubscription->plan->name }}</span>
                        </div>
                        @if($currentSubscription->status->value === 'pending')
                            <p class="mt-1 text-xs text-amber-600">La tua richiesta è in attesa di approvazione. Ti avviseremo non appena verificheremo il pagamento.</p>
                        @elseif($currentSubscription->status->value === 'trial')
                            <p class="mt-1 text-xs text-sky-600">Periodo di prova attivo fino al {{ $currentSubscription->trial_ends_at?->format('d/m/Y') ?? '—' }}.</p>
                        @elseif($currentSubscription->status->value === 'active')
                            <p class="mt-1 text-xs text-emerald-600">
                                Attivo
                                @if($currentSubscription->ends_at)
                                    fino al {{ $currentSubscription->ends_at->format('d/m/Y') }}
                                @else
                                    · nessuna scadenza
                                @endif
                            </p>
                        @endif
                    </div>
                    @if($currentSubscription->status->value === 'pending')
                        <form method="POST" action="{{ route('subscriptions.cancel', $currentSubscription) }}"
                              onsubmit="return confirm('Annullare la richiesta?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-xl border border-rose-200 bg-white/10 px-4 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50">
                                Annulla richiesta
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        {{-- Piani disponibili --}}
        @if ($plans->isEmpty())
            <div class="rounded-[1.6rem] border border-dashed border-stone-300 bg-white/[.045] py-14 text-center text-white/60">
                Nessun piano disponibile al momento. Contatta l'amministratore.
            </div>
        @else
            <div x-data="{ billing: 'monthly' }" class="space-y-8">

                {{-- Toggle mensile/annuale --}}
                <div class="flex justify-center">
                    <div class="inline-flex items-center rounded-full border border-white/10 bg-white/10 p-1 shadow-sm">
                        <button type="button"
                                @click="billing = 'monthly'"
                                :class="billing === 'monthly' ? 'bg-[color:var(--km-accent)] text-white shadow' : 'text-white/60 hover:text-white/80'"
                                class="rounded-full px-5 py-2 text-sm font-semibold transition">
                            Mensile
                        </button>
                        <button type="button"
                                @click="billing = 'yearly'"
                                :class="billing === 'yearly' ? 'bg-[color:var(--km-accent)] text-white shadow' : 'text-white/60 hover:text-white/80'"
                                class="rounded-full px-5 py-2 text-sm font-semibold transition">
                            Annuale
                            <span class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Risparmia</span>
                        </button>
                    </div>
                </div>

                {{-- Cards piani --}}
                <div class="grid gap-6 md:grid-cols-{{ min($plans->count(), 3) }}">
                    @foreach ($plans as $plan)
                        @php
                            $isCurrentPlan = $currentSubscription && $currentSubscription->plan_id === $plan->id
                                          && in_array($currentSubscription->status->value, ['active','trial','pending']);
                            $canRequest = ! $currentSubscription || ! in_array($currentSubscription->status->value, ['active','trial','pending']);
                        @endphp
                        <div class="km-portal-panel flex flex-col p-7 @if($plan->includesPage()) ring-2 ring-[color:var(--km-accent)] ring-offset-2 @endif">

                            @if($plan->includesPage())
                                <div class="mb-4 self-start">
                                    <span class="km-chip">Piano completo</span>
                                </div>
                            @endif

                            <h2 class="text-xl font-semibold text-white">{{ $plan->name }}</h2>

                            @if($plan->description)
                                <p class="mt-2 text-sm text-white/60">{{ $plan->description }}</p>
                            @endif

                            {{-- Prezzo --}}
                            <div class="mt-5">
                                <div x-show="billing === 'monthly'">
                                    @if((float)$plan->price_monthly === 0.0)
                                        <span class="text-4xl font-black text-white">Gratuito</span>
                                    @else
                                        <span class="text-4xl font-black text-white">€{{ number_format((float)$plan->price_monthly, 0, ',', '.') }}</span>
                                        <span class="text-white/45">/mese</span>
                                    @endif
                                </div>
                                <div x-show="billing === 'yearly'" x-cloak>
                                    @if((float)$plan->price_yearly === 0.0)
                                        <span class="text-4xl font-black text-white">Gratuito</span>
                                    @else
                                        <span class="text-4xl font-black text-white">€{{ number_format((float)$plan->price_yearly, 0, ',', '.') }}</span>
                                        <span class="text-white/45">/anno</span>
                                    @endif
                                </div>
                            </div>

                            @if($plan->hasTrial())
                                <p class="mt-1.5 text-xs font-medium text-emerald-600">✓ {{ $plan->trial_days }} giorni di prova gratuita</p>
                            @endif

                            {{-- Incluso --}}
                            <ul class="mt-5 space-y-2.5 flex-1">
                                <li class="flex items-start gap-2.5 text-sm text-white/80">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    Presenza nella directory membri
                                </li>
                                @if($plan->includesPage())
                                    <li class="flex items-start gap-2.5 text-sm text-white/80">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                        Pagina personale dedicata
                                    </li>
                                @endif
                                @if($plan->features)
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-start gap-2.5 text-sm text-white/80">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                            {{ $feature['item'] ?? $feature }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            {{-- CTA --}}
                            <div class="mt-7">
                                @if($isCurrentPlan)
                                    <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                        Piano selezionato
                                    </div>
                                @elseif($canRequest)
                                    <button type="button"
                                            @click="$dispatch('km:open-request-modal', { plan_id: {{ $plan->id }}, plan_name: '{{ addslashes($plan->name) }}' })"
                                            class="km-button-primary w-full justify-center">
                                        Richiedi questo piano
                                    </button>
                                @else
                                    <div class="rounded-2xl border border-white/10 bg-white/[.045] px-4 py-3 text-center text-sm text-white/45">
                                        Hai già un abbonamento attivo
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Nota metodi pagamento --}}
                <div class="rounded-2xl border border-white/10 bg-white/[.045] px-6 py-5">
                    <p class="text-sm font-medium text-white/80 mb-3">Metodi di pagamento accettati</p>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2 text-sm text-white/75">
                            <svg class="h-5 w-5 text-white/45" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 00-2 2v1h18V6a2 2 0 00-2-2H3zm16 5H1v5a2 2 0 002 2h14a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 010 2H5a1 1 0 01-1-1zm5-1a1 1 0 000 2h1a1 1 0 100-2H9z"/></svg>
                            Carta di credito/debito
                        </div>
                        <div class="flex items-center gap-2 text-sm text-white/75">
                            <svg class="h-5 w-5 text-white/45" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2h1v1H4v-1h1v-2a1 1 0 011-1h8a1 1 0 011 1z" clip-rule="evenodd"/></svg>
                            Bonifico bancario
                        </div>
                        <div class="flex items-center gap-2 text-sm text-white/75">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 24 24" fill="currentColor"><path d="M7.076 21.337H2.47a.641.641 0 01-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106z"/></svg>
                            PayPal
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-white/45">Dopo aver inviato la richiesta, effettua il pagamento e inserisci il riferimento nel form. L'abbonamento verrà attivato dopo la verifica da parte dell'amministratore.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL: Richiedi piano --}}
    <div x-data="{ open: false, plan_id: null, plan_name: '' }"
         @km:open-request-modal.window="open = true; plan_id = $event.detail.plan_id; plan_name = $event.detail.plan_name"
         x-show="open"
         x-cloak
         style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);" @click="open = false"></div>
        <div class="km-portal-modal relative w-full max-w-md p-7"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <button @click="open = false" class="absolute right-5 top-5 text-white/45 hover:text-white/80">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>

            <h2 class="text-lg font-semibold text-white mb-1" x-text="'Richiedi: ' + plan_name"></h2>
            <p class="text-sm text-white/60 mb-5">Scegli il metodo di pagamento e inserisci i dettagli. L'admin attiverà l'abbonamento dopo la verifica.</p>

            <form method="POST" action="{{ route('subscriptions.request') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="plan_id" :value="plan_id">

                <div>
                    <label class="block text-sm font-medium text-white/80 mb-1">Metodo di pagamento</label>
                    <select name="payment_method" class="km-portal-input" required>
                        <option value="">Seleziona metodo…</option>
                        <option value="bank_transfer">Bonifico bancario</option>
                        <option value="card">Carta di credito/debito</option>
                        <option value="paypal">PayPal</option>
                    </select>
                    @error('payment_method') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/80 mb-1">Riferimento pagamento <span class="text-white/45 font-normal">(opzionale)</span></label>
                    <input type="text" name="payment_reference" class="km-portal-input"
                           placeholder="es. CRO bonifico, ID transazione PayPal…">
                    <p class="mt-1 text-xs text-white/45">Puoi aggiungerlo anche dopo se non ce l'hai ancora.</p>
                    @error('payment_reference') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white/80 mb-1">Note aggiuntive <span class="text-white/45 font-normal">(opzionale)</span></label>
                    <textarea name="payment_notes" rows="2" class="km-portal-input"
                              placeholder="Informazioni utili per l'admin…"></textarea>
                    @error('payment_notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                            class="rounded-full border border-white/10 bg-white/[.045] px-5 py-2 text-sm font-medium text-white/80 hover:bg-white/[.075]">
                        Annulla
                    </button>
                    <button type="submit" class="km-button-primary px-6 py-2">
                        Invia richiesta
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('modals')
    @endpush

</x-app-layout>
