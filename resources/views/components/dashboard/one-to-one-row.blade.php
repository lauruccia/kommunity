@props([
    'request',         // OneToOneRequest instance
    'currentUserId',   // auth user id
])

@php
    $isReceived = $request->recipient_id === $currentUserId;
    $other      = $isReceived ? $request->requester : $request->recipient;
    $isPending  = in_array($request->status->value, ['pending', 'rescheduled'], true);
    $statusV    = $request->status->value;
    $tones = [
        'pending'    => ['#FCD34D', 'rgba(245,158,11,.14)', 'rgba(245,158,11,.30)'],
        'accepted'   => ['#9AD84A', 'rgba(139,197,63,.14)', 'rgba(139,197,63,.30)'],
        'declined'   => ['#FDA4AF', 'rgba(244,63,94,.12)',  'rgba(244,63,94,.28)'],
        'cancelled'  => ['#94A3B8', 'rgba(148,163,184,.10)','rgba(148,163,184,.20)'],
        'completed'  => ['#5EEAD4', 'rgba(45,212,191,.14)', 'rgba(45,212,191,.28)'],
        'rescheduled'=> ['#FCD34D', 'rgba(245,158,11,.14)', 'rgba(245,158,11,.30)'],
    ];
    [$col, $bg, $bor] = $tones[$statusV] ?? $tones['pending'];
    $avatar = $other?->memberProfile?->avatarUrl();
    $when   = $request->requested_at?->format('d/m H:i');
@endphp

<div class="rounded-xl border border-white/[.08] bg-white/[.04] p-3.5 transition hover:border-[rgba(139,197,63,.25)]">
    <div class="flex items-start gap-3">
        @if ($avatar)
            <img src="{{ $avatar }}" alt="{{ $other?->name }}" class="h-10 w-10 shrink-0 rounded-full border border-white/15 object-cover">
        @else
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#1e3128] to-[#0b1219] text-sm font-bold text-white">
                {{ \Illuminate\Support\Str::of($other?->name ?? '?')->substr(0, 1)->upper() }}
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-2">
                <p class="truncate text-sm font-bold text-white">{{ $other?->name ?? 'Utente eliminato' }}</p>
                @if ($when)
                    <span class="shrink-0 text-[11px] tabular-nums text-white/50">{{ $when }}</span>
                @endif
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                      style="background: {{ $bg }}; color: {{ $col }}; border: 1px solid {{ $bor }};">
                    {{ $request->status->label() }}
                </span>
                <span class="text-[11px] text-white/55">
                    {{ $request->meeting_mode === 'online' ? 'Online' : 'In presenza' }}
                    @if ($isReceived) · ricevuta @else · inviata @endif
                </span>
            </div>

            @if (! empty($request->goal))
                <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-white/70">{{ $request->goal }}</p>
            @endif

            @if ($request->canRespondTo($currentUserId))
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    <form method="POST" action="{{ route('one-to-ones.status', $request) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="accepted">
                        <button type="submit"
                                class="rounded-full bg-gradient-to-br from-[#8BC53F] to-[#5f9d42] px-3 py-1 text-[11px] font-bold text-[#061018] shadow-[0_4px_14px_rgba(139,197,63,.25)] transition hover:brightness-110">
                            Accetta
                        </button>
                    </form>
                    <form method="POST" action="{{ route('one-to-ones.status', $request) }}" class="inline"
                          onsubmit="return confirm('Rifiutare la richiesta?');">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="declined">
                        <button type="submit"
                                class="rounded-full border border-[rgba(244,63,94,.30)] px-3 py-1 text-[11px] font-bold text-[#FDA4AF] transition hover:bg-[rgba(244,63,94,.10)]">
                            Rifiuta
                        </button>
                    </form>
                    <a href="{{ route('one-to-ones.index', ['request' => $request->id]) }}"
                       class="rounded-full border border-white/10 px-3 py-1 text-[11px] font-semibold text-white/70 transition hover:border-white/20 hover:text-white">
                        Dettagli
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
