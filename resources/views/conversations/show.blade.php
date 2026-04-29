<x-app-layout>
    <x-slot name="header">
        <div class="km-portal-panel p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Conversazione</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-white">
                {{ $conversation->participants->firstWhere('id', '!=', auth()->id())?->name ?? $conversation->subject }}
            </h1>
        </div>
    </x-slot>

    <div class="km-portal-bg km-portal-page pb-12 pt-6">
        <div class="km-shell space-y-6">
            <div class="km-portal-panel space-y-4 p-6">
                @foreach ($conversation->messages as $message)
                    @php
                        $isOwnMessage = $message->user_id === auth()->id();
                    @endphp
                    <div class="rounded-[1.6rem] {{ $isOwnMessage ? 'bg-amber-50' : 'bg-white/[.075]' }} p-4">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <p class="text-sm font-semibold {{ $isOwnMessage ? 'text-stone-950' : 'text-white' }}">{{ $message->user?->name ?? 'Utente eliminato' }}</p>
                            <p class="text-xs {{ $isOwnMessage ? 'text-stone-500' : 'text-white/60' }} sm:whitespace-nowrap">{{ $message->created_at->format('d/m H:i') }}</p>
                        </div>
                        <p class="mt-2 text-sm leading-7 {{ $isOwnMessage ? 'text-stone-700' : 'text-white/80' }}">{{ $message->body }}</p>
                    </div>
                @endforeach
            </div>

            <div class="km-portal-panel p-6">
                <form method="POST" action="{{ route('conversations.messages.store', $conversation) }}" class="space-y-4">
                    @csrf
                    <textarea name="body" rows="4" class="km-portal-input" placeholder="Scrivi un messaggio" required></textarea>
                    <button type="submit" class="km-button-primary w-full sm:w-auto">Invia</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
