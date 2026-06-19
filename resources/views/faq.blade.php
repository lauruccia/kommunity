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
                    'Salva le modifiche e controlla l anteprima dal link alla tua pagina utente.',
                ],
            ],
            [
                'title' => '2. Usa la pagina personale',
                'items' => [
                    'La pagina personale e il tuo link pubblico da condividere con clienti, partner e contatti.',
                    'Chi non ha effettuato l accesso vede solo la pagina pubblica, senza menu e senza funzioni riservate.',
                    'Chi e loggato puo usare le azioni interne, come messaggi, one-to-one e referenze business, quando disponibili.',
                    'Nel profilo pubblico trovi due sezioni distinte: le Recensioni (valutazioni sull incontro) e le Referenze business (opportunita commerciali ricevute).',
                ],
            ],
            [
                'title' => '3. Trova altri utenti',
                'items' => [
                    'Apri K-Members per consultare la directory dei professionisti attivi.',
                    'Usa filtri e informazioni di profilo per individuare persone compatibili con i tuoi obiettivi.',
                    'Apri la pagina personale di un utente per leggere presentazione, servizi, contatti e referenze pubbliche.',
                ],
            ],
            [
                'title' => '4. Prenota un one-to-one',
                'items' => [
                    'Dalla pagina di un utente o dalla sezione One-to-one puoi inviare una richiesta di incontro.',
                    'Indica un motivo chiaro e, se necessario, proponi disponibilita utili.',
                    'Gestisci lo stato della richiesta dalla sezione One-to-one.',
                    'Quando entrambi i partecipanti confermano il completamento, si sblocca la possibilita di lasciare una Recensione reciproca visibile sul profilo pubblico.',
                ],
            ],
            [
                'title' => '5. Messaggi e relazioni',
                'items' => [
                    'Usa Messaggi per continuare una conversazione avviata con un utente.',
                    'Mantieni le conversazioni focalizzate su collaborazioni, appuntamenti e opportunita concrete.',
                    'Le notifiche in alto ti segnalano aggiornamenti importanti.',
                ],
            ],
            [
                'title' => '6. Eventi e community',
                'items' => [
                    'Apri Eventi per vedere gli appuntamenti disponibili nel tuo ecosistema.',
                    'Segna il tuo interesse o la partecipazione quando richiesto.',
                    // Forum nascosto temporaneamente
                ],
            ],
            [
                'title' => '7. Referenze business',
                'items' => [
                    'Le referenze business servono a passare un contatto o un\'opportunita commerciale a un altro utente: nome del potenziale cliente, azienda, contesto e obiettivo.',
                    'Puoi inviare una referenza business solo a utenti con cui hai completato almeno un one-to-one confermato da entrambi.',
                    'Il destinatario gestisce lo stato della referenza: dalla presa in carico fino alla chiusura (vinta o persa).',
                    'Le referenze chiuse con successo sono visibili sul profilo pubblico del destinatario come indicatore di reputazione business.',
                    'Puoi inviare piu referenze allo stesso utente nel tempo, una per ogni opportunita distinta.',
                ],
            ],
            [
                'title' => '8. Recensioni',
                'items' => [
                    'La recensione e un giudizio sulla persona e sull\'incontro, non su un\'opportunita commerciale.',
                    'Si sblocca automaticamente dopo che una one-to-one e stata completata e confermata da entrambi i partecipanti.',
                    'Puoi assegnare una valutazione da 1 a 5 stelle, selezionare le competenze dimostrate e scrivere un testo libero.',
                    'Puoi indicare se consiglieresti o meno questa persona ad altri utenti della rete.',
                    'La recensione e sempre pubblica e visibile nel profilo dell\'utente recensito. Ogni incontro ha la sua recensione.',
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
