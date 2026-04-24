<x-app-layout>
    @php
        $memberSearchItems = $members->map(fn ($member) => [
            'id' => $member->id,
            'name' => $member->name ?? 'Utente',
            'email' => $member->email ?? '',
            'company' => $member->memberProfile?->company_name,
            'city' => $member->memberProfile?->city?->name,
            'availability_slots' => $member->availabilitySlots->map(fn ($slot) => [
                'id' => $slot->id,
                'weekday' => $slot->weekday,
                'starts_at' => substr($slot->starts_at, 0, 5),
                'ends_at' => substr($slot->ends_at, 0, 5),
                'meeting_mode' => $slot->meeting_mode,
                'location' => $slot->location,
            ])->values(),
        ])->values();
    @endphp

    <div class="pb-12">
        <div class="w-full px-4 pt-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">

                {{-- SIDEBAR --}}
                <aside class="space-y-6 lg:sticky lg:top-6 lg:w-[340px] lg:min-w-[340px]">

                    {{-- HEADER --}}
                    <section class="km-panel p-6">
                        <h1 class="text-3xl font-semibold">Richieste one-to-one</h1>
                        <button id="open-one-to-one-create-modal" class="km-button-primary mt-5 w-full">
                            Nuova richiesta
                        </button>
                    </section>

                    {{-- DISPONIBILITA --}}
                    <section class="km-panel p-6">
                        <h2 class="text-xl font-semibold">Le mie disponibilità</h2>

                        <form method="POST" action="{{ route('one-to-ones.availability.store') }}" class="mt-5 space-y-4">
                            @csrf

                            <select name="weekday" class="km-input" required>
                                <option value="">Giorno</option>
                                @foreach ($weekdayOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            <div class="grid gap-4 md:grid-cols-2">
                                <input type="time" name="starts_at" class="km-input" required>
                                <input type="time" name="ends_at" class="km-input" required>
                            </div>

                            <button type="submit" class="km-button-secondary w-full">
                                Aggiungi disponibilità
                            </button>
                        </form>

                        <div class="mt-5 space-y-3">
                            @forelse ($availabilitySlots as $slot)
                                <div class="p-4 bg-gray-50 rounded-xl">
                                    {{ $weekdayOptions[$slot->weekday] ?? '-' }}
                                </div>
                            @empty
                                <p>Nessuna disponibilità</p>
                            @endforelse
                        </div>
                    </section>

                </aside>

                {{-- MAIN --}}
                <section class="flex-1 space-y-5">

                    {{-- TABLE --}}
                    <div class="km-panel overflow-hidden p-0">
                        <table class="w-full text-sm">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Membro</th>
                                    <th>Data</th>
                                    <th>Stato</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($requests as $requestItem)
                                    <tr>

                                        {{-- TIPO --}}
                                        <td>
                                            {{ $requestItem->requester_id === auth()->id() ? 'Inviata' : 'Ricevuta' }}
                                        </td>

                                        {{-- MEMBRO (FIX NULL) --}}
                                        <td>
                                            {{ $requestItem->requester_id === auth()->id()
                                                ? ($requestItem->recipient?->name ?? 'Utente non disponibile')
                                                : ($requestItem->requester?->name ?? 'Utente non disponibile') }}
                                        </td>

                                        {{-- DATA --}}
                                        <td>
                                            {{ optional($requestItem->requested_at)->format('d/m/Y H:i') ?? '-' }}
                                        </td>

                                        {{-- STATO --}}
                                        <td>
                                            {{ $requestItem->status?->label() ?? '-' }}
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">Nessuna richiesta</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </section>
            </div>
        </div>

        {{-- DETTAGLIO --}}
        @if ($selectedRequest)
            @php
                $isRequester = $selectedRequest->requester_id === auth()->id();
                $counterpart = $isRequester ? $selectedRequest->recipient : $selectedRequest->requester;
            @endphp

            <div class="fixed inset-0 bg-black/40"></div>

            <div class="fixed inset-0 flex items-center justify-center">
                <div class="bg-white p-6 rounded-xl w-[600px]">

                    <h3 class="text-xl font-semibold">
                        {{ $isRequester
                            ? 'Richiesta a '.($counterpart?->name ?? 'Utente')
                            : 'Richiesta da '.($counterpart?->name ?? 'Utente') }}
                    </h3>

                    <p class="mt-3">
                        {{ $selectedRequest->goal ?? '-' }}
                    </p>

                </div>
            </div>
        @endif

    </div>
</x-app-layout>