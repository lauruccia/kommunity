<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Nuova password</p>
        <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Imposta una nuova password</h1>
        <p class="mt-3 text-sm leading-7 text-stone-600">Aggiorna le credenziali del tuo account e torna nella dashboard.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="'Nuova password'" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="'Conferma password'" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button>
                Salva nuova password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
