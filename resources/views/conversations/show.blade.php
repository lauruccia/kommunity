<x-app-layout>
    <x-slot name="header">
        <div class="km-panel p-6">
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Conversazione</p>
            <h1 class="mt-3 font-serif text-2xl font-semibold sm:text-3xl lg:text-4xl text-stone-950">
                {{ $conversation->participants->firstWhere('id', '!=', auth()->id())?->name ?? $conversation->subject }}
            </h1>
        </div>
    </x-slot>

    <div class="pb-12">
        <div class="km-shell space-y-6">
            <div class="km-panel space-y-4 p-6">
                @foreach ($conversation->messages as $message)
                    <div class="rounded-[1.6rem] {{ $message->user_id === auth()->id() ? 'bg-amber-50' : 'bg-stone-100' }} p-4">
                        <div class="flex items-start justify-between gap-4">
                            <p class="text-sm font-semibold text-stone-950">{{ $message->user->name }}</p>
                            <p class="text-xs text-stone-500">{{ $message->created_at->format('d/m H:i') }}</p>
                        </div>
                        <p class="mt-2 text-sm leading-7 text-stone-700">{{ $message->body }}</p>
                    </div>
                @endforeach
            </div>

            <div class="km-panel p-6">
                <form method="POST" action="{{ route('conversations.messages.store', $conversation) }}" class="space-y-4">
                    @csrf
                    <textarea name="body" rows="4" class="km-input" placeholder="Scrivi un messaggio" required></textarea>
                    <button type="submit" class="km-button-primary">Invia</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
