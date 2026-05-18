{{-- KM-MEMBER-REFERRALS --}}
<x-app-layout>
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('members.show', $onepage->slug) }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-stone-800 transition">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd"/>
            </svg>
            Torna al profilo di {{ $user->name }}
        </a>

        {{-- Header --}}
        <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                    Referenze di {{ $user->name }}
                </h1>
                <p class="mt-1 text-sm text-stone-500">
                    {{ $totalCount }} {{ $totalCount === 1 ? 'referenza pubblica' : 'referenze pubbliche' }}
                    @if ($avgPriority)
                        &middot;
                        <span class="inline-flex items-center gap-1">
                            @php $fullStars = (int) floor($avgPriority); @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= $fullStars ? 'text-yellow-400' : 'text-stone-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="font-semibold text-stone-700">{{ number_format($avgPriority, 1) }}</span>
                            <span class="text-stone-400">media</span>
                        </span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Sorting bar (stile Amazon) --}}
        <div class="mt-5 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-stone-400">Ordina per:</span>
            @foreach ([
                'recenti'  => 'Più recenti',
                'migliori' => 'Migliori ★★★★★',
                'peggiori' => 'Peggiori ★',
            ] as $key => $label)
            <a href="{{ route('members.referrals', ['slug' => $onepage->slug, 'sort' => $key]) }}"
               class="rounded-full px-4 py-1.5 text-sm font-semibold transition
                      {{ $sort === $key
                          ? 'bg-stone-900 text-white'
                          : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:bg-stone-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Lista referenze --}}
        @if ($referrals->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-stone-200 bg-stone-50 px-6 py-12 text-center">
                <p class="text-stone-400">Nessuna referenza pubblica ancora.</p>
            </div>
        @else
        <div class="mt-6 space-y-4">
            @foreach ($referrals as $referral)
            @php
                $stars = match(true) {
                    in_array($referral->priority, ['1','2','3','4','5'], true) => (int) $referral->priority,
                    $referral->priority === 'high' => 5,
                    $referral->priority === 'low'  => 1,
                    default => 3,
                };
            @endphp
            <div class="km-panel p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    {{-- Avatar mittente --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-semibold text-amber-700">
                        {{ strtoupper(substr($referral->sender?->name ?? '?', 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        {{-- Mittente + stelle --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-stone-800">
                                {{ $referral->sender?->name ?? 'Membro Kommunity' }}
                            </span>
                            <span class="flex gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $stars ? 'text-yellow-400' : 'text-stone-200' }}"
                                         viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </span>
                            <span class="text-xs font-bold text-stone-700">{{ $stars }}/5</span>
                        </div>

                        {{-- Titolo --}}
                        <p class="mt-2 text-base font-semibold text-stone-900">{{ $referral->title }}</p>

                        {{-- Descrizione --}}
                        @if ($referral->description)
                            <p class="mt-1.5 text-sm leading-relaxed text-stone-600">{{ $referral->description }}</p>
                        @endif

                        {{-- Azienda + data --}}
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-stone-400">
                            @if ($referral->company_name)
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 16.5v-13h-.25a.75.75 0 010-1.5h12.5a.75.75 0 010 1.5H16v13h.25a.75.75 0 010 1.5h-3.5a.75.75 0 01-.75-.75v-2.5a.75.75 0 00-.75-.75h-2.5a.75.75 0 00-.75.75v2.5a.75.75 0 01-.75.75h-3.5a.75.75 0 010-1.5H4zm3-11a.75.75 0 01.75-.75h.5a.75.75 0 010 1.5h-.5A.75.75 0 017 5.5zm.75 2.25a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5zm-.75 4a.75.75 0 01.75-.75h.5a.75.75 0 010 1.5h-.5a.75.75 0 01-.75-.75zm4.75-6a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5zm-.75 4a.75.75 0 01.75-.75h.5a.75.75 0 010 1.5h-.5a.75.75 0 01-.75-.75z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $referral->company_name }}
                                </span>
                            @endif
                            <span>{{ $referral->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginazione --}}
        @if ($referrals->hasPages())
        <div class="mt-8">
            {{ $referrals->links() }}
        </div>
        @endif
        @endif

    </div>
</x-app-layout>
