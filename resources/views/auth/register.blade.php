<x-guest-layout>
    @php
        $headline    = \App\Models\SiteSetting::getCached('registration_headline', 'Entra in Kommunity');
        $subheadline = \App\Models\SiteSetting::getCached('registration_subheadline', 'Kommunity: la piattaforma che fa crescere il tuo business');
        $body        = \App\Models\SiteSetting::getCached('registration_body');
    @endphp

    <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

        {{-- COLONNA SINISTRA: testo promozionale --}}
        <div class="flex flex-col justify-center">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-400">Kommunity</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold leading-snug text-white sm:text-3xl lg:text-4xl">
                {{ $headline }}
            </h1>
            @if($subheadline)
                <p class="mt-3 text-base font-medium text-white/70">{{ $subheadline }}</p>
            @endif

            @if($body)
                <div class="prose prose-invert prose-sm mt-5 max-w-none [&_h2]:font-serif [&_h3]:font-serif [&_ul]:space-y-1">
                    {!! $body !!}
                </div>
            @else
                {{-- Testo placeholder mostrato finché l'admin non configura nulla --}}
                <div class="mt-5 space-y-4 text-sm leading-7 text-white/65">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white text-xs font-bold" style="background:#537d4d;">✓</span>
                        <p><strong class="text-white">Networking professionale</strong><br>Connettiti con professionisti selezionati e costruisci relazioni di valore.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white text-xs font-bold" style="background:#537d4d;">✓</span>
                        <p><strong class="text-white">Visibilità nel tuo settore</strong><br>Presenta la tua professionalità nella directory e ricevi richieste di collaborazione.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white text-xs font-bold" style="background:#537d4d;">✓</span>
                        <p><strong class="text-white">Pianeti di settore</strong><br>Entra in un gruppo ristretto di professionisti del tuo campo e partecipa a incontri One-to-one.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- COLONNA DESTRA: form --}}
        <div>
            <div class="mb-5">
                <p class="text-xs uppercase tracking-[0.24em] text-white/50">Registrazione</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" :value="'Nome e cognome'" />
                    <x-text-input id="name" class="km-portal-input mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="'Email'" />
                    <x-text-input id="email" class="km-portal-input mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="phone" :value="'Telefono'" />
                    <x-text-input id="phone" class="km-portal-input mt-1 block w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="invited_by_name" :value="'Invitato da'" />
                    <x-text-input id="invited_by_name" class="km-portal-input mt-1 block w-full" type="text" name="invited_by_name"
                        :value="old('invited_by_name', $invitedByName ?? '')"
                        required
                        :readonly="filled($invitedByName ?? null)" />
                    @if(filled($invitedByName ?? null))
                        <p class="mt-1.5 text-xs text-white/50">Campo compilato automaticamente dal referral link ricevuto.</p>
                    @else
                        <p class="mt-1.5 text-xs text-white/50">Inserisci nome e cognome della persona che ti ha invitato.</p>
                    @endif
                    <x-input-error :messages="$errors->get('invited_by_name')" class="mt-2" />
                </div>

                @if(filled($referralCode ?? null))
                    <input type="hidden" name="referral_code" value="{{ $referralCode }}">
                @endif

                <div>
                    <x-input-label for="password" :value="'Password'" />
                    <div style="position:relative; margin-top:0.25rem;">
                        <x-text-input id="password" class="km-portal-input block w-full" style="padding-right:2.75rem;" type="password" name="password" required autocomplete="new-password" />
                        <button type="button" onclick="kmTogglePwd('password',this)" tabindex="-1"
                            style="position:absolute; right:0; top:0; bottom:0; display:flex; align-items:center; padding:0 0.75rem; background:transparent; border:none; cursor:pointer; z-index:10; color:rgba(255,255,255,0.55);"
                            onmouseover="this.style.color='rgba(255,255,255,0.9)'" onmouseout="this.style.color='rgba(255,255,255,0.55)'"
                            aria-label="Mostra/nascondi password">
                            <svg class="eye-show" style="width:1.1rem;height:1.1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg class="eye-hide" style="width:1.1rem;height:1.1rem;display:none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="'Conferma password'" />
                    <div style="position:relative; margin-top:0.25rem;">
                        <x-text-input id="password_confirmation" class="km-portal-input block w-full" style="padding-right:2.75rem;" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <button type="button" onclick="kmTogglePwd('password_confirmation',this)" tabindex="-1"
                            style="position:absolute; right:0; top:0; bottom:0; display:flex; align-items:center; padding:0 0.75rem; background:transparent; border:none; cursor:pointer; z-index:10; color:rgba(255,255,255,0.55);"
                            onmouseover="this.style.color='rgba(255,255,255,0.9)'" onmouseout="this.style.color='rgba(255,255,255,0.55)'"
                            aria-label="Mostra/nascondi password">
                            <svg class="eye-show" style="width:1.1rem;height:1.1rem;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <svg class="eye-hide" style="width:1.1rem;height:1.1rem;display:none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
                <script>
                function kmTogglePwd(id, btn) {
                    const inp = document.getElementById(id);
                    const isHidden = inp.type === 'password';
                    inp.type = isHidden ? 'text' : 'password';
                    btn.querySelector('.eye-show').style.display = isHidden ? 'none' : '';
                    btn.querySelector('.eye-hide').style.display = isHidden ? '' : 'none';
                }
                </script>

                <div class="flex items-center justify-between pt-1">
                    <a href="{{ route('login') }}"
                       style="font-size:0.875rem; color:rgba(154,216,74,0.9); text-decoration:underline; text-underline-offset:3px;"
                       onmouseover="this.style.color='rgba(154,216,74,1)'" onmouseout="this.style.color='rgba(154,216,74,0.9)'">
                        Hai già un account?
                    </a>
                    <x-primary-button>
                        Registrati
                    </x-primary-button>
                </div>
            </form>
        </div>

    </div>
</x-guest-layout>
