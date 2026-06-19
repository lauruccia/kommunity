{{-- KM-MEMBER-REVIEWS --}}
<x-app-layout :hide-navigation="! auth()->check()">
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
        <div class="mt-6">
            <h1 class="font-serif text-2xl font-semibold text-stone-950 sm:text-3xl">
                Recensioni di {{ $user->name }}
            </h1>

            @if ($avgRating)
            <div class="mt-3 flex flex-wrap items-center gap-4">
                {{-- Numero grande --}}
                <div class="flex items-center gap-3">
                    <span class="text-5xl font-bold text-stone-900 leading-none">{{ number_format($avgRating, 1) }}</span>
                    <div>
                        <span class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= floor($avgRating) ? 'text-yellow-400' : 'text-stone-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </span>
                        <p class="mt-1 text-sm text-stone-500">
                            {{ $totalCount }} {{ $totalCount === 1 ? 'recensione' : 'recensioni' }}
                        </p>
                    </div>
                </div>
            </div>
            @else
                <p class="mt-1 text-sm text-stone-500">
                    {{ $totalCount }} {{ $totalCount === 1 ? 'recensione' : 'recensioni' }}
                </p>
            @endif
        </div>

        {{-- Sorting bar --}}
        <div class="mt-5 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-stone-400">Ordina per:</span>
            @foreach ([
                'recenti'  => 'Più recenti',
                'migliori' => 'Migliori ★★★★★',
                'peggiori' => 'Peggiori ★',
            ] as $key => $label)
            <a href="{{ route('members.reviews', ['slug' => $onepage->slug, 'sort' => $key]) }}"
               class="rounded-full px-4 py-1.5 text-sm font-semibold transition
                      {{ $sort === $key
                          ? 'bg-stone-900 text-white'
                          : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:bg-stone-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Lista recensioni --}}
        @if ($reviews->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-stone-200 bg-stone-50 px-6 py-12 text-center">
                <p class="text-stone-400">Nessuna recensione ancora.</p>
            </div>
        @else
        <div class="mt-6 space-y-4">
            @foreach ($reviews as $review)
            <div class="km-panel p-5 sm:p-6">
                <div class="flex items-start gap-4">
                    {{-- Avatar --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-200 text-sm font-semibold text-stone-600">
                        {{ strtoupper(substr($review->author?->name ?? '?', 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        {{-- Nome + badge raccomanda --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-stone-800">
                                {{ $review->author?->name ?? 'Utente Kommunity' }}
                            </span>
                            @if ($review->is_recommended)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                    ✓ Raccomanda
                                </span>
                            @endif
                        </div>

                        {{-- Stelle --}}
                        @if ($review->rating)
                        <span class="mt-1 flex gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-stone-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="ml-1 text-xs font-semibold text-stone-600">{{ $review->rating }}/5</span>
                        </span>
                        @endif

                        {{-- Tag competenze --}}
                        @if (!empty($review->tags))
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($review->tags as $tag)
                                <span class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-600">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Testo --}}
                        @if ($review->content)
                            <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $review->content }}</p>
                        @endif

                        {{-- Data --}}
                        <p class="mt-3 text-xs text-stone-400">{{ $review->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginazione --}}
        @if ($reviews->hasPages())
        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
        @endif
        @endif

    </div>
</x-app-layout>
