<nav x-data="{ open: false }" class="relative z-50 overflow-visible pt-5">
    <div class="km-shell overflow-visible">
        <div class="km-panel relative overflow-visible border-stone-300/80 bg-white/85 px-4 backdrop-blur sm:px-6">
            <div class="flex h-16 justify-between overflow-visible">
                <div class="flex">
                    <div class="flex shrink-0 items-center">
                        <a href="{{ route('home') }}" class="km-brand-lockup">
                            <div class="km-brand-mark km-brand-mark-sm">
                                <x-application-logo />
                            </div>
                            <div>
                                <div class="text-lg font-semibold tracking-tight text-stone-950">Kommunity</div>
                                <div class="km-brand-kicker">Community professionale</div>
                            </div>
                        </a>
                    </div>

                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('nav.dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('directory.index')" :active="request()->routeIs('directory.*')">
                            {{ __('nav.directory') }}
                        </x-nav-link>
                        <x-nav-link :href="route('one-to-ones.index')" :active="request()->routeIs('one-to-ones.*')">
                            {{ __('nav.one_to_one') }}
                        </x-nav-link>
                        <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                            {{ __('nav.events') }}
                        </x-nav-link>
                        <x-nav-link :href="route('forum.index')" :active="request()->routeIs('forum.*')">
                            {{ __('nav.forum') }}
                        </x-nav-link>
                        <x-nav-link :href="route('conversations.index')" :active="request()->routeIs('conversations.*')">
                            {{ __('nav.messages') }}
                        </x-nav-link>
                        <x-nav-link :href="route('referrals.index')" :active="request()->routeIs('referrals.*')">
                            {{ __('nav.referrals') }}
                        </x-nav-link>
                        <x-nav-link :href="route('subscriptions.index')" :active="request()->routeIs('subscriptions.*')">
                            {{ __('nav.subscription') }}
                        </x-nav-link>
                        @if (auth()->user()?->can('gestire-utenti'))
                            <x-nav-link href="/admin/users" :active="request()->is('admin/users*')">
                                {{ __('nav.admin') }}
                            </x-nav-link>
                        @endif
                    </div>
                </div>

                <div class="relative z-50 hidden sm:ms-6 sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-600 transition hover:text-stone-900 focus:outline-none">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('nav.profile') }}
                            </x-dropdown-link>

                            @if (auth()->user()?->can('gestire-utenti'))
                                <x-dropdown-link href="/admin/users">
                                    {{ __('nav.admin') }}
                                </x-dropdown-link>
                            @endif

                            <button @click="$dispatch('km:open-password-modal')"
                                class="block w-full px-4 py-2 text-start text-sm leading-5 text-stone-700 transition hover:bg-stone-100 focus:outline-none">
                                {{ __('nav.change_password') }}
                            </button>

                            <button @click="$dispatch('km:open-delete-modal')"
                                class="block w-full px-4 py-2 text-start text-sm leading-5 text-rose-600 transition hover:bg-rose-50 focus:outline-none">
                                {{ __('nav.delete_account') }}
                            </button>

                            {{-- Switcher lingua --}}
                            <div class="my-1 border-t border-stone-100"></div>
                            <div class="px-4 py-2">
                                <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-stone-400">{{ __('nav.language') }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('locale.switch', 'it') }}"
                                       class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ app()->getLocale() === 'it' ? 'bg-[color:var(--km-accent)] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                                        🇮🇹 IT
                                    </a>
                                    <a href="{{ route('locale.switch', 'en') }}"
                                       class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium transition {{ app()->getLocale() === 'en' ? 'bg-[color:var(--km-accent)] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                                        🇬🇧 EN
                                    </a>
                                </div>
                            </div>

                            <div class="my-1 border-t border-stone-100"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('nav.logout') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-2 text-stone-500 transition hover:bg-stone-100 hover:text-stone-700 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-stone-200 pb-4 pt-2 sm:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('nav.dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('directory.index')" :active="request()->routeIs('directory.*')">
                        {{ __('nav.directory') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('one-to-ones.index')" :active="request()->routeIs('one-to-ones.*')">
                        {{ __('nav.one_to_one') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                        {{ __('nav.events') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('forum.index')" :active="request()->routeIs('forum.*')">
                        {{ __('nav.forum') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('conversations.index')" :active="request()->routeIs('conversations.*')">
                        {{ __('nav.messages') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('referrals.index')" :active="request()->routeIs('referrals.*')">
                        {{ __('nav.referrals') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('subscriptions.index')" :active="request()->routeIs('subscriptions.*')">
                        {{ __('nav.subscription') }}
                    </x-responsive-nav-link>
                    @if (auth()->user()?->can('gestire-utenti'))
                        <x-responsive-nav-link href="/admin/users" :active="request()->is('admin/users*')">
                            {{ __('nav.admin') }}
                        </x-responsive-nav-link>
                    @endif
                </div>

                <div class="border-t border-stone-200 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-stone-900">{{ Auth::user()->name }}</div>
                        <div class="text-sm font-medium text-stone-500">{{ Auth::user()->email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">
                            {{ __('nav.profile') }}
                        </x-responsive-nav-link>

                        @if (auth()->user()?->can('gestire-utenti'))
                            <x-responsive-nav-link href="/admin/users">
                                {{ __('nav.admin') }}
                            </x-responsive-nav-link>
                        @endif

                        <button @click="$dispatch('km:open-password-modal'); open = false"
                            class="block w-full px-4 py-2 text-start text-base font-medium text-stone-600 transition hover:bg-stone-50 hover:text-stone-800">
                            {{ __('nav.change_password') }}
                        </button>

                        <button @click="$dispatch('km:open-delete-modal'); open = false"
                            class="block w-full px-4 py-2 text-start text-base font-medium text-rose-600 transition hover:bg-rose-50">
                            {{ __('nav.delete_account') }}
                        </button>

                        {{-- Switcher lingua mobile --}}
                        <div class="px-4 py-3 border-t border-stone-100">
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-stone-400">{{ __('nav.language') }}</p>
                            <div class="flex gap-2">
                                <a href="{{ route('locale.switch', 'it') }}"
                                   class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ app()->getLocale() === 'it' ? 'bg-[color:var(--km-accent)] text-white' : 'bg-stone-100 text-stone-600' }}">
                                    🇮🇹 IT
                                </a>
                                <a href="{{ route('locale.switch', 'en') }}"
                                   class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ app()->getLocale() === 'en' ? 'bg-[color:var(--km-accent)] text-white' : 'bg-stone-100 text-stone-600' }}">
                                    🇬🇧 EN
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('nav.logout') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

@push('modals')
{{-- MODAL: Cambia password — iniettato direttamente nel body via @stack --}}
<div x-data="{ open: false }"
     @km:open-password-modal.window="open = true"
     x-show="open"
     x-cloak
     style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);" @click="open = false"></div>
    <div class="relative w-full max-w-md rounded-[1.6rem] border border-stone-200 bg-white p-7 shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <button @click="open = false" class="absolute right-5 top-5 text-stone-400 hover:text-stone-700">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>

        <div class="mb-5 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-100">
                <svg class="h-5 w-5 text-stone-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-stone-950">Cambia password</h2>
                <p class="text-xs text-stone-400">Usa una password robusta e unica</p>
            </div>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('put')

            <div>
                <label for="nav_current_password" class="block text-sm font-medium text-stone-700">Password attuale</label>
                <input id="nav_current_password" name="current_password" type="password" autocomplete="current-password"
                       class="km-input mt-1 block w-full" required>
                @if ($errors->updatePassword->has('current_password'))
                    <p class="mt-1 text-xs text-rose-600">{{ $errors->updatePassword->first('current_password') }}</p>
                @endif
            </div>
            <div>
                <label for="nav_password" class="block text-sm font-medium text-stone-700">Nuova password</label>
                <input id="nav_password" name="password" type="password" autocomplete="new-password"
                       class="km-input mt-1 block w-full" required>
                @if ($errors->updatePassword->has('password'))
                    <p class="mt-1 text-xs text-rose-600">{{ $errors->updatePassword->first('password') }}</p>
                @endif
            </div>
            <div>
                <label for="nav_password_confirmation" class="block text-sm font-medium text-stone-700">Conferma nuova password</label>
                <input id="nav_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                       class="km-input mt-1 block w-full" required>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="open = false"
                        class="rounded-full border border-stone-200 bg-stone-50 px-5 py-2 text-sm font-medium text-stone-700 hover:bg-stone-100">
                    Annulla
                </button>
                <button type="submit"
                        class="rounded-full px-5 py-2 text-sm font-semibold text-white"
                        style="background:linear-gradient(135deg,#537d4d,#3f6239);">
                    Salva password
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: Elimina account — iniettato direttamente nel body via @stack --}}
<div x-data="{ open: false }"
     @km:open-delete-modal.window="open = true"
     x-show="open"
     x-cloak
     style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);" @click="open = false"></div>
    <div class="relative w-full max-w-md rounded-[1.6rem] border border-rose-200 bg-white p-7 shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <button @click="open = false" class="absolute right-5 top-5 text-stone-400 hover:text-stone-700">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
        </button>

        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100">
                <svg class="h-5 w-5 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-stone-950">Elimina account</h2>
                <p class="text-xs text-rose-400">Azione permanente e irreversibile</p>
            </div>
        </div>

        <div class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700 mb-5">
            Tutti i tuoi dati, contenuti e accessi verranno rimossi definitivamente. Non sarà possibile recuperarli.
        </div>

        <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('delete')

            <div>
                <label for="nav_delete_password" class="block text-sm font-medium text-stone-700">Conferma con la tua password</label>
                <input id="nav_delete_password" name="password" type="password" autocomplete="current-password"
                       class="km-input mt-1 block w-full" placeholder="Inserisci la tua password" required>
                @if ($errors->userDeletion->has('password'))
                    <p class="mt-1 text-xs text-rose-600">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="open = false"
                        class="rounded-full border border-stone-200 bg-stone-50 px-5 py-2 text-sm font-medium text-stone-700 hover:bg-stone-100">
                    Annulla
                </button>
                <button type="submit"
                        class="rounded-full bg-rose-600 px-5 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                    Elimina definitivamente
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
