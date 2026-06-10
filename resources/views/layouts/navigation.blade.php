@php
    $hasActiveSubscriptionPlans = \Illuminate\Support\Facades\Cache::remember(
        'subscription_plans_exist',
        now()->addMinutes(30),
        fn () => \App\Models\SubscriptionPlan::query()->where('is_active', true)->exists()
    );
    $memberNavigationItems = \App\Support\MemberNavigation::visibleItems($hasActiveSubscriptionPlans);

    $navNotifications    = [];
    $navUnreadCount      = 0;
    if (auth()->check()) {
        $navNotifications = auth()->user()
            ->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->toArray();
        $navUnreadCount = auth()->user()->unreadNotifications()->count();
    }
@endphp

<nav x-data="{ open: false }" class="relative z-50 overflow-visible pt-5">
    <div class="km-shell overflow-visible">
        <div class="km-panel relative overflow-visible border-stone-300/80 bg-white/85 px-3 backdrop-blur sm:px-6">
            <div class="flex min-h-16 items-center justify-between gap-3 overflow-visible py-2">
                <div class="min-w-0 flex flex-1 items-center">
                    <div class="flex min-w-0 items-center">
                        <a href="{{ route('home') }}" class="km-brand-lockup">
                            <div class="km-brand-mark km-brand-mark-sm">
                                <x-application-logo />
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-base font-semibold tracking-tight text-stone-950 sm:text-lg">Kommunity</div>
                                <div class="hidden sm:block km-brand-kicker">La piattaforma professionale</div>
                            </div>
                        </a>
                    </div>

                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        @foreach ($memberNavigationItems as $item)
                            <x-nav-link :href="route($item['route'])" :active="request()->routeIs($item['active'])">
                                {{ __($item['label']) }}
                            </x-nav-link>
                        @endforeach
                    </div>
                </div>

                {{-- ── Campanella notifiche (desktop) ─────────────────── --}}
                @auth
                <div class="relative z-50 hidden sm:flex sm:items-center"
                     x-data="{
                        open: false,
                        unread: {{ $navUnreadCount }},
                        notifications: {{ Js::from($navNotifications) }},
                        markRead(id) {
                            fetch('/notifications/' + id + '/read', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                            });
                            this.notifications = this.notifications.map(n => n.id === id ? {...n, read_at: new Date().toISOString()} : n);
                            this.unread = Math.max(0, this.unread - 1);
                        },
                        markAllRead() {
                            fetch('/notifications/read-all', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                            });
                            this.notifications = this.notifications.map(n => ({...n, read_at: new Date().toISOString()}));
                            this.unread = 0;
                        },
                        goTo(n) {
                            if (!n.read_at) this.markRead(n.id);
                            this.open = false;
                            window.location.href = n.data.url || '#';
                        },
                        timeAgo(dateStr) {
                            const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
                            if (diff < 60) return 'Adesso';
                            if (diff < 3600) return Math.floor(diff/60) + ' min fa';
                            if (diff < 86400) return Math.floor(diff/3600) + ' ore fa';
                            return Math.floor(diff/86400) + ' giorni fa';
                        }
                     }"
                     @click.away="open = false">
                    <button @click="open = !open"
                            class="relative mr-3 flex h-9 w-9 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-500 transition hover:bg-stone-50 hover:text-stone-700 focus:outline-none">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-2.83-2h5.66A3 3 0 0110 18z"/>
                        </svg>
                        <span x-show="unread > 0" x-cloak
                              class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white"
                              x-text="unread > 9 ? '9+' : unread"></span>
                    </button>

                    {{-- Dropdown notifiche --}}
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 top-full mt-2 origin-top-right rounded-2xl border border-stone-200 bg-white shadow-xl flex flex-col"
                         style="z-index:9999; width: min(22rem, calc(100vw - 1rem)); max-height: calc(100vh - 5rem); right: max(0px, env(safe-area-inset-right, 0px));">

                        <div class="flex shrink-0 items-center justify-between border-b border-stone-100 px-4 py-3 rounded-t-2xl">
                            <span class="text-sm font-semibold text-stone-900">Notifiche</span>
                            <button x-show="unread > 0" x-cloak
                                    @click.stop="markAllRead()"
                                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                                Segna tutte come lette
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto divide-y divide-stone-50" style="max-height:20rem;">
                            <template x-if="notifications.length === 0">
                                <div class="px-4 py-8 text-center text-sm text-stone-400">
                                    Nessuna notifica
                                </div>
                            </template>
                            <template x-for="n in notifications" :key="n.id">
                                <button @click="goTo(n)"
                                        class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-stone-50"
                                        :class="{ 'bg-emerald-50/50': !n.read_at }">
                                    <span class="mt-0.5 shrink-0 text-xl leading-none" x-text="(n.data && n.data.icon) ? n.data.icon : '🔔'"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-stone-900" x-text="(n.data && n.data.title) ? n.data.title : 'Notifica'"></p>
                                        <p class="mt-0.5 line-clamp-2 text-xs text-stone-500" x-text="(n.data && n.data.body) ? n.data.body : ''"></p>
                                        <p class="mt-1 text-[10px] text-stone-400" x-text="timeAgo(n.created_at)"></p>
                                    </div>
                                    <span x-show="!n.read_at" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-rose-400"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Footer: link a pagina completa --}}
                        <div class="shrink-0 border-t border-stone-100 rounded-b-2xl overflow-hidden">
                            <a href="{{ route('notifications.index') }}"
                               @click="open = false"
                               class="flex w-full items-center justify-center px-4 py-2.5 text-xs font-semibold text-stone-500 transition hover:bg-stone-50 hover:text-stone-700">
                                Vedi tutte le notifiche →
                            </a>
                        </div>
                    </div>
                </div>
                @endauth

                {{-- ── Switcher Pianeta (desktop) — visibile solo con più Pianeti ─── --}}
                @auth
                @php
                    $userPlanets    = auth()->user()->planets()->orderBy('name')->get(['chapters.id', 'chapters.name']);
                    $activePlanetId = auth()->user()->memberProfile?->active_chapter_id;
                @endphp
                @if ($userPlanets->count() > 1)
                <div class="relative z-50 hidden sm:flex sm:items-center mr-2"
                     x-data="{ open: false }"
                     @click.away="open = false">
                    <button @click="open = !open"
                            class="inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-medium text-stone-600 transition hover:text-stone-900 focus:outline-none"
                            title="Cambia Pianeta">
                        <svg class="h-4 w-4 shrink-0 text-[color:var(--km-accent)]" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/>
                        </svg>
                        {{-- Mostra il nome del pianeta attivo senza troncamento --}}
                        <span class="max-w-[200px] whitespace-nowrap text-xs">
                            {{ $userPlanets->firstWhere('id', $activePlanetId)?->name ?? 'Pianeta' }}
                        </span>
                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    {{-- Dropdown: larghezza adattiva, scroll se molti pianeti --}}
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 top-full mt-2 origin-top-right rounded-2xl border border-stone-200 bg-white shadow-xl flex flex-col"
                         style="z-index:9999; min-width:220px; max-width:320px;">
                        <div class="shrink-0 bg-white px-4 py-2.5 border-b border-stone-100 rounded-t-2xl">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-stone-400">I tuoi Pianeti</p>
                        </div>
                        <div class="py-1 overflow-y-auto" style="max-height:260px;">
                            @foreach ($userPlanets as $planet)
                            <form method="POST" action="{{ route('planet.switch', $planet) }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm transition hover:bg-stone-50
                                               {{ $planet->id === $activePlanetId ? 'font-semibold text-[color:var(--km-accent)]' : 'text-stone-700' }}">
                                    @if ($planet->id === $activePlanetId)
                                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <span class="h-3.5 w-3.5 shrink-0"></span>
                                    @endif
                                    <span class="break-words">{{ $planet->name }}</span>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @endauth

                @guest
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50 hover:text-stone-900">
                        Accedi
                    </a>
                </div>
                @endguest

                @auth
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
                @endauth

                <div class="-me-1 flex items-center self-start pt-1 sm:hidden">
                    <button @click="open = ! open" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl p-2 text-stone-500 transition hover:bg-stone-100 hover:text-stone-700 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-stone-200 pb-4 pt-3 sm:hidden">
                <div class="space-y-1 pb-3">
                    @foreach ($memberNavigationItems as $item)
                        <x-responsive-nav-link :href="route($item['route'])" :active="request()->routeIs($item['active'])">
                            {{ __($item['label']) }}
                        </x-responsive-nav-link>
                    @endforeach
                </div>

                @guest
                <div class="border-t border-stone-200 pt-4 px-4 pb-2">
                    <a href="{{ route('login') }}"
                       class="flex w-full items-center justify-center rounded-2xl py-3.5 text-base font-semibold text-white shadow-sm"
                       style="background: linear-gradient(135deg,#537d4d,#3f6239);">
                        Accedi
                    </a>
                    <p class="mt-3 text-center text-sm text-stone-500">
                        Non hai un account?
                        <a href="{{ route('register') }}" class="font-semibold text-[color:var(--km-accent-strong)] hover:underline">Registrati</a>
                    </p>
                </div>
                @endguest

                @auth
                <div class="border-t border-stone-200 pt-4">
                    <div class="flex items-center justify-between px-4">
                        <div>
                            <div class="text-base font-medium text-stone-900">{{ Auth::user()->name }}</div>
                            <div class="text-sm font-medium text-stone-500">{{ Auth::user()->email }}</div>
                        </div>
                        @if($navUnreadCount > 0)
                            <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-rose-500 px-1.5 text-xs font-bold text-white">
                                {{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">
                            {{ __('nav.profile') }}
                        </x-responsive-nav-link>

                        <button @click="$dispatch('km:open-password-modal'); open = false"
                            class="block min-h-[44px] w-full px-4 py-2 text-start text-base font-medium text-stone-600 transition hover:bg-stone-50 hover:text-stone-800">
                            {{ __('nav.change_password') }}
                        </button>

                        <button @click="$dispatch('km:open-delete-modal'); open = false"
                            class="block min-h-[44px] w-full px-4 py-2 text-start text-base font-medium text-rose-600 transition hover:bg-rose-50">
                            {{ __('nav.delete_account') }}
                        </button>

                        {{-- Switcher lingua mobile --}}
                        <div class="border-t border-stone-100 px-4 py-3">
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-widest text-stone-400">{{ __('nav.language') }}</p>
                            <div class="flex gap-2">
                                <a href="{{ route('locale.switch', 'it') }}"
                                   class="flex-1 rounded-lg px-3 py-2 text-center text-sm font-medium transition {{ app()->getLocale() === 'it' ? 'bg-[color:var(--km-accent)] text-white' : 'bg-stone-100 text-stone-600' }}">
                                    🇮🇹 IT
                                </a>
                                <a href="{{ route('locale.switch', 'en') }}"
                                   class="flex-1 rounded-lg px-3 py-2 text-center text-sm font-medium transition {{ app()->getLocale() === 'en' ? 'bg-[color:var(--km-accent)] text-white' : 'bg-stone-100 text-stone-600' }}">
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
                @endauth
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
    <div class="relative max-h-[calc(100vh-2rem)] w-full max-w-md overflow-y-auto rounded-[1.6rem] border border-stone-200 bg-white p-5 shadow-2xl sm:p-7"
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

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                <button type="button" @click="open = false"
                        class="rounded-full border border-stone-200 bg-stone-50 px-5 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-100">
                    Annulla
                </button>
                <button type="submit"
                        class="rounded-full px-5 py-2.5 text-sm font-semibold text-white"
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
    <div class="relative max-h-[calc(100vh-2rem)] w-full max-w-md overflow-y-auto rounded-[1.6rem] border border-rose-200 bg-white p-5 shadow-2xl sm:p-7"
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

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                <button type="button" @click="open = false"
                        class="rounded-full border border-stone-200 bg-stone-50 px-5 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-100">
                    Annulla
                </button>
                <button type="submit"
                        class="rounded-full bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                    Elimina definitivamente
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
