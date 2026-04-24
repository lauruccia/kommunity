<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Conferma sicurezza</p>
        <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Conferma la tua password</h1>
        <p class="mt-3 text-sm leading-7 text-stone-600">Per continuare in questa area riservata devi confermare la password del tuo account.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex justify-end">
            <x-primary-button>
                Conferma
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
