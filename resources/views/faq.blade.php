<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-500">Guida</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-stone-950">FAQ Kommunity</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">
                Una guida pratica per usare le funzioni principali della piattaforma.
            </p>
        </div>
    </x-slot>

    @php
        $sections = [
            [
                'title' => '1. Completa il profilo',
                'items' => [
                    'Vai su Profilo dal menu in alto.',
                    'Inserisci dati professionali, categorie, competenze, descrizione e contatti visibili.',
                    'Carica foto, logo e banner: sono gli elementi che rendono riconoscibile la tua pagina personale.',
                    'Salva le modifiche e controlla l anteprima dal link alla tua pagina membro.',
                ],
            ],
            [
                'title' => '2. Usa la pagina personale',
                'items' => [
                    'La pagina personale e il tuo link pubblico da condividere con clienti, partner e contatti.',
                    'Chi non ha effettuato l accesso vede solo la pagina pubblica, senza menu e senza funzioni riservate.',
                    'Chi e loggato puo usare le azioni interne, come messaggi, one-to-one e referenze, quando disponibili.',
                ],
            ],
            [
                'title' => '3. Trova altri membri',
                'items' => [
                    'Apri K-Members per consultare la directory dei professionisti attivi.',
                    'Usa filtri e informazioni di profilo per individuare persone compatibili con i tuoi obiettivi.',
                    'Apri la pagina personale di un membro per leggere presentazione, servizi, contatti e referenze pubbliche.',
                ],
            ],
            [
                'title' => '4. Prenota un one-to-one',
                'items' => [
                    'Dalla pagina di un membro o dalla sezione One-to-one puoi inviare una richiesta di incontro.',
                    'Indica un motivo chiaro e, se necessario, proponi disponibilita utili.',
                    'Gestisci lo stato della richiesta dalla sezione One-to-one.',
                ],
            ],
            [
                'title' => '5. Messaggi e relazioni',
                'items' => [
                    'Usa Messaggi per continuare una conversazione avviata con un membro.',
                    'Mantieni le conversazioni focalizzate su collaborazioni, appuntamenti e opportunita concrete.',
                    'Le notifiche in alto ti segnalano aggiornamenti importanti.',
                ],
            ],
            [
                'title' => '6. Eventi e community',
                'items' => [
                    'Apri Eventi per vedere gli appuntamenti disponibili nel tuo ecosistema.',
                    'Segna il tuo interesse o la partecipazione quando richiesto.',
                    'Usa Forum per condividere domande, spunti e contenuti utili alla community.',
                ],
            ],
            [
                'title' => '7. Referenze',
                'items' => [
                    'Le referenze servono a segnalare valore, affidabilita e collaborazioni riuscite.',
                    'Puoi consultare le referenze pubbliche nella pagina personale del membro.',
                    'Quando lasci una referenza, scrivi un testo concreto e verificabile.',
                ],
            ],
        ];
    @endphp

    <div class="km-shell pb-12">
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($sections as $section)
                <section class="km-panel p-6">
                    <h2 class="font-serif text-xl font-semibold text-stone-950">{{ $section['title'] }}</h2>
                    <ol class="mt-4 space-y-3">
                        @foreach ($section['items'] as $item)
                            <li class="flex gap-3 text-sm leading-6 text-stone-700">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[color:var(--km-accent)] text-xs font-semibold text-white">
                                    {{ $loop->iteration }}
                                </span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
