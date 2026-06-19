<x-guest-layout>
    <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

        {{-- COLONNA SINISTRA --}}
        <div class="flex flex-col justify-center">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-400">Kommunity</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold leading-snug text-white sm:text-3xl">
                Benvenuto nel Pianeta<br>
                <span style="color:#9ad84a;">{{ $invitation->chapter?->name }}</span>
            </h1>
            @if($invitation->invitedBy)
                <p class="mt-4 text-sm text-white/70">
                    <strong class="text-white">{{ $invitation->invitedBy->name }}</strong> ti ha invitato a far parte di questo Pianeta.
                </p>
            @endif
            @if($invitation->message)
                <blockquote class="mt-4 border-l-2 pl-4 text-sm text-white/60 italic" style="border-color:#537d4d;">
                    "{{ $invitation->message }}"
                </blockquote>
            @endif
        </div>

        {{-- COLONNA DESTRA --}}
        <div>
            <div class="mb-6">
                <p class="text-xs uppercase tracking-[0.24em] text-white/50">Invito Pianeta</p>
                <h2 class="mt-2 font-serif text-xl font-semibold text-white">Conferma la tua adesione</h2>
                <p class="mt-1.5 text-sm text-white/55">Sei già registrato su Kommunity. Clicca il bottone per entrare nel Pianeta.</p>
            </div>

            @if(session('error'))
                <div class="mb-4 rounded-lg p-3 text-sm" style="background:rgba(239,68,68,0.15); color:#fca5a5;">
                    {{ session('error') }}
                </div>
            @endif

            @if($alreadyMember)
                <div class="rounded-lg p-4 text-sm" style="background:rgba(154,216,74,0.1); border:1px solid rgba(154,216,74,0.3); color:#9ad84a;">
                    Sei già utente del Pianeta <strong>{{ $invitation->chapter?->name }}</strong>.
                </div>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}"
                       class="inline-block rounded-lg px-6 py-3 text-sm font-semibold text-white"
                       style="background:#537d4d;">
                        Vai alla dashboard
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('chapter.invite.accept', $invitation->token) }}">
                    @csrf

                    <div class="rounded-lg p-4 mb-6 text-sm" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);">
                        <p class="text-white/70 mb-1">Pianeta</p>
                        <p class="text-white font-semibold">{{ $invitation->chapter?->name }}</p>
                        @if($invitation->expires_at)
                            <p class="text-white/40 text-xs mt-2">Invito valido fino al {{ $invitation->expires_at->format('d/m/Y') }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit"
                                class="rounded-lg px-6 py-3 text-sm font-semibold text-white"
                                style="background:#537d4d;">
                            Entra nel Pianeta
                        </button>
                        <a href="{{ route('dashboard') }}"
                           class="text-sm text-white/50 hover:text-white/80 underline underline-offset-2">
                            Non ora
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-guest-layout>
