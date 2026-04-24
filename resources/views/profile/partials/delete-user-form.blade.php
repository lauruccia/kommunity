<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Elimina account
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            L'eliminazione e' permanente e rimuove dati, contenuti e accessi associati al profilo.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Elimina account</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Confermi l'eliminazione dell'account?
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Questa azione cancella in modo definitivo il tuo account. Inserisci la password per confermare.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Password" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Annulla
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Elimina account
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
