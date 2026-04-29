<x-guest-layout>
    <div class="mb-6">
        <p class="km-portal-eyebrow">Accesso membro</p>
        <h1 class="km-portal-title mt-3 text-3xl sm:text-4xl">Entra nella tua area Kommunity</h1>
        <p class="km-portal-muted mt-3 text-sm leading-7">Accedi per gestire profilo business, incontri, eventi, forum e messaggi.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="km-portal-input mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="km-portal-input mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-white/20 bg-white/10 text-[color:var(--km-green)] shadow-sm focus:ring-[rgba(139,197,63,0.20)]" name="remember">
                <span class="ms-2 text-sm text-white/75">Ricordami</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-[color:#9ad84a] underline focus:outline-none focus:ring-2 focus:ring-[rgba(139,197,63,0.20)] focus:ring-offset-2" href="{{ route('password.request') }}">
                    Password dimenticata?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Accedi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
