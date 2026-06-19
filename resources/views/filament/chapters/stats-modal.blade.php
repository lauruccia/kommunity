<div class="space-y-6 py-2">

    {{-- ── Panoramica ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalMembers }}</div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pianeta principale</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeInPivot }}</div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Iscritti attivi</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invitesPending }}</div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inviti in attesa</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $joinWaitlist }}</div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">In lista d'attesa</div>
        </div>
    </div>

    {{-- ── Limite professioni ───────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 rounded-lg border px-4 py-3 text-sm
        {{ $limitActive ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950/30' : 'border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5' }}">
        @if($limitActive)
            <span class="text-amber-600 dark:text-amber-400">⚠</span>
            <span class="text-amber-800 dark:text-amber-300">Limite attivo: max <strong>{{ $limit }}</strong> professionisti per categoria</span>
        @else
            <span class="text-gray-400">ℹ</span>
            <span class="text-gray-500 dark:text-gray-400">Limite professioni <strong>disattivato</strong> (max configurato: {{ $limit }})</span>
        @endif
    </div>

    {{-- ── Distribuzione professioni ────────────────────────────────────────── --}}
    <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
            Distribuzione professioni
        </h3>

        @if($professions->isEmpty())
            <p class="text-sm text-gray-400 italic">Nessun utente con professione assegnata.</p>
        @else
            <div class="space-y-2">
                @foreach($professions as $row)
                    @php
                        $pct = $limit > 0 ? min(100, round($row->total / $limit * 100)) : 100;
                        $color = $limitActive && $row->total >= $limit ? 'bg-red-500' : ($row->total >= $limit * 0.8 ? 'bg-amber-400' : 'bg-emerald-500');
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-44 shrink-0 truncate text-sm text-gray-700 dark:text-gray-300" title="{{ $row->name }}">
                            {{ $row->name }}
                        </div>
                        <div class="flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10" style="height:8px;">
                            <div class="{{ $color }} rounded-full" style="width:{{ $pct }}%; height:8px; transition:width 0.3s;"></div>
                        </div>
                        <div class="w-16 shrink-0 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $row->total }}@if($limit > 0) / {{ $limit }}@endif
                        </div>
                        @if($limitActive && $row->total >= $limit)
                            <span class="shrink-0 rounded-full bg-red-100 dark:bg-red-900/40 px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-300">pieno</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Altre info ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 gap-3 text-sm border-t border-gray-200 dark:border-white/10 pt-4">
        <div>
            <span class="text-gray-500 dark:text-gray-400">Inviti accettati:</span>
            <span class="ml-1 font-medium text-gray-800 dark:text-gray-200">{{ $invitesAccepted }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400">Richieste pending:</span>
            <span class="ml-1 font-medium text-gray-800 dark:text-gray-200">{{ $joinPending }}</span>
        </div>
        <div>
            <span class="text-gray-500 dark:text-gray-400">Totale iscrizioni pivot:</span>
            <span class="ml-1 font-medium text-gray-800 dark:text-gray-200">{{ $totalInPivot }}</span>
        </div>
    </div>

</div>
