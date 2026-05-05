<x-app-layout>
    <div class="pb-12 pt-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">

            <h1 class="font-serif text-2xl font-semibold text-stone-950 mb-2">Gestione Cache</h1>
            <p class="text-sm text-stone-500 mb-6">Svuota la cache delle view, config e route senza accesso SSH.</p>

            {{-- Risultati operazione --}}
            @if(session('cache_results'))
                <div class="km-panel p-5 mb-6 space-y-2">
                    <p class="text-sm font-semibold text-stone-700 mb-3">Risultato operazione:</p>
                    @foreach(session('cache_results') as $line)
                        <p class="text-sm text-stone-700">{{ $line }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Form svuota cache --}}
            <div class="km-panel p-6">
                <p class="text-sm text-stone-600 mb-5">
                    Questa operazione elimina i file compilati di:
                </p>
                <ul class="text-sm text-stone-600 space-y-1 mb-6 list-disc list-inside">
                    <li><strong>View cache</strong> — template Blade compilati (<code>storage/framework/views/</code>)</li>
                    <li><strong>Config cache</strong> — configurazione compilata (<code>bootstrap/cache/config.php</code>)</li>
                    <li><strong>Route cache</strong> — routing compilato (<code>bootstrap/cache/routes-v7.php</code>)</li>
                    <li><strong>Bootstrap cache</strong> — services e packages (<code>bootstrap/cache/</code>)</li>
                    <li><strong>OPcache PHP</strong> — cache bytecode del server, spesso attiva su cPanel</li>
                    <li><strong>Application cache</strong> — dati in cache (<code>storage/framework/cache/</code>)</li>
                </ul>

                <form method="POST" action="{{ route('admin.cache.clear') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full border border-transparent bg-[color:var(--km-accent)] px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-[color:var(--km-accent-strong)]"
                        onclick="return confirm('Svuotare tutta la cache?')"
                    >
                        Svuota tutta la cache
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
