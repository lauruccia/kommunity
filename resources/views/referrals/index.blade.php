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

            {{-- STATS --}}
            <div class="mt-4 flex flex-wrap gap-3">
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">{{ $summary['sent'] }}</span>
                    <span class="text-stone-500">inviate</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">{{ $summary['received'] }}</span>
                    <span class="text-stone-500">ricevute</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">{{ $summary['open'] }}</span>
                    <span class="text-stone-500">aperte</span>
                </div>
                <div class="flex items-center gap-2 rounded-2xl bg-white/60 px-4 py-2 text-sm border" style="border-color: var(--km-line);">
                    <span class="font-semibold text-stone-800">€ {{ number_format($summary['value'], 0, ',', '.') }}</span>
                    <span class="text-stone-500">pipeline</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-16">
        <div class="km-shell mt-6 grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">

            {{-- SIDEBAR: NUOVA REFERENZA --}}
            <aside class="space-y-4">
                <div class="km-panel p-6">
                    <h2 class="text-lg font-semibold text-stone-950">Nuova referenza</h2>
                    <p class="mt-1 text-sm leading-6 text-stone-500">
                        Puoi inviare una referenza solo a membri con cui hai già completato almeno un one-to-one.
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
                        <div class="mt-4 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4 text-sm text-stone-600">
                            <p class="font-medium text-stone-800">Nessun membro idoneo</p>
                            <p class="mt-1">Completa prima un one-to-one per poter inviare una referenza.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('referrals.store') }}" class="mt-5 space-y-3">
                            @csrf

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-stone-600">Destinatario *</label>
                                <select name="recipient_id" class="km-input" required>
                                    <option value="">Seleziona destinatario…</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}" @selected((string) old('recipient_id') === (string) $member->id)>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-stone-600">Titolo opportunità *</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="km-input" placeholder="Es. Progetto sito web e-commerce" required>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-stone-600">Descrizione *</label>
                                <textarea name="description" rows="4" class="km-input" placeholder="Descrivi l'opportunità, il contesto e cosa si cerca…" required>{{ old('description') }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-stone-600">Azienda</label>
                                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="km-input" placeholder="Nome azienda">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-stone-600">Referente</label>
                                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="km-input" placeholder="Nome contatto">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-stone-600">Valore stimato (€)</label>
                                    <input type="number" step="0.01" min="0" name="estimated_value" value="{{ old('estimated_value') }}" class="km-input" placeholder="0">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-stone-600">Priorità</label>
                                    <select name="priority" class="km-input">
                                        <option value="low" @selected(old('priority') === 'low')>🟢 Bassa</option>
                                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>🟡 Media</option>
                                        <option value="high" @selected(old('priority') === 'high')>🔴 Alta</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-stone-600">Note interne <span class="font-normal text-stone-400">(opzionale)</span></label>
                                <textarea name="notes" rows="2" class="km-input" placeholder="Contesto o info riservate per il destinatario">{{ old('notes') }}</textarea>
                            </div>

                            <button type="submit" class="km-button-primary w-full">Invia referenza</button>
                        </form>
                    @endif
                </div>
            </aside>

            {{-- MAIN --}}
            <section class="min-w-0 space-y-5">

                {{-- FILTRI --}}
                <div class="km-panel p-5">
                    <form method="GET" action="{{ route('referrals.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                        <div class="flex-1 min-w-[180px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Cerca</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-input" placeholder="Titolo, azienda, contatto…">
                        </div>
                        <div class="min-w-[160px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Stato</label>
                            <select name="status" class="km-input">
                                <option value="">Tutti gli stati</option>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-w-[140px]">
                            <label class="mb-1.5 block text-xs font-medium text-stone-600">Priorità</label>
                            <select name="priority" class="km-input">
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
                <div class="km-panel overflow-hidden p-0">
                    <div class="border-b px-6 py-4" style="border-color: var(--km-line);">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Da gestire</p>
                        <h2 class="mt-1 text-xl font-semibold text-stone-950">Referenze ricevute</h2>
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
                                    'archived'    => 'bg-stone-100 text-stone-500 border-stone-200',
                                ];
                                $sc = $statusColors[$referral->status->value] ?? 'bg-stone-100 text-stone-500 border-stone-200';
                                $priorityLabel = match($referral->priority) { 'high' => '🔴 Alta', 'low' => '🟢 Bassa', default => '🟡 Media' };
                            @endphp
                            <div class="border-b p-5" style="border-color: var(--km-line);">

                                <div class="flex flex-wrap items-start gap-3">
                                    {{-- STATO + PRIORITÀ --}}
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $sc }}">
                                            {{ $referral->status->label() }}
                                        </span>
                                        <span class="inline-block rounded-full border border-stone-200 bg-stone-50 px-2.5 py-0.5 text-[11px] font-medium">
                                            {{ $priorityLabel }}
                                        </span>
                                    </div>
                                </div>

                                <p class="mt-2 font-semibold text-stone-950">{{ $referral->title }}</p>
                                <p class="mt-0.5 text-xs text-stone-500">Da <span class="font-medium">{{ $referral->sender->name }}</span></p>
                                <p class="mt-2 text-sm leading-6 text-stone-700">{{ $referral->description }}</p>

                                @if ($referral->company_name || $referral->contact_name)
                                    <p class="mt-2 text-xs text-stone-500">
                                        @if ($referral->company_name) {{ $referral->company_name }} @endif
                                        @if ($referral->company_name && $referral->contact_name) · @endif
                                        @if ($referral->contact_name) {{ $referral->contact_name }} @endif
                                    </p>
                                @endif

                                @if ($referral->notes)
                                    <p class="mt-2 rounded-xl bg-stone-50 px-3 py-2 text-xs text-stone-600">
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
                                    <summary class="cursor-pointer text-xs font-medium text-stone-500 hover:text-stone-700 select-none">
                                        Aggiorna stato →
                                    </summary>
                                    <form method="POST" action="{{ route('referrals.status', $referral) }}" class="mt-3 space-y-2">
                                        @csrf @method('PATCH')
                                        <select name="status" class="km-input text-sm">
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($referral->status->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="outcome" rows="2" class="km-input text-sm" placeholder="Esito o aggiornamento…">{{ $referral->outcome }}</textarea>
                                        <textarea name="notes" rows="2" class="km-input text-sm" placeholder="Note interne…">{{ $referral->notes }}</textarea>
                                        <button type="submit" class="km-button-secondary w-full text-sm">Salva aggiornamento</button>
                                    </form>
                                </details>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-sm text-stone-400">
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
                <div class="km-panel overflow-hidden p-0">
                    <div class="border-b px-6 py-4" style="border-color: var(--km-line);">
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Archivio</p>
                        <h2 class="mt-1 text-xl font-semibold text-stone-950">Referenze inviate</h2>
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
                                    'archived'    => 'bg-stone-100 text-stone-500 border-stone-200',
                                ];
                                $sc = $statusColors[$referral->status->value] ?? 'bg-stone-100 text-stone-500 border-stone-200';
                                $priorityLabel = match($referral->priority) { 'high' => '🔴 Alta', 'low' => '🟢 Bassa', default => '🟡 Media' };
                            @endphp
                            <div class="border-b p-5" style="border-color: var(--km-line);">

                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.12em] {{ $sc }}">
                                            {{ $referral->status->label() }}
                                        </span>
                                        <span class="inline-block rounded-full border border-stone-200 bg-stone-50 px-2.5 py-0.5 text-[11px] font-medium">
                                            {{ $priorityLabel }}
                                        </span>
                                        @if ($referral->estimated_value)
                                            <span class="inline-block rounded-full border border-stone-200 bg-stone-50 px-2.5 py-0.5 text-[11px] font-medium text-stone-600">
                                                € {{ number_format((float) $referral->estimated_value, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-2 font-semibold text-stone-950">{{ $referral->title }}</p>
                                <p class="mt-0.5 text-xs text-stone-500">Per <span class="font-medium">{{ $referral->recipient->name }}</span></p>
                                <p class="mt-2 text-sm leading-6 text-stone-700">{{ $referral->description }}</p>

                                @if ($referral->company_name || $referral->contact_name)
                                    <p class="mt-2 text-xs text-stone-500">
                                        @if ($referral->company_name) {{ $referral->company_name }} @endif
                                        @if ($referral->company_name && $referral->contact_name) · @endif
                                        @if ($referral->contact_name) {{ $referral->contact_name }} @endif
                                    </p>
                                @endif

                                @if ($referral->notes)
                                    <p class="mt-2 rounded-xl bg-stone-50 px-3 py-2 text-xs text-stone-600">
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
                            <div class="px-6 py-10 text-center text-sm text-stone-400">
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
