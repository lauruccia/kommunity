<?php

return [
    'status' => [
        'sent'        => 'Inviata',
        'in_progress' => 'In corso',
        'completed'   => 'Conclusa (da validare)',
        'confirmed'   => 'Confermata',
        'cancelled'   => 'Annullata',
        'rejected'    => 'Valore rifiutato',
    ],

    'tabs' => [
        'received'    => 'Ricevute',
        'sent'        => 'Inviate',
        'archive'     => 'Archivio',
        'leaderboard' => 'Classifica',
        'moderation'  => 'Moderazione',
    ],

    'actions' => [
        'view'           => 'Vedi referenza',
        'acknowledge'    => 'Prendi in carico',
        'declare_value'  => 'Dichiara valore consulenza',
        'declare_submit' => 'Dichiara consulenza conclusa',
        'approve'        => 'Approva valore',
        'reject'         => 'Rifiuta',
        'cancel'         => 'Annulla referenza',
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
        'declared'  => 'Valore dichiarato. La referenza è in attesa di validazione da parte di un admin.',
        'confirmed' => 'Valore confermato: ora conta per la classifica e i premi.',
        'rejected'  => 'Valore non approvato.',
    ],
];
