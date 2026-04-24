<x-app-layout>
    <x-slot name="header">
        <div class="km-panel p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Referenze business</p>
                    <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Opportunita' e introduzioni tra membri</h1>
                    <p class="mt-3 text-sm leading-7 text-stone-600">Invia opportunita', traccia lo stato e tieni ordinata la pipeline relazionale della community.</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
            <aside class="km-panel p-6">
                <h2 class="text-lg font-semibold text-stone-950">Nuova referenza</h2>
                <p class="mt-2 text-sm leading-6 text-stone-600">Puoi inviare una referenza solo a membri con cui hai gia' completato almeno un one-to-one.</p>
                @if (session('status') === 'referral-created')
                    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        Referenza inviata correttamente.
                    </div>
                @elseif (session('status') === 'referral-updated')
                    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        Stato referenza aggiornato.
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold text-rose-900">Controlla i dati inseriti.</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('referrals.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <select name="recipient_id" class="km-input" required>
                        <option value="">Seleziona destinatario</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected((string) old('recipient_id') === (string) $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="title" value="{{ old('title') }}" class="km-input" placeholder="Titolo opportunita'" required>
                    <textarea name="description" rows="5" class="km-input" placeholder="Descrizione opportunita'" required>{{ old('description') }}</textarea>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" class="km-input" placeholder="Azienda o contatto">
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="km-input" placeholder="Nome referente">
                    <div class="grid gap-4 md:grid-cols-2">
                        <input type="number" step="0.01" min="0" name="estimated_value" value="{{ old('estimated_value') }}" class="km-input" placeholder="Valore stimato">
                        <select name="priority" class="km-input">
                            <option value="low" @selected(old('priority') === 'low')>Bassa</option>
                            <option value="medium" @selected(old('priority', 'medium') === 'medium')>Media</option>
                            <option value="high" @selected(old('priority') === 'high')>Alta</option>
                        </select>
                    </div>
                    <textarea name="notes" rows="3" class="km-input" placeholder="Note interne">{{ old('notes') }}</textarea>
                    @if ($members->isEmpty())
                        <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                            Nessun membro idoneo: completa prima un one-to-one per poter inviare una referenza.
                        </div>
                    @else
                        <button type="submit" class="km-button-primary w-full">Invia referenza</button>
                    @endif
                </form>
            </aside>

            <section class="space-y-6">
                <div class="grid gap-4 xl:grid-cols-4">
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Inviate</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $summary['sent'] }}</p>
                    </div>
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Ricevute</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $summary['received'] }}</p>
                    </div>
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Aperte</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $summary['open'] }}</p>
                    </div>
                    <div class="km-panel p-5">
                        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">Valore pipeline</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-950">€ {{ number_format($summary['value'], 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="km-panel p-5">
                    <form method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto]">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="km-input" placeholder="Cerca titolo, azienda, contatto o descrizione">
                        <select name="status" class="km-input">
                            <option value="">Tutti gli stati</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="priority" class="km-input">
                            <option value="">Tutte le priorita'</option>
                            <option value="low" @selected(($filters['priority'] ?? null) === 'low')>Bassa</option>
                            <option value="medium" @selected(($filters['priority'] ?? null) === 'medium')>Media</option>
                            <option value="high" @selected(($filters['priority'] ?? null) === 'high')>Alta</option>
                        </select>
                        <div class="flex gap-3">
                            <button type="submit" class="km-button-primary">Filtra</button>
                            <a href="{{ route('referrals.index') }}" class="km-button-secondary">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="km-panel p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Ricevute</p>
                            <h2 class="mt-2 text-2xl font-semibold text-stone-950">Referenze ricevute</h2>
                        </div>
                    </div>
                    <div class="mt-5 space-y-4">
                        @forelse ($receivedReferrals as $referral)
                            <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-stone-950">{{ $referral->title }}</p>
                                        <p class="mt-1 text-sm text-stone-500">Da {{ $referral->sender->name }}</p>
                                        <p class="mt-3 text-sm leading-7 text-stone-700">{{ $referral->description }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-stone-600">
                                            @if ($referral->company_name)
                                                <span class="rounded-full bg-stone-100 px-3 py-1">{{ $referral->company_name }}</span>
                                            @endif
                                            <span class="rounded-full bg-stone-100 px-3 py-1">{{ $referral->status->label() }}</span>
                                            <span class="rounded-full bg-stone-100 px-3 py-1">
                                                Priorita' {{ match($referral->priority) { 'high' => 'alta', 'low' => 'bassa', default => 'media' } }}
                                            </span>
                                        </div>
                                        @if ($referral->notes)
                                            <p class="mt-3 text-sm text-stone-500">Note: {{ $referral->notes }}</p>
                                        @endif
                                        @if ($referral->outcome)
                                            <p class="mt-2 text-sm text-emerald-700">Esito: {{ $referral->outcome }}</p>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('referrals.status', $referral) }}" class="grid gap-3 lg:w-[260px]">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="km-input">
                                            @foreach ($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($referral->status->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="notes" rows="2" class="km-input" placeholder="Note interne">{{ $referral->notes }}</textarea>
                                        <textarea name="outcome" rows="3" class="km-input" placeholder="Esito o aggiornamento">{{ $referral->outcome }}</textarea>
                                        <button type="submit" class="km-button-secondary">Aggiorna stato</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-stone-600">Nessuna referenza ricevuta.</p>
                        @endforelse
                    </div>
                    <div class="mt-5">
                        {{ $receivedReferrals->links() }}
                    </div>
                </div>

                <div class="km-panel p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Inviate</p>
                            <h2 class="mt-2 text-2xl font-semibold text-stone-950">Referenze inviate</h2>
                        </div>
                    </div>
                    <div class="mt-5 space-y-4">
                        @forelse ($sentReferrals as $referral)
                            <article class="rounded-[1.6rem] bg-stone-100 p-5">
                                <p class="text-sm font-semibold text-stone-950">{{ $referral->title }}</p>
                                <p class="mt-1 text-sm text-stone-500">Per {{ $referral->recipient->name }}</p>
                                <p class="mt-3 text-sm leading-7 text-stone-700">{{ $referral->description }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs text-stone-600">
                                    <span class="rounded-full bg-white px-3 py-1">{{ $referral->status->label() }}</span>
                                    <span class="rounded-full bg-white px-3 py-1">
                                        Priorita' {{ match($referral->priority) { 'high' => 'alta', 'low' => 'bassa', default => 'media' } }}
                                    </span>
                                    @if ($referral->estimated_value)
                                        <span class="rounded-full bg-white px-3 py-1">€ {{ number_format((float) $referral->estimated_value, 2, ',', '.') }}</span>
                                    @endif
                                </div>
                                @if ($referral->company_name || $referral->contact_name)
                                    <p class="mt-3 text-sm text-stone-500">
                                        {{ $referral->company_name ?: 'Contatto diretto' }}@if($referral->contact_name) · {{ $referral->contact_name }}@endif
                                    </p>
                                @endif
                                @if ($referral->notes)
                                    <p class="mt-2 text-sm text-stone-500">Note: {{ $referral->notes }}</p>
                                @endif
                                @if ($referral->outcome)
                                    <p class="mt-2 text-sm text-emerald-700">Esito: {{ $referral->outcome }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-stone-600">Non hai ancora inviato referenze.</p>
                        @endforelse
                    </div>
                    <div class="mt-5">
                        {{ $sentReferrals->links() }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
