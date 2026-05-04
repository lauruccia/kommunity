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
            <div class="relative mt-1">
                <x-text-input id="password" class="km-portal-input block w-full pr-10" type="password" name="password" required autocomplete="current-password" />
                <button type="button" onclick="kmTogglePwd('password',this)" tabindex="-1"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-white/40 hover:text-white/80 transition-colors"
                    aria-label="Mostra/nascondi password">
                    <svg class="h-5 w-5 eye-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg class="h-5 w-5 eye-hide hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <script>
        function kmTogglePwd(id, btn) {
            const inp = document.getElementById(id);
            const nowHidden = inp.type === 'password';
            inp.type = nowHidden ? 'text' : 'password';
            btn.querySelector('.eye-show').classList.toggle('hidden', nowHidden);
            btn.querySelector('.eye-hide').classList.toggle('hidden', !nowHidden);
        }
        </script>

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
