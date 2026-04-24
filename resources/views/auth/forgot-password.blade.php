<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Recupero accesso</p>
        <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Reimposta la password</h1>
        <p class="mt-3 text-sm leading-7 text-stone-600">Inserisci il tuo indirizzo email. Ti invieremo un link per scegliere una nuova password.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button>
                Invia link di reset
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
