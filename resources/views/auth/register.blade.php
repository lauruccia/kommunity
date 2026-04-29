<x-guest-layout>
    @php
        $headline    = \App\Models\SiteSetting::getCached('registration_headline', 'Entra in Kommunity');
        $subheadline = \App\Models\SiteSetting::getCached('registration_subheadline', 'La community professionale che fa crescere il tuo business');
        $body        = \App\Models\SiteSetting::getCached('registration_body');
    @endphp

    <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

        {{-- COLONNA SINISTRA: testo promozionale --}}
        <div class="flex flex-col justify-center">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600">Kommunity</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold leading-snug text-stone-950 sm:text-3xl lg:text-4xl">
                {{ $headline }}
            </h1>
            @if($subheadline)
                <p class="mt-3 text-base font-medium text-stone-600">{{ $subheadline }}</p>
            @endif

            @if($body)
                <div class="prose prose-stone prose-sm mt-5 max-w-none [&_h2]:font-serif [&_h2]:text-stone-900 [&_h3]:font-serif [&_h3]:text-stone-900 [&_ul]:space-y-1 [&_li]:text-stone-600">
                    {!! $body !!}
                </div>
            @else
                {{-- Testo placeholder mostrato finché l'admin non configura nulla --}}
                <div class="mt-5 space-y-4 text-sm leading-7 text-stone-600">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white text-xs font-bold" style="background:#537d4d;">✓</span>
                        <p><strong class="text-stone-800">Networking professionale</strong><br>Connettiti con professionisti selezionati e costruisci relazioni di valore.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white text-xs font-bold" style="background:#537d4d;">✓</span>
                        <p><strong class="text-stone-800">Visibilità nel tuo settore</strong><br>Presenta la tua professionalità nella directory e ricevi richieste di collaborazione.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white text-xs font-bold" style="background:#537d4d;">✓</span>
                        <p><strong class="text-stone-800">Pianeti di settore</strong><br>Entra in un gruppo ristretto di professionisti del tuo campo e partecipa a incontri One-to-one.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- COLONNA DESTRA: form --}}
        <div>
            <div class="mb-5">
                <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Registrazione</p>
                <h2 class="mt-2 font-serif text-xl font-semibold text-stone-950">Crea il tuo account</h2>
                <p class="mt-1.5 text-sm text-stone-500">Compila i campi per completare la richiesta di accesso.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" :value="'Nome e cognome'" />
                    <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="'Email'" />
                    <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="invited_by_name" :value="'Invitato da'" />
                    <x-text-input id="invited_by_name" class="mt-1 block w-full" type="text" name="invited_by_name"
                        :value="old('invited_by_name', $invitedByName ?? '')"
                        required
                        :readonly="filled($invitedByName ?? null)" />
                    @if(filled($invitedByName ?? null))
                        <p class="mt-1.5 text-xs text-stone-500">Campo compilato automaticamente dal referral link ricevuto.</p>
                    @else
                        <p class="mt-1.5 text-xs text-stone-500">Inserisci nome e cognome della persona che ti ha invitato.</p>
                    @endif
                    <x-input-error :messages="$errors->get('invited_by_name')" class="mt-2" />
                </div>

                @if(filled($referralCode ?? null))
                    <input type="hidden" name="referral_code" value="{{ $referralCode }}">
                @endif

                <div>
                    <x-input-label for="password" :value="'Password'" />
                    <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="'Conferma password'" />
                    <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <a class="km-link text-sm underline" href="{{ route('login') }}">
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
