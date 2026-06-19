<?php

return [
    'auto_title' => 'Segnalazione per :client',

    'status' => [
        'sent'             => 'Inviata',
        'in_progress'      => 'In corso',
        'completed'        => 'Valore dichiarato (conferma cliente)',
        'client_confirmed' => 'Confermata dal cliente (da validare)',
        'confirmed'        => 'Confermata',
        'cancelled'        => 'Annullata',
        'rejected'         => 'Valore rifiutato',
    ],

    'tabs' => [
        'received'    => 'Ricevute (sono il professionista)',
        'sent'        => 'Inviate (ho segnalato)',
        'client'      => 'Sono stato segnalato',
        'archive'     => 'Archivio',
        'leaderboard' => 'Classifica',
        'moderation'  => 'Moderazione',
    ],

    'roles' => [
        'sender'       => 'Segnalatore',
        'professional' => 'Professionista',
        'client'       => 'Cliente segnalato',
    ],

    'form' => [
        'title'             => 'Nuova segnalazione',
        'intro'             => 'Collega un cliente che ha bisogno di un servizio a un professionista della Kommunity.',
        'professional'      => 'Professionista (chi offre il servizio)',
        'professional_ph'   => 'Seleziona professionista',
        'professional_help' => 'Solo membri con cui hai un one-to-one completato.',
        'client'            => 'Cliente (chi ha bisogno del servizio)',
        'client_ph'         => 'Seleziona cliente',
        'client_help'       => 'Il membro che stai segnalando (es. il tuo amico Fabbro).',
        'submit'            => 'Invia segnalazione',
    ],

    'actions' => [
        'view'                 => 'Vedi referenza',
        'acknowledge'          => 'Prendi in carico',
        'declare_value'        => 'Dichiara valore consulenza',
        'declare_submit'       => 'Dichiara consulenza conclusa',
        'client_confirm'       => 'Conferma servizio ricevuto',
        'client_confirm_help'  => 'Conferma di aver ricevuto la consulenza: la referenza passerà alla validazione dell\'admin.',
        'approve'              => 'Approva valore',
        'reject'               => 'Rifiuta',
        'cancel'               => 'Annulla referenza',
    ],

    'value' => [
        'estimated'      => 'Valore stimato',
        'declared'       => 'Valore dichiarato',
        'approved'       => 'Valore confermato',
        'amount_label'   => 'Valore della consulenza (€)',
        'amount_help'    => 'Indica l\'importo della consulenza realizzata grazie a questa referenza.',
        'pending'        => 'In attesa di validazione',
    ],

    'leaderboard' => [
        'title'         => 'Classifica generatori di valore',
        'subtitle'      => 'Chi porta più valore alla Kommunity con le proprie referenze.',
        'rank'          => '#',
        'member'        => 'Membro',
        'referrals'     => 'Consulenze confermate',
        'value'         => 'Valore generato',
        'points'        => 'Punti',
        'you'           => 'Tu',
        'empty'         => 'Ancora nessuna referenza confermata. Le consulenze validate appariranno qui.',
        'my_points'     => 'I tuoi punti',
        'my_value'      => 'Valore che hai generato',
        'how_title'     => 'Come si calcolano i punti',
        'how_body'      => '50 punti per ogni consulenza confermata + 1 punto ogni 10 € di valore validato dall\'admin.',
    ],

    'flash' => [
        'declared'         => 'Valore dichiarato. Ora il cliente deve confermare di aver ricevuto il servizio.',
        'client_confirmed' => 'Grazie! Hai confermato il servizio. La referenza è in attesa di validazione da parte di un admin.',
        'confirmed'        => 'Valore confermato: ora conta per la classifica e i premi.',
        'rejected'         => 'Valore non approvato.',
    ],
];
