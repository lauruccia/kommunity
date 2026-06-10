{{--
    Partial: interazioni del viewer con il membro visualizzato.
    Visibile solo se autenticato e NON proprietario del profilo.
    Variabili richieste: $user, $onepage, $sharedConversation, $sharedOneToOnes, $sharedReferrals, $sharedEvents
--}}

@php
    $hasMessages   = !empty($sharedConversation);
    $hasOneToOnes  = !empty($sharedOneToOnes) && $sharedOneToOnes->isNotEmpty();
    $hasReferrals  = !empty($sharedReferrals) && $sharedReferrals->isNotEmpty();
    $hasEvents     = !empty($sharedEvents) && $sharedEvents->isNotEmpty();
    $hasAny        = $hasMessages || $hasOneToOnes || $hasReferrals || $hasEvents;
@endphp

@if ($hasAny)
<div class="km-panel p-6">
    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">
        {{ __('profile.interactions_title') }}
    </h3>

    <div class="mt-4 space-y-4">

        {{-- ── MESSAGGI ──────────────────────────────────────────────── --}}
        @if ($hasMessages)
        @php $lastMsg = $sharedConversation->lastMessage; @endphp
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-stone-500">
                    <svg class="h-3.5 w-3.5 text-sky-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v7a2 2 0 01-2 2H6l-4 4V5z"/>
                    </svg>
                    {{ __('profile.interactions_messages') }}
                </span>
                <a href="{{ route('conversations.index', ['conversation' => $sharedConversation->id]) }}"
                   class="text-xs font-semibold text-[color:var(--km-accent-strong)] hover:underline">
                    {{ __('profile.interactions_open') }} →
                </a>
            </div>
            @if ($lastMsg)
            <div class="rounded-xl border border-stone-100 bg-stone-50 px-3 py-2.5">
                <p class="text-xs font-medium text-stone-500">
                    {{ $lastMsg->user?->name ?? '—' }} ·
                    {{ $lastMsg->created_at->diffForHumans() }}
                </p>
                <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-stone-700">
                    {{ $lastMsg->body }}
                </p>
            </div>
            @endif
        </div>
        @endif

        {{-- ── ONE-TO-ONE ─────────────────────────────────────────────── --}}
        @if ($hasOneToOnes)
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-stone-500">
                    <svg class="h-3.5 w-3.5 text-[color:var(--km-accent)]" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zm5 2a2 2 0 11-4 0 2 2 0 014 0zm-4 7a4 4 0 00-8 0v.5a.5.5 0 00.5.5h7a.5.5 0 00.5-.5V15zm6 0a4 4 0 00-3-3.87V11a2 2 0 10-2 0v.13A4.002 4.002 0 0120 15v.5a.5.5 0 01-.5.5H16a5.01 5.01 0 00.98-3z"/>
                    </svg>
                    {{ __('profile.interactions_one_to_one') }}
                </span>
                <a href="{{ route('one-to-ones.index', ['member' => $user->id]) }}"
                   class="text-xs font-semibold text-[color:var(--km-accent-strong)] hover:underline">
                    {{ __('profile.interactions_view_all') }} →
                </a>
            </div>
            @php $oto = $sharedOneToOnes->first(); @endphp
            <div class="rounded-xl border border-stone-100 bg-stone-50 px-3 py-2.5">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-stone-700 truncate">
                        {{ $oto->title ?? __('profile.interactions_one_to_one_session') }}
                    </p>
                    @php
                        $otoStatus = $oto->status ?? null;
                        $otoStatusClass = match($otoStatus) {
                            'accepted', 'confirmed', 'completed' => 'bg-emerald-50 text-emerald-700',
                            'pending'                            => 'bg-amber-50 text-amber-700',
                            'declined', 'cancelled'              => 'bg-rose-50 text-rose-700',
                            default                              => 'bg-stone-100 text-stone-500',
                        };
                        $otoStatusLabel = $otoStatus
                            ? __('profile.oto_status_' . $otoStatus, [], null) ?: ucfirst($otoStatus)
                            : null;
                    @endphp
                    @if ($otoStatusLabel)
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $otoStatusClass }}">
                        {{ $otoStatusLabel }}
                    </span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-stone-500">
                    {{ $oto->created_at->diffForHumans() }}
                    @if ($sharedOneToOnes->count() > 1)
                        · {{ $sharedOneToOnes->count() }} {{ __('profile.interactions_total') }}
                    @endif
                </p>
            </div>
        </div>
        @endif

        {{-- ── REFERRAL ────────────────────────────────────────────────── --}}
        @if ($hasReferrals)
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-stone-500">
                    <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    {{ __('profile.interactions_referrals') }}
                </span>
                <a href="{{ route('one-to-ones.index') }}"
                   class="text-xs font-semibold text-[color:var(--km-accent-strong)] hover:underline">
                    {{ __('profile.interactions_view_all') }} →
                </a>
            </div>
            @php $ref = $sharedReferrals->first(); @endphp
            <div class="rounded-xl border border-stone-100 bg-stone-50 px-3 py-2.5">
                <p class="line-clamp-1 text-xs font-semibold text-stone-700">
                    {{ $ref->title ?? '—' }}
                </p>
                <p class="mt-0.5 text-xs text-stone-500">
                    @if ($ref->sender_id === auth()->id())
                        {{ __('profile.interactions_referral_sent') }}
                    @else
                        {{ __('profile.interactions_referral_received') }}
                    @endif
                    @if ($sharedReferrals->count() > 1)
                        · {{ $sharedReferrals->count() }} {{ __('profile.interactions_total') }}
                    @endif
                </p>
            </div>
        </div>
        @endif

        {{-- ── CO-PARTECIPAZIONE EVENTI ────────────────────────────────── --}}
        @if ($hasEvents)
        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-stone-500">
                    <svg class="h-3.5 w-3.5 text-violet-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('profile.interactions_events') }}
                </span>
                <a href="{{ route('events.index') }}"
                   class="text-xs font-semibold text-[color:var(--km-accent-strong)] hover:underline">
                    {{ __('profile.interactions_view_all') }} →
                </a>
            </div>
            <div class="space-y-1.5">
                @foreach ($sharedEvents->take(3) as $event)
                <div class="flex items-center gap-2 rounded-xl border border-stone-100 bg-stone-50 px-3 py-2">
                    <svg class="h-3.5 w-3.5 shrink-0 text-violet-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-stone-700">{{ $event->title }}</p>
                        @if ($event->starts_at)
                        <p class="text-xs text-stone-400">{{ $event->starts_at->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endif
