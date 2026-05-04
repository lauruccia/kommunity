<x-app-layout>
    @push('body-class') km-bg-light @endpush

    <main class="km-shell py-8">
        <div class="km-panel mx-auto max-w-2xl p-0 overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-stone-100 px-6 py-4">
                <div>
                    <h1 class="text-lg font-bold text-stone-900">Notifiche</h1>
                    <p class="text-sm text-stone-500">Tutte le tue notifiche recenti</p>
                </div>
                @if(auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition">
                            Segna tutte come lette
                        </button>
                    </form>
                @endif
            </div>

            {{-- Lista --}}
            <div class="divide-y divide-stone-50">
                @forelse($notifications as $notification)
                    @php
                        $data    = $notification->data ?? [];
                        $title   = $data['title'] ?? 'Notifica';
                        $body    = $data['body']  ?? '';
                        $icon    = $data['icon']  ?? '🔔';
                        $url     = $data['url']   ?? null;
                        $isUnread = is_null($notification->read_at);
                    @endphp
                    <div class="flex items-start gap-4 px-6 py-4 transition hover:bg-stone-50 {{ $isUnread ? 'bg-emerald-50/40' : '' }}">
                        <span class="mt-0.5 shrink-0 text-2xl leading-none">{{ $icon }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-stone-900">{{ $title }}</p>
                            @if($body)
                                <p class="mt-0.5 text-sm text-stone-500 line-clamp-2">{{ $body }}</p>
                            @endif
                            <p class="mt-1 text-xs text-stone-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            @if($isUnread)
                                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                            @endif
                            @if($url)
                                <a href="{{ $url }}"
                                   class="rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 transition hover:bg-stone-50">
                                    Vai →
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <span class="text-4xl">🔔</span>
                        <p class="mt-3 text-base font-semibold text-stone-700">Nessuna notifica</p>
                        <p class="mt-1 text-sm text-stone-400">Quando ricevi notifiche, le trovi qui.</p>
                    </div>
                @endforelse
            </div>

            {{-- Paginazione --}}
            @if($notifications->hasPages())
                <div class="border-t border-stone-100 px-6 py-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </main>
</x-app-layout>
