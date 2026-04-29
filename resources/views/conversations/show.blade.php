<x-app-layout>
    <style>
        body {
            background:
                radial-gradient(circle at 82% 0%, rgba(139,197,63,.18), transparent 30%),
                radial-gradient(circle at 10% 25%, rgba(45,212,191,.12), transparent 35%),
                linear-gradient(135deg, #020b12, #031822 48%, #06111a) !important;
            color: #f8fafc;
        }
    </style>

    <x-slot name="header">
        <div class="km-portal-panel overflow-hidden p-0">
            <div class="px-6 py-5">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="km-portal-eyebrow">Conversazione privata</p>
                        <h1 class="mt-1 text-3xl font-semibold text-white">
                            {{ $conversation->participants->firstWhere('id', '!=', auth()->id())?->name ?? $conversation->subject }}
                        </h1>
                    </div>
                    <a href="{{ route('conversations.index') }}" class="inline-flex h-11 items-center justify-center rounded-full border px-5 text-sm font-semibold text-white/80" style="background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.18);">← Tutte le conversazioni</a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="km-portal-bg km-portal-page pb-12 pt-6">
        <div class="km-shell space-y-6">
            <div class="km-portal-panel space-y-4 p-6">
                @foreach ($conversation->messages as $message)
                    @php
                        $isOwnMessage = $message->user_id === auth()->id();
                    @endphp
                    <div class="rounded-[1.6rem] p-4 {{ $isOwnMessage ? 'border border-[rgba(154,216,74,0.25)]' : 'border border-white/10' }}" style="{{ $isOwnMessage ? 'background: rgba(154,216,74,0.12);' : 'background: rgba(255,255,255,0.06);' }}">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <p class="text-sm font-semibold {{ $isOwnMessage ? 'text-[#9AD84A]' : 'text-white' }}">{{ $message->user?->name ?? 'Utente eliminato' }}</p>
                            <p class="text-xs text-white/55 sm:whitespace-nowrap">{{ $message->created_at->format('d/m H:i') }}</p>
                        </div>
                        <p class="mt-2 text-sm leading-7 text-white/85">{{ $message->body }}</p>
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
