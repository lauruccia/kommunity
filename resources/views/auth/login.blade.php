<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Accesso membro</p>
        <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">Entra nella tua area Kommunity</h1>
        <p class="mt-3 text-sm leading-7 text-stone-600">Accedi per gestire profilo business, directory, incontri, forum e messaggi.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[rgba(70,93,112,0.24)] text-[color:var(--km-accent)] shadow-sm focus:ring-[rgba(85,121,79,0.18)]" name="remember">
                <span class="ms-2 text-sm text-stone-600">Ricordami</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="km-link rounded-md text-sm underline focus:outline-none focus:ring-2 focus:ring-[rgba(85,121,79,0.18)] focus:ring-offset-2" href="{{ route('password.request') }}">
                    Password dimenticata?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Accedi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
