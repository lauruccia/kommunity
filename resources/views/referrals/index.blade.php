<x-app-layout>
    <x-slot name="header">
        <div class="km-panel p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Referenze business</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold text-stone-950 sm:text-3xl lg:text-4xl">
                Opportunità e introduzioni tra membri
            </h1>
            <p class="mt-2 text-sm leading-7 text-stone-600">
                Invia opportunità, traccia lo stato e tieni ordinata la pipeline relazionale della community.
            </p>
            <div class="mt-3 flex items-center gap-2 text-sm text-amber-500">
                <span class="tracking-[0.2em]" aria-label="Valutazione recensione">★★★★★</span>
                <span class="text-stone-600">Referenze qualificate dalla rete Kommunity</span>
            </div>

            {{-- STATS --}}
            <div class="mt-4 flex flex-wrap gap-3">
                <div class="flex items-center gap-2 rounded-2xl border border-stone-200 bg-white/70 px-4 py-2 text-sm">
                    <span class="font-semibold text-stone-950">{{ $summary['sent'] }}</span>
                    <span class="text-stone-500">inviate</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl border border-stone-200 bg-white/70 px-4 py-2 text-sm">
                    <span class="font-semibold text-stone-950">{{ $summary['received'] }}</span>
                    <span class="text-stone-500">ricevute</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl border border-stone-200 bg-white/70 px-4 py-2 text-sm">
                    <span class="font-semibold text-stone-950">{{ $summary['open'] }}</span>
                    <span class="text-stone-500">aperte</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl border border-stone-200 bg-white/70 px-4 py-2 text-sm">
                    <span class="font-semibold text-stone-950">€ {{ number_format($summary['value'], 0, ',', '.') }}</span>
                    <span class="text-stone-500">pipeline</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="km-portal-bg km-portal-page pb-16 pt-6">
        <div class="km-shell mt-6 grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">

            {{-- SIDEBAR: NUOVA REFERENZA --}}
            <aside class="space-y-4">
                <div class="km-portal-panel p-6">
                    <h2 class="text-lg font-semibold text-white">Nuova referenza</h2>
                    <p class="mt-1 text-sm leading-6 text-white/60">
                        Puoi inviare una referenza solo a membri con cui hai un one-to-one completato e confermato da entrambi.
                    </p>

                    {{-- FLASH --}}
                    @if (session('status') === 'referral-created')
                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            ✓ Referenza inviata correttamente.
                        </div>
                    @elseif (session('status') === 'referral-updated')
                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            ✓ Stato referenza aggiornato.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <p class="font-semibold text-rose-900">Controlla i dati inseriti.</p>
                            <ul class="mt-2 list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($members->isEmpty())
                        <div class="mt-4 rounded-2xl border border-white/10 bg-white/[.045] px-4 py-4 text-sm text-white/75">
                            <p class="font-medium text-white/90">Nessun membro idoneo</p>
                            <p class="mt-1">Completa e conferma un one-to-one da entrambe le parti per poter inviare una referenza.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('referrals.store') }}" class="mt-5 space-y-3">
                            @csrf

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-white/75">Destinatario *</label>
                                <select name="recipient_id" class="km-portal-input" required>
                                    <option value="">Seleziona destinatario…</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" @selected((string) old('recipient_id') === (string) $member->id)>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-white/75">Titolo opportunità *</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="km-portal-input" placeholder="Es. Progetto sito web e-commerce" required>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-white/75">Descrizione *</label>
                                <textarea name="description" rows="4" class="km-portal-input" placeholder="Descrivi l'opportunità, il contesto e cosa si cerca…" required>{{ old('description') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-white/75">Azienda</label>
                                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="km-portal-input" placeholder="Nome azienda">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-white/75">Referente</label>
                                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="km-portal-input" placeholder="Nome contatto">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-white/75">Valore stimato (€)</label>
                                    <input type="number" step="0.01" min="0" name="estimated_value" value="{{ old('estimated_value') }}" class="km-portal-input" placeholder="0">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-white/75">Priorità</label>
                                    <select name="priority" class="km-portal-input">
                                        <option value="low" @selected(old('priority') === 'low')>🟢 Bassa</option>
                                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>🟡 Media</option>
                                        <option value="high" @selected(old('priority') === 'high')>🔴 Alta</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-white/75">Note interne <span class="font-normal text-white/45">(opzionale)</span></label>
                                <textarea name="notes" rows="2" class="km-portal-input" placeholder="Contesto o info riservate per il destinatario">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" class="km-button-primary w-full">Invia referenza</button>
                        </form>
                    @endif
                </div>
            </aside>

            {{-- MAIN --}}
            <section class="min-w-0 space-y-5">

                {{-- FILTRI --}}
                <div class="km-portal-panel p-5">
                    <form method="GET" action="{{ route('referrals.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                        <div class="flex-1 min-w-[180px]">
                            <label class="mb-1.5 block text-xs font-medium text-white/75">Cerca</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-portal-input" placeholder="Titolo, azienda, contatto…">
                        </div>
                        <div class="min-w-[160px]">
                            <label class="mb-1.5 block text-xs font-medium text-white/75">Stato</label>
                            <select name="status" class="km-portal-input">
                                <option value="">Tutti gli stati</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[140px]">
                            <label class="mb-1.5 block text-xs font-medium text-white/75">Priorità</label>
                            <select name="priority" class="km-portal-input">
                                <option value="">Tutte</option>
                                <option value="high" @selected(($filters['priority'] ?? '') === 'high')>🔴 Alta</option>
                                <option value="medium" @selected(($filters['priority'] ?? '') === 'medium')>🟡 Media</option>
                                <option value="low" @selected(($filters['priority'] ?? '') === 'low')>🟢 Bassa</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="km-button-primary">Filtra</button>
                            @if (array_filter($filters))
                                <a href="{{ route('referrals.index') }}" class="km-button-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- REFERENZE RICEVUTE --}}
                <div class="km-portal-panel overflow-hidden p-0">
                    <div class="border-b px-6 py-4" style="border-color: var(--km-line);">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/60">Da gestire</p>
                        <h2 class="mt-1 text-xl font-semibold text-white">Referenze ricevute</h2>
                    </div>

                    <div>
                        @forelse ($receivedReferrals as $referral)
                            @php
                                $statusColors = [
                                    'sent'        => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'in_charge'   => 'bg-violet-50 text-violet-700 border-violet-200',
                                    'contacted'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'negotiating' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'won'         => 'bg-green-50 text-green-700 border-green-200',
                                    'lost'        => 'bg-red-50 text-red-700 border-red-200',
                                    'archived'    => 'bg-white/[.075] text-white/60 border-white/10',
                                ];
                                $sc = $statusColors[$referral->status->value] ?? 'bg-white/[.075] text-white/60 border-white/10';
                                $priorityLabel = match($referral->priority) { 'high' => '🔴 Alta', 'low' => '🟢 Bassa', default => '🟡 Media' };
                            @endphp
                            <div class="border-b p-5" style="border-color: var(--km-line);">

                                <div class="flex flex-wrap items-start gap-3">
                                    {{-- STATO + PRIORITÀ --}}
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $sc }}">
                                            {{ $referral->status->label() }}
                                        </span>
                                        <span class="inline-block rounded-full border border-white/10 bg-white/[.045] px-2.5 py-0.5 text-[11px] font-medium">
                                            {{ $priorityLabel }}
                                        </span>
                                    </div>
                                </div>

                                <p class="mt-2 font-semibold text-white">{{ $referral->title }}</p>
                                <p class="mt-1 text-xs tracking-[0.2em] text-amber-300" aria-label="Valutazione referenza">★★★★★</p>
                                <p class="mt-0.5 text-xs text-white/60">Da <span class="font-medium">{{ $referral->sender?->name ?? 'Utente eliminato' }}</span></p>
                                <p class="mt-2 text-sm leading-6 text-white/80">{{ $referral->description }}</p>

                                @if ($referral->company_name || $referral->contact_name)
                                    <p class="mt-2 text-xs text-white/60">
                                        @if ($referral->company_name) {{ $referral->company_name }} @endif
                                        @if ($referral->company_name && $referral->contact_name) · @endif
                                        @if ($referral->contact_name) {{ $referral->contact_name }} @endif
                                    </p>
                                @endif

                                @if ($referral->notes)
                                    <p class="mt-2 rounded-xl bg-white/[.045] px-3 py-2 text-xs text-white/75">
                                        <span class="font-medium">Nota:</span> {{ $referral->notes }}
                                    </p>
                                @endif
                                @if ($referral->outcome)
                                    <p class="mt-2 text-sm text-emerald-700">
                                        <span class="font-medium">Esito:</span> {{ $referral->outcome }}
                                    </p>
                                @endif

                                {{-- FORM AGGIORNA --}}
                                <details class="mt-4">
                                    <summary class="cursor-pointer text-xs font-medium text-white/60 hover:text-white/80 select-none">
                                        Aggiorna stato →
                                    </summary>
                                    <form method="POST" action="{{ route('referrals.status', $referral) }}" class="mt-3 space-y-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="km-portal-input text-sm">
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($referral->status->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="outcome" rows="2" class="km-portal-input text-sm" placeholder="Esito o aggiornamento…">{{ $referral->outcome }}</textarea>
                                        <textarea name="notes" rows="2" class="km-portal-input text-sm" placeholder="Note interne…">{{ $referral->notes }}</textarea>
                                        <button type="submit" class="km-button-secondary w-full text-sm">Salva aggiornamento</button>
                                    </form>
                                </details>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-white/45">
                                Nessuna referenza ricevuta.
                            </div>
                        @endforelse
                    </div>

                    @if ($receivedReferrals->hasPages())
                        <div class="border-t px-5 py-4" style="border-color: var(--km-line);">
                            {{ $receivedReferrals->links() }}
                        </div>
                    @endif
                </div>

                {{-- REFERENZE INVIATE --}}
                <div class="km-portal-panel overflow-hidden p-0">
                    <div class="border-b px-6 py-4" style="border-color: var(--km-line);">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/60">Archivio</p>
                        <h2 class="mt-1 text-xl font-semibold text-white">Referenze inviate</h2>
                    </div>

                    <div>
                        @forelse ($sentReferrals as $referral)
                            @php
                                $statusColors = [
                                    'sent'        => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'in_charge'   => 'bg-violet-50 text-violet-700 border-violet-200',
                                    'contacted'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'negotiating' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'won'         => 'bg-green-50 text-green-700 border-green-200',
                                    'lost'        => 'bg-red-50 text-red-700 border-red-200',
                                    'archived'    => 'bg-white/[.075] text-white/60 border-white/10',
                                ];
                                $sc = $statusColors[$referral->status->value] ?? 'bg-white/[.075] text-white/60 border-white/10';
                                $priorityLabel = match($referral->priority) { 'high' => '🔴 Alta', 'low' => '🟢 Bassa', default => '🟡 Media' };
                            @endphp
                            <div class="border-b p-5" style="border-color: var(--km-line);">

                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $sc }}">
                                            {{ $referral->status->label() }}
                                        </span>
                                        <span class="inline-block rounded-full border border-white/10 bg-white/[.045] px-2.5 py-0.5 text-[11px] font-medium">
                                            {{ $priorityLabel }}
                                        </span>
                                        @if ($referral->estimated_value)
                                            <span class="inline-block rounded-full border border-white/10 bg-white/[.045] px-2.5 py-0.5 text-[11px] font-medium text-white/75">
                                                € {{ number_format((float) $referral->estimated_value, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-2 font-semibold text-white">{{ $referral->title }}</p>
                                <p class="mt-1 text-xs tracking-[0.2em] text-amber-300" aria-label="Valutazione referenza">★★★★★</p>
                                <p class="mt-0.5 text-xs text-white/60">Per <span class="font-medium">{{ $referral->recipient?->name ?? 'Utente eliminato' }}</span></p>
                                <p class="mt-2 text-sm leading-6 text-white/80">{{ $referral->description }}</p>

                                @if ($referral->company_name || $referral->contact_name)
                                    <p class="mt-2 text-xs text-white/60">
                                        @if ($referral->company_name) {{ $referral->company_name }} @endif
                                        @if ($referral->company_name && $referral->contact_name) · @endif
                                        @if ($referral->contact_name) {{ $referral->contact_name }} @endif
                                    </p>
                                @endif

                                @if ($referral->notes)
                                    <p class="mt-2 rounded-xl bg-white/[.045] px-3 py-2 text-xs text-white/75">
                                        <span class="font-medium">Nota:</span> {{ $referral->notes }}
                                    </p>
                                @endif
                                @if ($referral->outcome)
                                    <p class="mt-2 text-sm text-emerald-700">
                                        <span class="font-medium">Esito:</span> {{ $referral->outcome }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-white/45">
                                Non hai ancora inviato referenze.
                            </div>
                        @endforelse
                    </div>

                    @if ($sentReferrals->hasPages())
                        <div class="border-t px-5 py-4" style="border-color: var(--km-line);">
                            {{ $sentReferrals->links() }}
                        </div>
                    @endif
                </div>

            </section>
        </div>
    </div>
</x-app-layout>
