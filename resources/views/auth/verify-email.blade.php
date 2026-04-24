<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Verifica email</p>
        <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Attiva il tuo account</h1>
        <p class="mt-3 text-sm leading-7 text-stone-600">Prima di iniziare, verifica il tuo indirizzo email dal link che ti abbiamo appena inviato. Se non l'hai ricevuto, puoi richiederne subito uno nuovo.</p>
    </div>

    <div class="mb-6 rounded-3xl border border-[rgba(70,93,112,0.14)] bg-[linear-gradient(135deg,rgba(255,255,255,0.86)_0%,rgba(228,238,227,0.88)_100%)] px-5 py-4 text-sm leading-6 text-[color:var(--km-deep-strong)]">
        <p class="font-semibold">Account in attesa di attivazione</p>
        <p class="mt-1">Se non ricevi email automatiche, il tuo account può essere attivato direttamente da un amministratore. Contatta l'amministrazione e chiedi l'abilitazione manuale.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-[color:var(--km-accent-strong)]">
            Ti abbiamo inviato un nuovo link di verifica all'indirizzo indicato in registrazione.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Invia di nuovo il link
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="km-link rounded-md text-sm underline focus:outline-none focus:ring-2 focus:ring-[rgba(85,121,79,0.18)] focus:ring-offset-2">
                Esci
            </button>
        </form>
    </div>
</x-guest-layout>
